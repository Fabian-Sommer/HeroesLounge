<?php namespace Rikki\LoungeViews\Components;

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

class ViewApplication extends ComponentBase
{
    public $app;
    public $sloth;
    public $team;
    public $allowed = false;

    public function componentDetails()
    {
        return [
            'name'        => 'ViewApplication',
            'description' => 'Allows users and teams to inspect applications related to them'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRender()
    {
        try {
            $user = Auth::getUser();

            if ($user != null) {
                $this->sloth = SlothModel::getFromUser($user);
            } else {
                return Redirect::to('/');
            }

            $this->app = Applications::where('id', $this->param('id'))->first();

            $this->allowed = true;

            $this->team = Teams::where('id', $this->app->team_id)->first();
        } catch (Exception $ex) {
            Flash::error($ex->getMessage());
        } finally {
            Redirect::refresh();
        }
    }
}
