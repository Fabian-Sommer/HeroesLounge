<?php namespace Rikki\LoungeViews\Http;

use Backend\Classes\Controller;
use Rikki\Heroeslounge\Models\Division as DivisionModel;

/**
 * Division Back-end Controller
 */
class Division extends Controller
{
    public $implement = [
        'Mohsin.Rest.Behaviors.RestController'
    ];

    public $restConfig = 'config_rest.yaml';

    public function standings($id)
    {
        return DivisionModel::findOrFail($id)->getDivisionTableStandings();
    }

    public function standing($divisionId, $teamId)
    {
        $standingsTable = DivisionModel::findOrFail($divisionId)->getDivisionTableStandings();
        return $standingsTable->first(function ($team) use ($teamId) {
            return $team['id'] == $teamId;
        });
    }
}
