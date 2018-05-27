<?php namespace Rikki\LoungeStatistics;

use Illuminate\Support\Facades\Event;
use Rikki\Heroeslounge\Models\Timeline;
use System\Classes\PluginBase;

use Flash;

class Plugin extends PluginBase
{
    public $require = ['Rikki.Heroeslounge'];

    public function registerComponents()
    {
        return [
			'Rikki\LoungeStatistics\Components\Statistics' => 'Statistics',
            'Rikki\LoungeStatistics\Components\CasterStatistics' => 'CasterStatistics',
            'Rikki\LoungeStatistics\Components\GameStatistics' => 'GameStatistics',
            'Rikki\LoungeStatistics\Components\TeamStatistics' => 'TeamStatistics',
            'Rikki\LoungeStatistics\Components\SlothStatistics' => 'SlothStatistics',
        	'Rikki\LoungeStatistics\Components\HeroDetails' => 'HeroDetails'
			
        ];
    }

    public function registerSettings()
    {
    }

    public function registerPermissions()
    {
     
    }

    public function register(){
	
    }

	public function registerSchedule($schedule)
	{

	}


    public function boot()
	{

    }

}
