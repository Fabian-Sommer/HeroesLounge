<?php namespace Rikki\LoungeStatistics\Components;

use Cms\Classes\ComponentBase;
use Rikki\Heroeslounge\Models\Season as Seasons;
use RainLab\User\Models\User;
use Rikki\LoungeStatistics\classes\Casters\CasterStatistics as CSclass;

class CasterStatistics extends ComponentBase
{

	public function componentDetails()
    {
        return [
            'name'        => 'Caster Statistics',
            'description' => 'Allows casters to view statistics about which team was casted when'
        ];
    }

    public $season = null;
    public $casters = null;
    public $casterRoundData = null;
    public $teamCastData = null;
    public $divisionGamesCast = null;
    public $divisionGamesPlayed = null;
    public $divisionGamesPercentage = null;
    public $totalGamesCast = 0;
    public $totalGamesPlayed = 0;
    public $totalGamesPercentage = 0;

    public function init()
    {
        $this->season = Seasons::find($this->property('season'));
        $this->addCss('/plugins/rikki/heroeslounge/assets/css/heroeslounge.css');
        $component = $this->addComponent(
                        'RainLab\User\Components\Session',
                        'session',
                        [
                            'deferredBinding'   => true,
                            'security'           => 'all'
                        ]
                    );
    }

    public function onRender()
    {
        if ($this->season == null) {
            return;
        }
        $csc = new CSclass;
        $csc->season = $this->season;
        $csc->calculateStats();
        $this->casters = $csc->casters;
        $this->casterRoundData = $csc->casterRoundData;
        $this->teamCastData = $csc->teamCastData;
        $this->divisionGamesCast = $csc->divisionGamesCast;
        $this->divisionGamesPlayed = $csc->divisionGamesPlayed;
        $this->divisionGamesPercentage = $csc->divisionGamesPercentage;
        $this->totalGamesCast = $csc->totalGamesCast;
        $this->totalGamesPlayed = $csc->totalGamesPlayed;
        $this->totalGamesPercentage = $csc->totalGamesPercentage;
    }

    public function defineProperties()
    {
    	return [
    		'season' => [
                'title' => 'Season',
                'description' => 'Decides for what season to display stats',
                'type' => 'dropdown',
                'placeholder' => 'Select Season',
                'required' => 'true'
            ]
    	];
    }

    public function getSeasonOptions()
    {

        $myData = Seasons::all();
        $retOptions = [];
        foreach($myData as $entity)
        {
            $retOptions[$entity->id] = $entity->title;
        }
        return $retOptions;
    }
}
