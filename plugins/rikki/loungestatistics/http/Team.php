<?php namespace Rikki\LoungeStatistics\Http;

use Backend\Classes\Controller;
use Rikki\Heroeslounge\Models\Team as TeamModel;
use Rikki\HeroesLounge\Models\Season;
use Rikki\LoungeStatistics\classes\statistics\Statistics;

class Team extends Controller
{
    public $implement = [
        'Mohsin.Rest.Behaviors.RestController'
    ];

    public $restConfig = 'config_rest.yaml';

    public function herostatistics($id)
    {
        $team = TeamModel::findOrFail($id);
        return Statistics::calculateHeroStatisticsForTeam($team, null);
    }

    public function seasonHerostatistics($id, $seasonId)
    {
        $season = Season::findOrFail($seasonId);
        $team = TeamModel::findOrFail($id);
        return Statistics::calculateHeroStatisticsForTeam($team, $season);
    }

    public function mapstatistics($id)
    {
        $team = TeamModel::findOrFail($id);
        return Statistics::calculateMapStatisticsForTeam($team, null);
    }

    public function seasonMapstatistics($id, $seasonId)
    {
        $season = Season::findOrFail($seasonId);
        $team = TeamModel::findOrFail($id);
        return Statistics::calculateMapStatisticsForTeam($team, $season);
    }
}
