<?php namespace Rikki\Heroeslounge\Http;

use Backend\Classes\Controller;
use Rikki\Heroeslounge\Models\Talent as TalentModel;
/**
 * Talent Back-end Controller
 */
class Talents extends Controller
{
    public $implement = [
        'Mohsin.Rest.Behaviors.RestController'
    ];

    public $restConfig = 'config_rest.yaml';


    public function indexAll()
    {
        return TalentModel::all();
    }
}
