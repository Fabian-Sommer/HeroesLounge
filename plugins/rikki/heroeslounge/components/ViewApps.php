<?php namespace Rikki\Heroeslounge\Components;

use Auth;
use Db;
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
use October\Rain\Support\Collection;
use Rikki\Heroeslounge\classes\Discord;

use RainLab\User\Components\Account as UserAccount;
use Rikki\Heroeslounge\Models\Sloth as SlothModel;
use Rikki\Heroeslounge\Models\Team as Teams;
use Rikki\Heroeslounge\Models\Apps as Applications;
use Rikki\Heroeslounge\Models\Timeline;
use Rikki\Heroeslounge\Models\Season;

class ViewApps extends ComponentBase
{
    public $sloth;
    public $slothApps;
    public $slothAppsTeams;
    public $teamApps;
    public $teamAppsUsers;

    public function componentDetails()
    {
        return [
            'name'        => 'ViewApps',
            'description' => 'Allows users to manage their applications',
        ];
    }

    public function init()
    {
        $user = Auth::getUser();
        $this->teamApps = new Collection();
        $this->teamAppsUsers = [];
        if ($user != null) {
            $this->sloth = SlothModel::getFromUser($user);
            foreach ($this->sloth->teams as $key => $team) {
                if ($team->pivot->is_captain) {
                    $apps = Applications::where("team_id", "=", $team->id)->where('withdrawn', 0)->where('accepted', 0)->get();
                    foreach ($apps as $key => $app) {
                        $this->teamApps->push($app);
                    }

                    $appsFrom = Db::table('rikki_heroeslounge_team_apps')->where('team_id', '=', $team->id)->where('withdrawn', 0)->where('accepted', 0)->lists('user_id');
                    foreach ($appsFrom as $uId) {
                        $this->teamAppsUsers[$uId] = SlothModel::where('user_id', $uId)->first();
                    }
                }
            }

            $this->slothApps = Applications::where("user_id", "=", $this->sloth->user_id)->where('withdrawn', 0)->where('accepted', 0)->get();
            $appliedTo = Db::table('rikki_heroeslounge_team_apps')->where('user_id', '=', $this->sloth->user_id)->where('withdrawn', 0)->where('accepted', 0)->lists('team_id');

            foreach ($appliedTo as $tId) {
                $team = Teams::where('id', '=', $tId)->first();
                if ($team) {
                    $this->slothAppsTeams[$tId] = $team;
                }
            }
        }
    }

    public function onSendAccept()
    {
        $user = Auth::getUser();

        if ($user != null) {
            $this->sloth = SlothModel::getFromUser($user);
        }

        $app = Applications::find(post("id"));

        if ($this->sloth->teams->contains(function ($team) use ($app) {
            return $team->id == $app->team_id;
        })) {
            $app->approved = 1;
            $app->save();
            Flash::success("Application successfully accepted. The player can now join the team by accepting the invite on his Account page.");
            return Redirect::refresh();
        }
    }

    public function onAccept()
    {
        $user = Auth::getUser();

        if ($user != null) {
            $this->sloth = SlothModel::getFromUser($user);
        } else {
            return;
        }

        $app = Applications::find(post("id"));

        if ($app->approved == 1) {
            if ($app->user_id == $this->sloth->user_id) {
                $team = Teams::find($app->team_id);
                $sloth = $this->sloth;
                if ($team->sloths->filter(function ($s) use ($sloth) {
                    return $s->id == $sloth->id;
                })->count() > 0) {
                    //sloth is already part of the team
                    return;
                }
                $numOfPlayers = $team->sloths->count();
                if ($numOfPlayers < 9) {
                    //a sloth should not participate in two different teams in the same season or tournament
                    if ($this->sloth->mayJoinTeam($team)) {
                        $this->sloth->teams()->add($team);

                        $seasons = Season::all();
                        foreach ($seasons as $season) {
                            if ($season->reg_open == 1 && $season->teams->contains($team) && $season->free_agents->contains($this->sloth)) {
                                $season->free_agents()->detach($this->sloth->id);
                                Discord\RoleManagement::UpdateUserRole("DELETE", $sloth->discord_id, "FreeAgent");
                            }
                        }
                        
                        $this->sloth->save();

                        $app->accepted = 1;
                        $app->save();


                        if ($numOfPlayers == 8) {
                            $appsToTeam = Applications::where("team_id", $app->team_id)->get();

                            $appsToTeam->each(function ($model) {
                                $model->withdrawn = 1;
                                $model->save();
                            });
                        }

                        Flash::success("Application successfully accepted!");
                    } else {
                        Flash::error("The team that invited you already participates in a season or event that another one of your teams is already participating in.");
                    }
                } else {
                    Flash::error("The team that invited you already has the maximum number of players.");
                }
            } else {
                Flash::error("Something went wrong accepting this invitation.");
            }
        } else {
            Flash::error("You were not invited by the team.");
        }
        return Redirect::refresh();
    }

    public function onWithdraw()
    {
        $user = Auth::getUser();

        if ($user != null) {
            $this->sloth = $user->sloth;
        }

        $app = Applications::find(post("id"));
        $team = Teams::find($app->team_id);

        if ($app->user_id == $this->sloth->user_id) {
            $app->withdrawn = 1;
            $app->save();
        } elseif ($team->captain->id == $this->sloth->id) {
            $app->withdrawn = 1;
            $app->save();
        }

        Flash::success("Application successfully withdrawn");
        return Redirect::refresh();
    }
}
