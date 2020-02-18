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
        $team = TeamModel::WithMatches()->where('id', $id)->firstOrFail();
        return Statistics::calculateHeroStatisticsForTeam($team, null);
    }

    public function seasonHerostatistics($id, $seasonId)
    {
        $season = Season::findOrFail($seasonId);
        $team = TeamModel::WithMatches()->where('id', $id)->firstOrFail();
        return Statistics::calculateHeroStatisticsForTeam($team, $season);
    }

    public function mapstatistics($id)
    {
        $team = TeamModel::with('matches', 'matches.games', 'matches.games.map', 'matches.games.replay', 'matches.games.gameParticipations')->where('id', $id)->firstOrFail();
        return Statistics::calculateMapStatisticsForTeam($team, null);
    }

    public function seasonMapstatistics($id, $seasonId)
    {
        $season = Season::findOrFail($seasonId);
        $team = TeamModel::with('matches', 'matches.games', 'matches.games.map', 'matches.games.replay', 'matches.games.gameParticipations')->where('id', $id)->firstOrFail();
        return Statistics::calculateMapStatisticsForTeam($team, $season);
    }
}
