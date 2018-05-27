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

use RainLab\User\Components\Account as UserAccount;
use Rikki\Heroeslounge\Models\Sloth as SlothModel;
use Rikki\Heroeslounge\Models\Team as Teams;
use Rikki\Heroeslounge\Models\Apps as Applications;
use Rikki\Heroeslounge\Models\Timeline;

class ViewApps extends ComponentBase
{
    public $sloth;
    public $apps;
    public $teams;
    public $users;

    public function componentDetails()
    {
        return [
            'name'        => 'ViewApps',
            'description' => 'Allows users to manage their applications'
        ];
    }

    public function init()
    {
        $user = Auth::getUser();

        if ($user != null) {
            $this->sloth = SlothModel::getFromUser($user);
        }
            
        if ($this->sloth->team_id != 0) {
            $this->apps = Applications::where("team_id", "=", $this->sloth->team_id)->where('withdrawn', 0)->where('accepted', 0)->get();
            $appsFrom = Db::table('rikki_heroeslounge_team_apps')->where('team_id', '=', $this->sloth->team_id)->where('withdrawn', 0)->where('accepted', 0)->lists('user_id');

            foreach ($appsFrom as $uId) {
                $this->users[$uId] = SlothModel::where('user_id', $uId)->first();
            }
        } else {
            $this->apps = Applications::where("user_id", "=", $this->sloth->user_id)->where('withdrawn', 0)->where('accepted', 0)->get();
            $appliedTo = Db::table('rikki_heroeslounge_team_apps')->where('user_id', '=', $this->sloth->user_id)->where('withdrawn', 0)->where('accepted', 0)->lists('team_id');

            foreach ($appliedTo as $tId) {
                $this->teams[$tId] = Teams::where('id', '=', $tId)->first();
            }
        }
    }

    public function onSendAccept()
    {
        try {
            $user = Auth::getUser();

            if ($user != null) {
                $this->sloth = SlothModel::getFromUser($user);
            }

            $app = Applications::find(post("id"));

            if ($app->team_id == $this->sloth->team_id) {
                $app->approved = 1;
                $app->save();
                Flash::success("Application successfully accepted. The player can now join the team by accepting the invite on his Account page.");
            }
        } catch (Exception $e) {
            Flash::error($e->getMessage());
        } finally {
            return Redirect::refresh();
        }
    }

    public function onAccept()
    {
        try {
            $user = Auth::getUser();

            if ($user != null) {
                $this->sloth = SlothModel::getFromUser($user);
            }

            $app = Applications::find(post("id"));

            if ($app->approved == 1) {
                if ($app->user_id == $this->sloth->user_id) {
                    $numOfPlayers = SlothModel::where("team_id", $app->team_id)->count();

                    if ($numOfPlayers < 9) {
                        $this->sloth->team_id = $app->team_id;
                        $this->sloth->seasons()->detach();
                        $this->sloth->save();
                        $timeline = new Timeline();
                        $timeline->type = 'Sloth.Joins.Team';
                        $timeline->save();
                        $timeline->sloths()->add($this->sloth);
                        $timeline->teams()->add(Teams::findOrFail($app->team_id));

                        $otherApps = Applications::where("user_id", $this->sloth->user_id)->where("team_id", "!=", $app->team_id)->get();

                        $otherApps->each(function ($model) {
                            $model->withdrawn = 1;
                            $model->save();
                        });

                        $app->accepted = 1;
                        $app->save();


                        if ($numOfPlayers == 8) {
                            $appsToTeam = Applications::where("team_id", $app->team_id)->get();

                            $appsToTeam->each(function ($model) {
                                $model->withdrawn = 1;
                                $model->save();
                            });
                        }

                        Flash::success("Application successfully accepted! All other applications have been deleted.");
                    } else {
                        Flash::error("The team that invited you already has the maximum number of players.");
                    }
                } else {
                    Flash::error("Something went wrong accepting this invitation.");
                }
            } else {
                Flash::error("You were not invited by the team.");
            }
        } catch (Exception $e) {
            Flash::error($e->getMessage());
        } finally {
            return Redirect::refresh();
        }
    }

    public function onWithdraw()
    {
        try {
            $user = Auth::getUser();

            if ($user != null) {
                $this->sloth = SlothModel::getFromUser($user);
            }

            $app = Applications::find(post("id"));

            if ($app->user_id == $this->sloth->user_id) {
                $app->withdrawn = 1;
                $app->save();
            } elseif ($app->team_id == $this->sloth->team_id) {
                $app->withdrawn = 1;
                $app->save();
            }

            Flash::success("Application successfully withdrawn");
        } catch (Exception $e) {
            Flash::error($e->getMessage());
        } finally {
            return Redirect::refresh();
        }
    }
}
