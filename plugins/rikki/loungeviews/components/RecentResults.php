<?php namespace Rikki\LoungeViews\Components;

 
use Cms\Classes\ComponentBase;
use Rikki\Heroeslounge\Models\Match;
use Rikki\Heroeslounge\Models\Team as Teams;
use Rikki\Heroeslounge\Models\Season as Seasons;
use Rikki\Heroeslounge\Models\Division as Divisions;

use Request;

class RecentResults extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Recent Results',
            'description' => 'Allows users to view Recent Results'
        ];
    }
    public $matches = null;



    public function onRun()
    {
    }
    public function onRender()
    {
        $type = $this->property('type');
        $id = $this->property('id');
        $myData = null;
        switch ($type) {
            case 'team':
                $myData = Teams::findOrFail($id);
                break;
            case 'season':
                $myData = Seasons::findOrFail($id);
                break;
            case 'division':
                $myData = Divisions::findOrFail($id);
                break;
            /* CURRENTLY NOT IMPLEMENTED */
            /*case 'playoff':
                $myData = Playoffs::find($id);
                break;*/
            case 'all':
                /*CURRENTLY NOT IMPLEMENTED*/
                break;
        }
        $this->matches = $myData->matches()->where('is_played', 1)->with('teams', 'teams.logo')->orderBy('updated_at', 'desc')->take($this->property('maxItems'))->get();
    }


    public function defineProperties()
    {
        return [
            'maxItems' => [
                'title' => 'MaxItems',
                'description' => 'The most amount of RecentResults to Show',
                'default' => 5,
                'type' => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'The Max Item property can contain only numeric symbols'
            ],
            'type' => [
                'title' => 'Type',
                'description' => 'Entity type of which recent results shall be shown',
                'type' => 'dropdown',
                'placeholder' => 'Select Entity',
                'required' => 'true',
                'default' => 'all',
                'options' => ['all' => 'All','team' => 'Team','season' => 'Season','division' => 'Division','playoff'=>'Playoff']
            ],
            'id' => [
                'title' => 'Entity',
                'description' => 'The specific Entity to take data from - not needed for All',
                'default' => 'all',
                'type' => 'dropdown',
                'depends' => ['type'],
                'placeholder' => 'Select specific Entity'
            ]
        ];
    }

    public function getIdOptions()
    {
        $type = Request::input('type');
        $myData = [];
        switch ($type) {
            case 'team':
                $myData = Teams::all();
                break;
            case 'season':
                $myData = Seasons::all();
                break;
            case 'division':
                $myData = Divisions::all();
                break;
            /* CURRENTLY NOT IMPLEMENTED */
            /*case 'playoff':
                $myData = Playoffs::find($id);
                break;*/
            case 'all':
                $myData[0] = 'All';
                return $myData;
        }
        $retOptions = [];
        foreach ($myData as $entity) {
            $retOptions[$entity->id] = $entity->title;
        }
        return $retOptions;
    }
}
