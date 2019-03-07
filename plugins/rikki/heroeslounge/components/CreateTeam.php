<?php namespace Rikki\Heroeslounge\Components;


use Cms\Classes\ComponentBase;
use Rikki\Heroeslounge\Models\Team as Teams;
use Rikki\Heroeslounge\Models\Sloth as Sloths;
use Rikki\Heroeslounge\Models\Season as Seasons;
use Rikki\Heroeslounge\Classes\Helpers\URLHelper;
use Rikki\Heroeslounge\Models\Timeline;

use Auth;
use Flash;
use Carbon\Carbon;
use Redirect;
use Input;
use Validator;
use ValidationException;
use Rikki\Heroeslounge\Models\Apps as Applications;
use Rikki\Heroeslounge\classes\Discord;
use Rikki\Heroeslounge\Models\Region as Region;

class CreateTeam extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Create Team',
            'description' => 'Allows User to create their team'
        ];
    }

    public $user = null;
    public $team = null;
    public $regions = null;

    public function init()
    {
        $this->addJs('/plugins/rikki/heroeslounge/assets/js/selectFile.js');

        $this->user = Auth::getUser();
        $this->regions = Region::all();
        if ($this->user == null) {
            Flash::error('You are not authenticated!');
        }
    }
    public function onRun()
    {
    }

    public function onCreateTeam()
    {
        if (empty(post('team_name'))) {
            Flash::error('A team name is required!');
            return Redirect::refresh();
        } else if (strpos(post('team_slug'), '%') !== false || strpos(post('team_slug'), '$') !== false) {
            Flash::error('The team abbreviation must not contain special characters!');
            return Redirect::refresh();
        } else {
            try {
                $this->team = new Teams;

                $validation = Validator::make(['title'=>post('team_name'), 'slug'=>post('team_slug')], $this->team->rules);
                if ($validation->fails()) {
                    Flash::error($validation->messages()->first());
                } else {
                    $this->team->title = post('team_name');
                    $this->team->slug = post('team_slug');
                    $this->team->region_id = post('region_id');
                    $this->team->type = post('type');
                    $this->team->disbanded = false;
                    $this->team->save();
                    if (post('type') == 1) {
                        $this->user->sloth->team_id = $this->team->id;
                        $this->user->sloth->is_captain = 1;
                    } else {
                        $this->user->sloth->divs_team_id = $this->team->id;
                        $this->user->sloth->is_divs_captain = 1;
                    }
                    $this->user->sloth->save();
                    $timeline = new Timeline();
                    $timeline->type = 'Team.Created';
                    $timeline->save();
                    $this->team->timeline()->add($timeline);
                    $this->user->sloth->timeline()->add($timeline);
                    $this->onLogoSave();
                    $this->onBannerSave();
                    $this->onRosterSave();
                    $this->onDescriptionSave();
                    $this->onSocialSave();

                    $pendingApps = Applications::where("user_id", $this->user->id)->where("team_id", "!=", $this->team->id)->get();

                    $pendingApps->each(function ($model) {
                        $model->withdrawn = 1;
                        $model->save();
                    });

                    Flash::success('Team sucessfully created!');
                    return Redirect::to('team/manage/'.$this->team->slug);
                }
            } catch (Exception $e) {
                if ($e->getMessage() == "The slug has already been taken.") {
                    Flash::error("This abbreviation has already been taken.");
                } else {
                    Flash::error($e->getMessage());
                }
                return Redirect::refresh();
            }
        }
    }

    private function onLogoSave()
    {
        $f = Input::file('logo');

        if ($f != null) {
            try {
                if ($f->getClientOriginalExtension() == 'gif') {
                    Flash::error('Only Patreons are allowed to upload GIF Files!');
                    return;
                }
                $logo = new \System\Models\File;
                $logo->fromPost($f);
                $logo->save();
                $this->team->logo()->add($logo);
                $this->team->save();
            } catch (Exception $e) {
                Flash::error($e->getMessage());
            }
        } else {
            Flash::warning('No Logo provided!');
        }
    }
    public function onBannerSave()
    {
        $f = Input::file('banner');

        if ($f != null) {
            try {
                if ($f->getClientOriginalExtension() == 'gif') {
                    Flash::error('Only Patreons are allowed to upload GIF Files!');
                    return;
                }
                $banner = new \System\Models\File;
                $banner->fromPost($f);
                $banner->save();
                $this->team->banner()->add($banner);
                $this->team->save();
            } catch (Exception $e) {
                Flash::error($e->getMessage());
            }
        } else {
            Flash::warning('No Banner provided!');
        }
    }

    private function onRosterSave()
    {
        try {
            Flash::warning('A roster must have atleast 5 members to be eligible. See rules for more details.');
        } catch (Exception $e) {
            Flash::error($e->getMessage());
        }
    }

    private function onDescriptionSave()
    {
        try {
            $this->team->short_description = post('short_description');
            $this->team->save();
        } catch (Exception $e) {
            Flash::error($e->getMessage());
        }
    }

    private function onSocialSave()
    {
        try {
            $_POST['twitch_url'] = URLHelper::makeTwitchURL($_POST['twitch_url']);
            $_POST['facebook_url'] = URLHelper::makeFacebookURL($_POST['facebook_url']);
            $_POST['twitter_url'] = URLHelper::makeTwitterURL($_POST['twitter_url']);
            $_POST['website_url'] = URLHelper::makeWebsiteURL($_POST['website_url']);
            $_POST['youtube_url'] = URLHelper::makeYoutubeURL($_POST['youtube_url']);
            $validation = Validator::make(
                $_POST,
                [
                'twitch_url' => ['url', 'regex:/^https:\/\/www\.twitch\.tv\/[a-zA-Z0-9][\w]{2,24}$/u'],
                'facebook_url' => ['url', 'regex:/^https:\/\/www\.facebook\.com\/[a-z\d.]{3,}$/u'],
                'twitter_url' => ['url', 'regex:/^https:\/\/twitter\.com\/([a-zA-Z0-9_]+)$/u'],
                'youtube_url' => ['url', 'regex:/^https:\/\/www\.youtube\.com\/(channel|user)\/([a-zA-Z0-9_\-]+)$/u'],
                'website_url' => ['url', 'regex:/^((?!porn).)*$/u']
                ]
            );
            if ($validation->fails()) {
                Flash::error((new ValidationException($validation))->getMessage());
                return Redirect::refresh();
            }
            $this->team->facebook_url = post('facebook_url');
            $this->team->twitter_url = post('twitter_url');
            $this->team->twitch_url = post('twitch_url');
            $this->team->youtube_url = post('youtube_url');
            $this->team->website_url = post('website_url');
            $this->team->save();
        } catch (Exception $e) {
            Flash::error($e->getMessage());
        }
    }
}
