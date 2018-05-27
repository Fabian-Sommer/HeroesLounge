<?php namespace Rikki\LoungeStatistics\Components;

use Cms\Classes\ComponentBase;
use Rikki\Heroeslounge\Models\Hero as Heroes;
use Rikki\Heroeslounge\Models\GameParticipation as GP;

use Db;

class Statistics extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Statistics',
            'description' => 'Provides HeroesLounge Statistics'
        ];
    }

    public $heroes = null;

    public function init()
    {
        $this->heroes = Heroes::orderBy('title','asc')->get();
      


    }



    public function defineProperties()
    {
       return [];
    }
}
