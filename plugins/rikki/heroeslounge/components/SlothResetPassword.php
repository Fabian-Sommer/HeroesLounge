<?php namespace Rikki\Heroeslounge\Components;

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
use RainLab\User\Models\User as UserModel;
use RainLab\User\Components\ResetPassword as ResetPassword;

class SlothResetPassword extends ResetPassword
{
    public function componentDetails()
    {
        return [
            'name'        => 'SlothPasswordReset',
            'description' => 'Handle password reset'
        ];
    }

    public function defineProperties()
    {
        return [
            'paramCode' => [
                'title'       => 'rainlab.user::lang.reset_password.code_param',
                'description' => 'rainlab.user::lang.reset_password.code_param_desc',
                'type'        => 'string',
                'default'     => 'code'
            ]
        ];
    }

    public function onRun()
    {
        if (Auth::getUser()) {
            return Redirect::to('/');
        }
    }

    /**
     * Trigger the password reset email
     */
    public function onRestorePassword()
    {
        try {
            $rules = [
                'email' => 'required|email|between:6,255'
            ];

            $validation = Validator::make(post(), $rules);
            if ($validation->fails()) {
                throw new ValidationException($validation);
            }

            if (!$user = UserModel::findByEmail(post('email'))) {
                throw new ApplicationException(trans('rainlab.user::lang.account.invalid_user'));
            }

            $code = implode('!', [$user->id, $user->getResetPasswordCode()]);
            $link = $this->controller->currentPageUrl([
                $this->property('paramCode') => $code
            ]);

            $data = [
                'name' => $user->username,
                'link' => $link,
                'code' => $code
            ];

            Mail::send('rainlab.user::mail.restore', $data, function ($message) use ($user) {
                $message->to($user->email, $user->full_name);
            });
        } catch (Exception $ex) {
            Flash::error($ex->getMessage() + "potato");
        }
    }

    /**
     * Perform the password reset
     */
    public function onResetPassword()
    {
        try {
            $rules = [
                'code'     => 'required',
                'password' => 'required|between:4,255'
            ];

            $validation = Validator::make(post(), $rules);
            if ($validation->fails()) {
                throw new ValidationException($validation);
            }

            /*
             * Break up the code parts
             */
            $parts = explode('!', post('code'));
            if (count($parts) != 2) {
                throw new ValidationException(['code' => trans('rainlab.user::lang.account.invalid_activation_code')]);
            }

            list($userId, $code) = $parts;

            if (!strlen(trim($userId)) || !($user = Auth::findUserById($userId))) {
                throw new ApplicationException(trans('rainlab.user::lang.account.invalid_user'));
            }

            if (!$user->attemptResetPassword($code, post('password'))) {
                throw new ValidationException(['code' => trans('rainlab.user::lang.account.invalid_activation_code')]);
            }
        } catch (Exception $ex) {
            Flash::error($ex->getMessage());
        }
    }
}
