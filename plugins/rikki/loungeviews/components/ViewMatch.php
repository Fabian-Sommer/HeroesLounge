<?php

namespace Rikki\LoungeViews\Components;

use Cms\Classes\ComponentBase;
use Rikki\Heroeslounge\Models\Game;
use Rikki\Heroeslounge\Models\Map;
use Rikki\Heroeslounge\Models\Match;
use Rikki\Heroeslounge\Models\Sloth;
use Rikki\Heroeslounge\Classes\Helpers\TimezoneHelper;
use Auth;
use Input;
use Redirect;
use Validator;
use Flash;
use System\Models\File;

class ViewMatch extends ComponentBase
{
    public $match = null;
    public $decoded_playoff_position = null;
    public $timezone = null;
    public $timezoneOffset = null;

    public function componentDetails()
    {
        return [
            'name'        => 'View Match',
            'description' => 'Allows Users to view a certain match'
        ];
    }

    public function init()
    {
        $component = $this->addComponent(
            'Rikki\LoungeStatistics\Components\GameStatistics',
            'gameStatistic',
            [
                'deferredBinding'   => true
            ]
        );
    }

    public function onRender()
    {
        $this->match = Match::find($this->param('id'));
        if ($this->match->playoff_position != null) {
            $this->decoded_playoff_position = Match::decodePlayoffPosition($this->match->playoff_position);
        }
        $this->timezone = TimezoneHelper::getTimezone();
        $this->timezoneOffset = TimezoneHelper::getTimezoneOffset();
    }

    public function defineProperties()
    {
        return [
            'id' => [
                'title' => 'MatchID',
                'description' => 'MatchID to grab data from',
                'type' => 'string',
                'required' => true,
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'The MatchID property can contain only numeric symbols'
            ]
        ];
    }
}
