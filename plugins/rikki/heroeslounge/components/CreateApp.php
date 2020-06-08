<?php namespace Rikki\Heroeslounge\Components;

use Auth;
use Event;
use Flash;
use Input;
use Request;
use Redirect;
use ApplicationException;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;
use Exception;

use Rikki\Heroeslounge\Models\Sloth as SlothModel;
use Rikki\Heroeslounge\Models\Team as Teams;
use Rikki\Heroeslounge\Models\Apps as Application;

class CreateApp extends ComponentBase
{
    public $sloth = null;
    public $teams;

    public function componentDetails()
    {
        return [
            'name'        => 'CreateApp',
            'description' => 'Allows a user to apply to a team'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        parent::onRun();

        $user = Auth::getUser();

        if ($user != null) {
            $this->sloth = SlothModel::getFromUser($user);
        } else {
            return Redirect::to('/');
        }

        $appliedTo = Application::where('user_id', $this->sloth->user_id)->pluck('team_id');
        $this->teams = Teams::where('disbanded', 0)->where('accepting_apps', 1)->where('region_id', $this->sloth->region_id)->whereNotIn('id', $appliedTo)->orderBy('title')->get();
    }

    public function onApplicationSend()
    {
        try {
            $user = Auth::getUser();

            if ($user != null) {
                $this->sloth = SlothModel::getFromUser($user);
                $team = Teams::findOrFail(post('team_id'));

                if ($sloth->isInTeam($team)) {
                    Flash::error('You are already a member of ' . $team->title . '!');
                    return Redirect::refresh();
                }

                if (!$team->accepting_apps) {
                    Flash::error('That team is currently not accepting applications!');
                    return Redirect::refresh();
                }

                $app = new Application;
                $app->user_id = $this->sloth->user_id;
                $app->team_id = post('team_id');
                $app->message = post('message');
                $app->save();
                Flash::success('Application sent successfully!');
            } else {
                Flash::error("You have to be logged in to send an application");
            }
        } catch (Exception $e) {
            Flash::error($e->getMessage());
        } finally {
            return Redirect::refresh();
        }
    }
}
