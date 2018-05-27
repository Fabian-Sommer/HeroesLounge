<?php namespace Rikki\LoungeViews\Components;

use Lang;
use Auth;
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
use Rikki\Heroeslounge\Models\Sloth as SlothModel;
use Rikki\Heroeslounge\Models\Season;

class SideNav extends UserAccount
{
    public function componentDetails()
    {
        return [
            'name'        => 'Side Navigation',
            'description' => 'SideNavigation for the website'
        ];
    }

    public $user = null;
    public $sloth = null;
    public $hasMatches = false;
    public $seasons = null;

    public function init()
    {
        parent::init();


        $this->user = Auth::getUser();
        $this->seasons = Season::all();
        if ($this->user != null) 
        {
            $this->sloth = SlothModel::getFromUser($this->user);
        }
      

    }

    public function onLeaveTeam()
    {
        if($this->sloth)
        {
            $this->sloth->leaveTeam();
            return Redirect::refresh();
        }
    }

    public function onLogOut()
    {
        Auth::logout();
        return Redirect::refresh();
    }


 



    public function defineProperties()
    {
        return [];
    }
}
