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

class Navigation extends UserAccount
{
    public function componentDetails()
    {
        return [
            'name'        => 'Navigation',
            'description' => 'MainNavigation for the website'
        ];
    }

    public $user = null;
    public $sloth = null;
    public $amateurseasons = null;
    public $divsseasons = null;

    public function init()
    {
        parent::init();


        $this->user = Auth::getUser();
        $this->amateurseasons = Season::where('type', 1)->orderBy('created_at','desc')->get();
        $this->divsseasons = Season::where('type', 2)->orderBy('created_at','desc')->get();
        if ($this->user != null) {
            $this->sloth = SlothModel::getFromUser($this->user);
        }
        $component = $this->addComponent(
            'RainLab\Pages\Components\StaticMenu',
            'staticMenuGuides',
            [
                'deferredBinding'   => true,
                'code' => 'guides'
            
            ]
        );
        $this->addComponent(
            'RainLab\Pages\Components\StaticMenu',
            'staticMenuGeneral',
            [
                'deferredBinding'   => true,
                'code' => 'general'
            
            ]
        );
    }





    public function onRun()
    {
        return parent::onRun();
    }



    public function onSignIn()
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
            $this->sloth = SlothModel::getFromUser($user);

            /*
             * Redirect to the intended page after successful sign in
             */
            $redirectUrl = $this->pageUrl($this->property('redirect'))
                ?: $this->property('redirect');

            if ($redirectUrl == Request::root() . "/user/forgotpassword") {
                $redirectUrl = Request::root();
            }

            if ($redirectUrl = input('redirect', $redirectUrl)) {
                return Redirect::intended($redirectUrl);
            }
            else
            {
                return Redirect::refresh();
            }
        } catch (Exception $ex) {
            if (Request::ajax()) {
                throw $ex;
            } else {
                Flash::error($ex->getMessage());
            }
        }
    }


 


    public function defineProperties()
    {
        return [];
    }
}
