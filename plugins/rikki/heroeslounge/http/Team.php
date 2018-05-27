<?php namespace Rikki\Heroeslounge\Http;

use Backend\Classes\Controller;
use Rikki\Heroeslounge\Models\Team as TeamModel;
use Input;
/**
 * Team Back-end Controller
 */
class Team extends Controller
{
    public $implement = [
        'Mohsin.Rest.Behaviors.RestController'
    ];

    public $restConfig = 'config_rest.yaml';

    public function indexAll()
    {
        return TeamModel::all();
    }
 
    public function logo($teamId)
    {
        return TeamModel::findOrFail($teamId)->logo;
    }

    public function logos()
    {
        $retVal = [];
        TeamModel::all()->each(function($t) use(&$retVal) { $retVal[] = $t->logo;});
        return $retVal;
    }
    public function sloths($teamId)
    {
        return TeamModel::findOrFail($teamId)->sloths;
    }

    public function sloth($teamId, $slothId)
    {
        return TeamModel::findOrFail($teamId)->sloths()->findOrFail($slothId);
    }

    public function slothTimelines($teamId,$slothId)
    {
        return TeamModel::findOrFail($teamId)->sloths()->findOrFail($slothId)->timeline;
    }

    public function timelines($teamId)
    {
        return TeamModel::findOrFail($teamId)->timeline;
    }

    public function matches($teamId)
    {
        return TeamModel::findOrFail($teamId)->matches;
    }

 

  
}