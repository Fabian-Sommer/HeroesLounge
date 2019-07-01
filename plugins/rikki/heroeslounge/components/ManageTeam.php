<?php namespace Rikki\Heroeslounge\Components;

 
use Cms\Classes\ComponentBase;
use Rikki\Heroeslounge\Models\Team as Teams;
use Rikki\Heroeslounge\Models\Season as Seasons;
use Rikki\Heroeslounge\Models\Sloth as Sloths;
use Rikki\Heroeslounge\Models\Timeline as Timeline;
use Rikki\Heroeslounge\Models\Apps as Application;
use Rikki\Heroeslounge\Classes\Helpers\URLHelper;
use October\Rain\Database\Attach\Resizer;



use Auth;
use Flash;
use Carbon\Carbon;
use Redirect;
use Input;
use Validator;
use ValidationException;
use Response;
use Log;

class ManageTeam extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Manage Team',
            'description' => 'Allows Captains to manage their team'
        ];
    }

    public $team = null;
    public $user = null;
    public $seasons = null;
    public $players = null;
    public $appsCount = null;
    public $rosterLocked = false;

    public function init()
    {
        $this->user = Auth::getUser();

        if ($this->user == null) {
            Flash::error('You are not authenticated!');
        } else {
            $this->addJs('/plugins/rikki/heroeslounge/assets/js/selectFile.js');
            $this->addJs('/plugins/rikki/heroeslounge/assets/js/autocomplete.js');
            $this->team = Teams::where('slug', $this->param('slug'))->first();
            if ($this->team) {
                $this->seasons = Seasons::where('is_active', 1)->where('region_id', $this->team->region_id)->get();
                $this->rosterLocked = $this->team->ongoingCompetitions()->count() > 0 ? true : false;
                $this->players = $this->team->sloths->where('id', '!=', $this->user->sloth->id);
                

                $component = $this->addComponent(
                                'Rikki\Heroeslounge\Components\ViewApps',
                                'ViewApps',
                                []
                            );
                $this->appsCount = $component->slothApps->count() + $component->teamApps->count();
            }
        }
    }


    public function onAutocomplete()
    {
        $term = implode(post());
        $queries = Sloths::where(function ($q) use ($term) {
            $q->where('title', 'LIKE', '%'.$term.'%')->orWhere('battle_tag', 'LIKE', '%'.$term.'%')->orWhere('discord_tag', 'LIKE', '%'.$term.'%');
        })->take(5)->get()->pluck('title');


        return Response::json($queries);
    }


    public function onLogoSave()
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
                $filename = $logo->getLocalPath();
                list($width, $height) = getimagesize($filename);
                if ($width > 1280) {
                    Flash::error('Logo width must be equal or less than 1280px!');
                    throw new ValidationException(['logo' => 'Logo width must be equal or less than 1280px!']);
                }
                if ($height > 1280) {
                    Flash::error('Logo height must be equal or less than 1280px!');
                    throw new ValidationException(['logo' => 'Logo height must be equal or less than 1280px!']);
                }
                $logo->save();
                $this->team->logo()->add($logo);
                $this->team->save();
                Flash::success('Logo updated successfully!');
            } catch (Exception $e) {
                Flash::error($e->getMessage());
            } finally {
                return Redirect::refresh();
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
                Flash::success('Banner updated successfully!');
            } catch (Exception $e) {
                Flash::error($e->getMessage());
            } finally {
                return Redirect::refresh();
            }
        } else {
            Flash::warning('No Banner provided!');
        }
    }

    public function onParticipationSave()
    {
        if ($this->team->region_id == 2) {
            $this->team->server_preference = post('server_preference');
            $this->team->save();
        }
        try {
            foreach ($this->seasons as $season) {
                if ($season->reg_open == 1) {
                    $val = post('part_seas_'.$season->id);
                    if (isset($val) && !$season->teams->contains($this->team->id)) {
                        $season->teams()->attach($this->team->id);
                        Flash::success('You will participate in '.$season->title.'!');
                    } elseif (isset($val) == false) {
                        $season->teams()->detach($this->team->id);
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

    private function handleRemovedPlayers($removedPlayers)
    {
        foreach ($removedPlayers as $player) {
            $this->team->sloths()->remove($player);
            Flash::warning($player->title.' has been removed from your team');
        }
    }


    private function handleNewPlayers($newPlayers)
    {
        $oldPlayersCount = $this->team->sloths->count();
        if (9-$oldPlayersCount >= count($newPlayers)) {
            foreach ($newPlayers as $player) {
                $appAlreadyExists = Application::where('user_id', $player->user_id)->where('team_id', $this->team->id)->where('accepted', 0)->where('withdrawn', 0)->first();

                if ($appAlreadyExists == null) {
                    $app = new Application;
                    $app->user_id = $player->user_id;
                    $app->team_id = $this->team->id;
                    $app->approved = 1;
                    $app->message = $this->user->username.' has invited you to join '.$this->team->title;
                    $app->save();
                    Flash::success($player->title.' has been invited to join your team.');
                } else {
                    if ($appAlreadyExists->approved == 1) {
                        Flash::error($player->title.' has already been invited to join your team.');
                    } else {
                        $appAlreadyExists->approved = 1;
                        $appAlreadyExists->save();
                        Flash::success($player->title . ' has been invited to join your team.');
                    }
                }
            }
        } else {
            Flash::error('You are trying to add more players than you have space for!');
        }
    }

    public function onMemberRemove()
    {
        $player = $this->players->where('title', post('remove'))->first();
        $this->handleRemovedPlayers([$player]);
        return Redirect::refresh();
    }

    public function onPromoteMemberToCaptain()
    {
        // Demote current captain
        $currentCaptain = $this->team->sloths->where('id', $this->user->sloth->id)->first();
        $currentCaptain->pivot->is_captain = false;
        $currentCaptain->pivot->save();

        // Promote user to captain
        $slothToPromote = $this->team->sloths->where('title', post('promote'))->first();
        $slothToPromote->pivot->is_captain = true;
        $slothToPromote->pivot->save();
        
        Flash::success($slothToPromote->title.' has been promoted to Captain');
        return Redirect::to('/team/view/'.$this->team->slug);
    }

    public function onRosterSave()
    {
        if ($this->rosterLocked == false) {
            
            $pArr = array();
            for ($i=2;$i<=9;$i++) {
                $field = post('sl'.$i);
                if (!empty($field)) {
                    $pArr[] = $field;
                }
            }

            $newPlayers = [];
            foreach ($pArr as $title) {
                $np = Sloths::where('title', $title)->first();
                
                if (isset($np)) {
                    $newPlayers[] = $np;
                }
            }
            $this->team->accepting_apps = post('accepting_apps') !== null ? 1 : 0;
            $this->team->save();
            $this->handleNewPlayers($newPlayers);


            return Redirect::refresh();
        } else {
            Flash::warning('Your roster is locked.');
            return Redirect::refresh();
        }
    }


    public function onDescriptionSave()
    {
        try {
            $this->team->short_description = post('short_description');
            $this->team->save();
            Flash::success('Description updated successfully!');
        } catch (Exception $e) {
            Flash::error($e->getMessage());
        } finally {
            return Redirect::refresh();
        }
    }

    public function onSocialSave()
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
                'twitch_url' => ['url', 'regex:/^http[s]?:\/\/(www\.)?twitch\.tv\/[a-zA-Z0-9][\w]{2,24}(\/)?$/u'],
                'facebook_url' => ['url', 'regex:/^http[s]?:\/\/(www\.)?facebook\.com\/[A-Za-z0-9\.]{3,}(\/)?$/u'],
                'twitter_url' => ['url', 'regex:/^http[s]?:\/\/(www\.)?twitter\.com\/([a-zA-Z0-9_]+)(\/)?$/u'],
                'youtube_url' => ['url', 'regex:/^http[s]?:\/\/(www\.)?youtube\.com\/(channel|user|c)\/([a-zA-Z0-9_\-]+)(\/)?$/u'],
                'website_url' => ['url', 'regex:/^((?!porn).)*$/u']
                ]
            );
            if ($validation->fails()) {
                Flash::error((new ValidationException($validation))->getMessage());
            }

            $this->team->facebook_url = post('facebook_url');
            $this->team->twitter_url = post('twitter_url');
            $this->team->twitch_url = post('twitch_url');
            $this->team->youtube_url = post('youtube_url');
            $this->team->website_url = post('website_url');
            $this->team->save();
            Flash::success('Social Links updated successfully!');
        } catch (Exception $e) {
            Flash::error($e->getMessage());
        } finally {
            return Redirect::refresh();
        }
    }
    public function onTimelineEntrySave()
    {
        try {
            $timeline = new Timeline();
            $timeline->message = post('message');
            $timeline->type = 'Team.Message';
            $timeline->save();
            $timeline->teams()->add($this->team);
            Flash::success('Timeline entry saved succecfully!');
        } catch (Exception $e) {
            Flash::error($e->getMessage());
        } finally {
            return Redirect::refresh();
        }
    }
}
