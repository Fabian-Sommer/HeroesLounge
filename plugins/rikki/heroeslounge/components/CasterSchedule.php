<?php namespace Rikki\Heroeslounge\Components;

 

use Cms\Classes\ComponentBase;
use Rikki\Heroeslounge\Models\Sloth;
use Auth;

class CasterSchedule extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Caster Schedule',
            'description' => 'Allows casters to see their matches'
        ];
    }

    public $caster = null;
    public function init()
    {
        $user = Auth::getUser();
        if ($user) {
            $this->caster = Sloth::getFromUser($user);

            $component = $this->addComponent(
                            'Rikki\Heroeslounge\Components\UpcomingMatches',
                            'upcomingMatchesPending',
                            [
                                'deferredBinding'   => true,
                                'daysInFuture'           => $this->property('daysInFuture'),
                                'showLogo'          => true,
                                'showName' => false,
                                'type' => 'caster',
                                'showCasters' => true,
                                'id' => $this->caster->id,
                                'casterFilter' => 'pending'
                            ]
                        );
            $component = $this->addComponent(
                            'Rikki\Heroeslounge\Components\UpcomingMatches',
                            'upcomingMatchesAccepted',
                            [
                                'deferredBinding'   => true,
                                'daysInFuture'           => $this->property('daysInFuture'),
                                'showLogo'          => true,
                                'showName' => false,
                                'type' => 'caster',
                                'showCasters' => true,
                                'id' => $this->caster->id,
                                'casterFilter' => 'accepted'
                            ]
                        );
            $component = $this->addComponent(
                            'Rikki\Heroeslounge\Components\UpcomingMatches',
                            'upcomingMatchesDenied',
                            [
                                'deferredBinding'   => true,
                                'daysInFuture'           => $this->property('daysInFuture'),
                                'showLogo'          => true,
                                'showName' => false,
                                'type' => 'caster',
                                'showCasters' => true,
                                'id' => $this->caster->id,
                                'casterFilter' => 'denied'
                            ]
                        );
        }
    }

    public function defineProperties()
    {
        return [
            'daysInFuture' => [
                'title' => 'Days in Future',
                'description' => 'Number of days to grab Matches from',
                'default' => 50,
                'type' => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'The daysInFuture property can contain only numeric symbols'
            ]
        ];
    }
}
