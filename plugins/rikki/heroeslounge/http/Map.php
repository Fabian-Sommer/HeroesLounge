<?php namespace Rikki\Heroeslounge\Http;

use Backend\Classes\Controller;
use Rikki\Heroeslounge\Models\Map as MapModel;
use Input;
/**
 * Map Back-end Controller
 */
class Map extends Controller
{
    public $implement = [
        'Mohsin.Rest.Behaviors.RestController'
    ];

    public $restConfig = 'config_rest.yaml';

   
    public function getEnabled()
    {
        $data = Input::all();
        $enabled = array_get($data,'enabled');
        return MapModel::where('enabled',$enabled)->get();
    }
    
    
}
