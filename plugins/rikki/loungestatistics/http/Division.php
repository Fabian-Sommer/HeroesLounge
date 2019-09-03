<?php namespace Rikki\LoungeStatistics\Http;

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

    public function herostatistics($id)
    {
        return DivisionModel::findOrFail($id)->herostatistics();
    }
}
