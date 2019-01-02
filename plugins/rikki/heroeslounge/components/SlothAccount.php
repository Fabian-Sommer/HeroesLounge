<?php namespace Rikki\Heroeslounge\Components;

use Lang;
use Auth;
use Log;
use Mail;
use Event;
use Flash;
use Input;
use Request;
use Redirect;
use Validator;
use ValidationException;
use ApplicationException;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use RainLab\User\Models\Settings as UserSettings;
use Exception;

use RainLab\User\Components\Account as UserAccount;
use Rikki\Heroeslounge\classes\Discord;
use Rikki\Heroeslounge\Models\Sloth as SlothModel;
use Rikki\Heroeslounge\Models\Season as Seasons;
use Rikki\Heroeslounge\Classes\Helpers\URLHelper;
use Rikki\Heroeslounge\Models\SlothRole;
use Rikki\Heroeslounge\Models\Timeline;
use Rikki\Heroeslounge\classes\Mailchimp\MailChimpAPI;
use Rikki\Heroeslounge\Models\Region as Region;

class SlothAccount extends UserAccount
{
    public $sloth;
    public $seasons;
    public function componentDetails()
    {
        return [
            'name'        => 'SlothAccount',
            'description' => 'SignIn,Register & UpdateProfile functionalties'
        ];
    }

    public function defineProperties()
    {
        return [
            'redirect' => [
                'title'       => 'rainlab.user::lang.account.redirect_to',
                'description' => 'rainlab.user::lang.account.redirect_to_desc',
                'type'        => 'dropdown',
                'default'     => ''
            ],
            'paramCode' => [
                'title'       => 'rainlab.user::lang.account.code_param',
                'description' => 'rainlab.user::lang.account.code_param_desc',
                'type'        => 'string',
                'default'     => 'code'
            ],
            'forceSecure' => [
                'title'       => 'Force secure protocol',
                'description' => 'Always redirect the URL with the HTTPS schema.',
                'type'        => 'checkbox',
                'default'     => 0
            ],
        ];
    }

    public function getRedirectOptions()
    {
        return [''=>'- none -'] + Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public $roles = null;
    public $notifications = null;
    public $appsCount = null;
    public $regions = null;

    public function init()
    {
        parent::init();
        $this->addJs('/plugins/rikki/heroeslounge/assets/js/selectFile.js');


        $user = Auth::getUser();
        $this->roles = SlothRole::all();
        $this->regions = Region::all();
        if ($user) {
            $component = $this->addComponent(
                            'Rikki\Heroeslounge\Components\ViewApps',
                            'viewApps',
                            []
                        );
            $this->sloth = SlothModel::getFromUser($user);
            $this->seasons = Seasons::where('is_active', 1)->where('region_id', $this->sloth->region_id)->get();
            $this->appsCount = $component->apps->count();
        }
    }
    public function onRun()
    {
        parent::onRun();
    }

    /**
     * Sign in the user
     */
    public function onSignin()
    {
        try {
            /*
             * Validate input
             */
            $data = post();
            $rules = [];

            $rules['login'] = $this->loginAttribute() == UserSettings::LOGIN_USERNAME
                ? 'required|between:2,255'
                : 'required|email|between:6,255';

            $rules['password'] = 'required|between:4,255';

            if (!array_key_exists('login', $data)) {
                $data['login'] = post('username', post('email'));
            }

            $validation = Validator::make($data, $rules);
            if ($validation->fails()) {
                throw new ValidationException($validation);
            }

            /*
             * Authenticate user
             */
            $credentials = [
                'login'    => array_get($data, 'login'),
                'password' => array_get($data, 'password')
            ];

            Event::fire('rainlab.user.beforeAuthenticate', [$this, $credentials]);
            $user = Auth::authenticate($credentials, true);


            /*
             * Redirect to the intended page after successful sign in
             */
            $redirectUrl = $this->pageUrl($this->property('redirect'))
                ?: $this->property('redirect');

            if ($redirectUrl = input('redirect', $redirectUrl)) {
                return Redirect::intended($redirectUrl);
            }
        } catch (Exception $ex) {
            if (Request::ajax()) {
                throw $ex;
            } else {
                Flash::error($ex->getMessage());
            }
        }
    }

    public function onRegister()
    {
        try {
            /*
             * Validate input
             */
            $data = post();

            if (!array_key_exists('password_confirmation', $data)) {
                $data['password_confirmation'] = post('password');
            }

            $rules = [
                'email'    => 'required|email|unique:users|between:6,255|confirmed',
                'password' => 'required|between:4,255|confirmed',
                'battle_tag' => 'required|unique:rikki_heroeslounge_sloths|regex:/^[\p{L}\p{Mn}][\p{L}\p{Mn}0-9]{2,11}#[0-9]{1,6}+$/u',
                'discord_tag' => 'required|unique:rikki_heroeslounge_sloths|regex:/[\s\S]*#[0-9]{1,10}+$/u',
                'region_id' => 'required|exists:rikki_heroeslounge_regions,id'
            ];

            if ($this->loginAttribute() == UserSettings::LOGIN_USERNAME) {
                $rules['username'] = 'required|unique:users|between:2,18';
            }

            $validation = Validator::make($data, $rules);
            if ($validation->fails()) {
                throw new ValidationException($validation);
            }

            /*
             * Check if the user is on the Heroes Lounge Discord server.
             */

            $userDiscordId = Discord\Attendance::GetDiscordUserId($data['discord_tag']);

            if (empty($userDiscordId)) {
                throw new ApplicationException("Please join the Discord server before registering. If you are a member, check your Discord tag and try again later.");
            }


            /*
             * Register user
             */
            $requireActivation = UserSettings::get('require_activation', true);
            $automaticActivation = UserSettings::get('activate_mode') == UserSettings::ACTIVATE_AUTO;
            $userActivation = UserSettings::get('activate_mode') == UserSettings::ACTIVATE_USER;
            $user = Auth::register($data, $automaticActivation);


            /*
             * Activation is by the user, send the email
             */
            if ($userActivation) {
                $this->sendActivationEmail($user);

                Flash::success(Lang::get('rainlab.user::lang.account.activation_email_sent'));
            }

            /*
             * Automatically activated or not required, log the user in
             */
            if ($automaticActivation || !$requireActivation) {
                Auth::login($user);
            }


            /*
            * Make sure the user gets a Sloth attached
            */

            $sloth = SlothModel::getFromUser($user);
            $sloth->battle_tag = $data['battle_tag'];
            $sloth->discord_tag = $data['discord_tag'];
            $sloth->discord_id = $userDiscordId;
            $sloth->region_id = $data['region_id'];

            $sloth->save();
            $this->user = $user;

            IDFetcher::fetchID($sloth);
            MMRFetcher::updateMMR($sloth);

            /*
              Assign EU or NA role on Discord based on region_id.
              region_id = 1: EU
              region_id = 2: NA
            */

            if ($sloth->region_id == 1) {
              Discord\RoleManagement::UpdateUserRole("PUT", $sloth->discord_id, "EU");
            } else if ($sloth->region_id == 2) {
              Discord\RoleManagement::UpdateUserRole("PUT", $sloth->discord_id, "NA");
            }

            // sign up for newsletter
            if (array_key_exists('newsletter_subscription', $data) && $data['newsletter_subscription']) {
                MailChimpAPI::subscribeNewUser($user);
            } else {
                MailChimpAPI::unsubscribeNewUser($user);
            }

            /*
             * Redirect to the intended page after successful sign in
             */
            $redirectUrl = $this->pageUrl($this->property('redirect'))
                ?: $this->property('redirect');

            if ($redirectUrl = post('redirect', $redirectUrl)) {
                return Redirect::intended($redirectUrl);
            }
        } catch (Exception $ex) {
            if (Request::ajax()) {
                throw $ex;
            } else {
                Flash::error($ex->getMessage());
            }
        }
    }

    public function onParticipationSave()
    {
        try {
            foreach ($this->seasons as $season) {
                if ($season->current_round == 0) {
                    $val = post('part_seas_'.$season->id);
                    if (isset($val) && !$season->free_agents->contains($this->sloth->id)) {
                        $season->free_agents()->attach($this->sloth->id);
                        Discord\RoleManagement::UpdateUserRole("PUT", $this->sloth->discord_id, "FreeAgent");
                        Flash::success('You will participate in '.$season->title.'!');
                    } elseif (isset($val) == false) {
                        $season->free_agents()->detach($this->sloth->id);
                        Discord\RoleManagement::UpdateUserRole("DELETE", $this->sloth->discord_id, "FreeAgent");
                        Flash::error('You will not paricipate in '.$season->title.' - Sad to see you go!');
                    }
                }
            }
        } catch (Exception $e) {
            Flash::error($e->getMessage());
        } finally {
            return Redirect::refresh();
        }
    }

    public function onUpdateGeneral()
    {
        if (!$user = $this->user()) {
            return;
        }

        $data = post();
        $sloth = SlothModel::getFromUser($user);

        $sloth->user->rules = [
            'password' => 'between:4,255|confirmed'
        ];

        if ($data['username'] != $sloth->title) {
            $sloth->user->rules['username'] = 'required|unique:users|between:2,18';
        }

        $validation = Validator::make($data, $sloth->user->rules);
        if ($validation->fails()) {
            //throw new ValidationException($validation);
            Flash::error($validation->messages()->first());
            return;
        }

        $user->fill($data);
        $user->save();
        $sloth->title = $data['username'];
        $sloth->save();

        if (strlen(post('password'))) {
            Auth::login($user->reload(), true);
        }

        Flash::success(post('flash', Lang::get('rainlab.user::lang.account.success_saved')));
        if ($redirect = $this->makeRedirection()) {
            return $redirect;
        }
    }

    public function onUpdateAvatar()
    {
        if (!$user = $this->user()) {
            return;
        }
        $sloth = SlothModel::getFromUser($user);

        $f = Input::file('avatar');
        if ($f != null) {
            try {
                if ($f->getClientOriginalExtension() == 'gif') {
                    Flash::error('Only Patreons are allowed to upload GIF Files!');
                } else {
                    $avatar = new \System\Models\File;
                    $avatar->fromPost($f);
                    $filename = $avatar->getLocalPath();
                    list($width, $height) = getimagesize($filename);
                    if ($width > 1280) {
                        Flash::error('Avatar width must be equal or less than 1280px!');
                        throw new ValidationException(['avatar' => 'Avatar width must be equal or less than 1280px!']);
                    }
                    if ($height > 1280) {
                        Flash::error('Avatar height must be equal or less than 1280px!');
                        throw new ValidationException(['avatar' => 'Logo height must be equal or less than 1280px!']);
                    }
                    $avatar->save();
                    $sloth->user->avatar()->add($avatar);
                    $sloth->user->save();
                    $timeline = new Timeline();
                    $timeline->type = 'Sloth.Logo';
                    $timeline->save();
                    $timeline->sloths()->add($sloth);
                    Flash::success('Avatar updated successfully!');
                }
            } catch (Exception $e) {
                Flash::error($e->getMessage());
            }
        } elseif (!$sloth->user->avatar) {
            Flash::warning('No Avatar provided!');
        }
        $sloth->save();
        if ($redirect = $this->makeRedirection()) {
            return $redirect;
        }
    }

    public function onUpdateBanner()
    {
        if (!$user = $this->user()) {
            return;
        }
        $sloth = SlothModel::getFromUser($user);

        $b = Input::file('banner');
        if ($b != null) {
            try {
                if ($b->getClientOriginalExtension() == 'gif') {
                    Flash::error('Only Patreons are allowed to upload GIF Files!');
                } else {
                    $banner = new \System\Models\File;
                    $banner->fromPost($b);
                    $banner->save();
                    $sloth->banner()->add($banner);
                    $sloth->save();
                    Flash::success('Banner updated successfully!');
                }
            } catch (Exception $e) {
                Flash::error($e->getMessage());
            }
        } elseif (!$sloth->banner) {
            Flash::warning('No Banner provided!');
        }
        $sloth->save();
        if ($redirect = $this->makeRedirection()) {
            return $redirect;
        }
    }

    public function onUpdateDescription()
    {
        if (!$user = $this->user()) {
            return;
        }

        $data = post();
        $sloth = SlothModel::getFromUser($user);
        $sloth->short_description = $data['short_description'];
        $sloth->save();

        Flash::success('Description updated successfully!');
        if ($redirect = $this->makeRedirection()) {
            return $redirect;
        }
    }

    public function onUpdateLinks()
    {
        if (!$user = $this->user()) {
            return;
        }

        $data = post();
        $sloth = SlothModel::getFromUser($user);

        $sloth->twitch_url = URLHelper::makeTwitchURL($data['twitch_url']);
        $sloth->facebook_url = URLHelper::makeFacebookURL($data['facebook_url']);
        $sloth->twitter_url = URLHelper::makeTwitterURL($data['twitter_url']);
        $sloth->website_url = URLHelper::makeWebsiteURL($data['website_url']);
        $sloth->youtube_url = URLHelper::makeYoutubeURL($data['youtube_url']);
        $sloth->discord_tag = $data['discord_tag'];
        $sloth->region_id = $data['region_id'];
        $sloth->save();

        $user->country_id = $data['country_id'];
        $user->save();

        Flash::success('Social information was updated successfully!');
        if ($redirect = $this->makeRedirection()) {
            return $redirect;
        }
    }

    public function onUpdateGame()
    {
        if (!$user = $this->user()) {
            return;
        }

        $data = post();
        $sloth = SlothModel::getFromUser($user);

        $sloth->role_id = $data['role_id'];
        if ($sloth->region_id == 2 && isset($data['server_preference'])) {
            $sloth->server_preference = $data['server_preference'];
        }
        if ($sloth->team_id == 0) {
            $this->onParticipationSave();
        }
        $sloth->save();

        Flash::success('Role updated successfully!');
        if ($redirect = $this->makeRedirection()) {
            return $redirect;
        }
    }

    public function onSubscribeNewsletter()
    {
        if (!$user = $this->user()) {
            return;
        }
        MailChimpAPI::subscribeExistingUser($user);
    }

    public function onUnsubscribeNewsletter()
    {
        if (!$user = $this->user()) {
            return;
        }
        MailChimpAPI::unsubscribeExistingUser($user);
    }
}
