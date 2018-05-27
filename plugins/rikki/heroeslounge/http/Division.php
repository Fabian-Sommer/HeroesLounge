<?php namespace Rikki\Heroeslounge\Http;

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

    public function indexAll()
    {
        return DivisionModel::all();
    }

    public function teams($id)
    {
        return DivisionModel::findOrFail($id)->teams;
    }

}
