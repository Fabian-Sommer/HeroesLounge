<?php

namespace Rikki\LoungeViews\Components;

use Cms\Classes\ComponentBase;
use Rikki\Heroeslounge\Models\Game;
use Rikki\Heroeslounge\Models\Map;
use Rikki\Heroeslounge\Models\Match;
use Rikki\Heroeslounge\Models\Sloth;
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
    }


    public function onMyRender()
    {
        $this->match = Match::find($this->param('id'));
        $timezoneoffset = (int)$_POST['time'];
        $timezoneName = $_POST['timezone'];

        if (!in_array($timezoneName, timezone_identifiers_list())) {
            $timezoneName = "Europe/Berlin";
        }

        $containerId = "#matchtime";
        return [
            $containerId => $this->renderPartial('@matchtime', ['timezone' => $timezoneName])
        ];
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
