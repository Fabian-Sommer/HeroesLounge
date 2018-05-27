<?php namespace Rikki\LoungeStatistics\Components;

use Cms\Classes\ComponentBase;
use Rikki\Heroeslounge\Models\Game;
use Rikki\Heroeslounge\Models\GameParticipation as GP;

use Db;

class GameStatistics extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'GameStatistics',
            'description' => 'Provides HeroesLounge GameStatistics'
        ];
    }

    public $game = null;

    public function init()
    {
        $this->addJs('assets/js/datatables.min.js');
        $this->addCss('assets/css/datatables.min.css');
    }

    public function onRender()
    {
    
        $this->game = Game::find($this->property('game_id'));
    }



    public function defineProperties()
    {
        return [
            'game_id' => [
                'title' => 'Game ID',
                'description' => 'Game ID to grab data from',
                'default' => 0,
                'type' => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'The game_id property can contain only numeric symbols'
            ]
            ];
    }


}
