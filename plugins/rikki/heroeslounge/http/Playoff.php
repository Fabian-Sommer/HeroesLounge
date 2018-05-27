<?php namespace Rikki\Heroeslounge\Http;

use Backend\Classes\Controller;
use Rikki\Heroeslounge\Models\Playoff as PlayoffModel;

/**
 * Season Back-end Controller
 */
class Playoff extends Controller
{
    public $implement = [
        'Mohsin.Rest.Behaviors.RestController'
    ];

    public $restConfig = 'config_rest.yaml';

    public function indexAll()
    {
        return PLayoffModel::all();
    }

    public function matches($id)
    {
        return PlayoffModel::findOrFail($id)->matches;
    }

    public function divisions($id)
    {
        return PlayoffModel::findOrFail($id)->divisions;
    }
}
