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


        $appliedTo = Db::table('rikki_heroeslounge_team_apps')->where('user_id', '=', $this->sloth->user_id)->lists('team_id');

        $this->teams = Db::table('rikki_heroeslounge_teams')->where("accepting_apps", "=", 1)->whereNotIn('id', $appliedTo)->get();
    }

    public function onApplicationSend()
    {
        try {
            $user = Auth::getUser();

            if ($user != null) {
                $this->sloth = SlothModel::getFromUser($user);
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
