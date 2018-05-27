<?php namespace Rikki\Heroeslounge\Http;

use Backend\Classes\Controller;
use Rikki\Heroeslounge\Models\Season as SeasonModel;

/**
 * Season Back-end Controller
 */
class Season extends Controller
{
    public $implement = [
        'Mohsin.Rest.Behaviors.RestController'
    ];

    public $restConfig = 'config_rest.yaml';

    public function indexAll()
    {
        return SeasonModel::all();
    }

    public function teams($id)
    {
        return SeasonModel::findOrFail($id)->teams;
    }

    public function divisions($id)
    {
        return SeasonModel::findOrFail($id)->divisions;
    }

    public function playoffs($id)
    {
        return SeasonModel::findOrFail($id)->playoffs;
    }
}
