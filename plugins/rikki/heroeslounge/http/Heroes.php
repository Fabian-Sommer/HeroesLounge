<?php namespace Rikki\Heroeslounge\Http;

use Backend\Classes\Controller;
use Rikki\Heroeslounge\Models\Hero as HeroModel;
/**
 * Sloth Back-end Controller
 */
class Heroes extends Controller
{
    public $implement = [
        'Mohsin.Rest.Behaviors.RestController'
    ];

    public $restConfig = 'config_rest.yaml';

    public function indexAll()
    {
        return HeroModel::all();
    }
    
}
