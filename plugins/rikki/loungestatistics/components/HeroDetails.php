<?php namespace Rikki\LoungeStatistics\Components;

use Cms\Classes\ComponentBase;
use Rikki\Heroeslounge\Models\Hero as Heroes;
use Rikki\Heroeslounge\Models\Season as Seasons;
use Rikki\Heroeslounge\Models\GameParticipation as GP;

use Db;

class HeroDetails extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Hero Details',
            'description' => 'Provides HeroesLounge Hero Details'
        ];
    }

    public $hero = null;
    public $seasons = null;
    public $selSeason = null;
    public $divisions = null;

    public function init()
    {
        $this->hero = Heroes::where('title',$this->param('slug'))->first();
        $this->selSeason = Seasons::where('title',$this->param('season'))->first();
        $this->seasons = Seasons::all();
        if(isset($this->selSeason))
        $this->divisions = $this->selSeason->divisions;

    }



    public function defineProperties()
    {
       return [];
    }
}
