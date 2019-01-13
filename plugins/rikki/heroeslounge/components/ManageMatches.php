<?php namespace Rikki\Heroeslounge\Components;


use Cms\Classes\ComponentBase;
use Rikki\Heroeslounge\Models\Team as Teams;



use Auth;
use Flash;
use Carbon\Carbon;
use Redirect;
use Input;
use Validator;
use ValidationException;
use Db;

class ManageMatches extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Manage Matches',
            'description' => 'Allows Captains to manage their matches'
        ];
    }

    public $user = null;
    public $matches = null;
    public $team = null;
    public function init()
    {
        $this->user = Auth::getUser();
        if ($this->user == null) {
            Flash::error('You are not authenticated!');
        } else {
            $this->team = Teams::where('slug', $this->param('slug'))->first();
            if ($this->team) {
                $this->matches = $this->team->matches()->where(function ($q) {
                    $q->where('is_played', false);
                })->get();

                $component = $this->addComponent(
                            'Rikki\Heroeslounge\Components\UpdateMatch',
                            'updateMatch',
                            [
                                                   'deferredBinding'   => true,
                            ]
                );

                $component = $this->addComponent(
                            'Rikki\Heroeslounge\Components\ScheduleMatch',
                            'scheduleMatch',
                            [
                                'deferredBinding'   => true,
                            ]

                );
            }
        }
    }
}
