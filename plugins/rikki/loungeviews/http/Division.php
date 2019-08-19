<?php namespace Rikki\LoungeViews\Http;

use Backend\Classes\Controller;
use Rikki\Heroeslounge\Models\Division as DivisionModel;
use Rikki\Heroeslounge\Classes\Helpers\TimezoneHelper;

use Carbon\Carbon;

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

    public function recentresults($id)
    {
        $timestamp = Carbon::today(TimezoneHelper::DEFAULT_TIMEZONE);
        $timestamp->subDays(2);

        return DivisionModel::findOrFail($id)->matches->filter(function ($match) use ($timestamp) {
            return $match->is_played == true && $match->wbp > $timestamp;
        })->all();
    }
}
