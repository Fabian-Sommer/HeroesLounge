<?php namespace Rikki\LoungeStatistics\Http;

use Backend\Classes\Controller;
use Rikki\Heroeslounge\Models\Sloth as SlothModel;

/**
 * Season Back-end Controller
 */
class Sloth extends Controller
{
    public $implement = [
        'Mohsin.Rest.Behaviors.RestController'
    ];

    public $restConfig = 'config_rest.yaml';

    public function herostatistics($id)
    {
        return SlothModel::findOrFail($id)->herostatistics();
    }
}
