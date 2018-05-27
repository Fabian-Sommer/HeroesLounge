<?php namespace Rikki\LoungeStatistics\Http;

use Backend\Classes\Controller;
use Rikki\Heroeslounge\Models\Season as SeasonModel;
use Rikki\LoungeStatistics\classes\casters\CasterStatistics;

/**
 * Season Back-end Controller
 */
class Season extends Controller
{
    public $implement = [
        'Mohsin.Rest.Behaviors.RestController'
    ];

    public $restConfig = 'config_rest.yaml';

    public function casterstatistics($id)
    {
        $season = SeasonModel::findOrFail($id);
        $cs = new CasterStatistics;
        $cs->season = $season;
        $cs->calculateStats();
        $response = [];
        $response['matchesPlayed'] = $cs->totalGamesPlayed;
        $response['matchesCast'] = $cs->totalGamesCast;
        $response['coverage'] = $cs->totalGamesPercentage;
        $response['dataByRound'] = $cs->castsPerRound;
        return json_encode($response);
    }
}
