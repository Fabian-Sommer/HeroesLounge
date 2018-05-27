<?php namespace Rikki\LoungeViews\Components;

 
use Cms\Classes\ComponentBase;
use Rikki\Heroeslounge\Models\Division as Divisions;

use Request;

class ExtendedDivisionTable extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'ExtendedDivisionTable',
            'description' => 'Allows staff to view rigs'
        ];
    }
    public $teams = null;

    public function onRender()
    {
        $id = $this->param('id');
        $div = Divisions::find($id);
        if ($div) {
            $this->teams = $div->teams()->withPivot('win_count')->withPivot('match_count')->withPivot('free_win_count')->orderBy('win_count', 'desc')->orderBy('match_count')->get();
        }
    }


    public function defineProperties()
    {
        return[
            'id' => [
                'title' => 'Entity',
                'description' => 'The specific Entity to take data from - not needed for All',
                'type' => 'dropdown',
                'placeholder' => 'Select specific Entity'
            ]
        ];
    }

    public function getIdOptions()
    {
        $myData = Divisions::all();
        $retOptions = [];
        foreach ($myData as $entity) {
            $retOptions[$entity->id] = $entity->title;
        }
        return $retOptions;
    }
}
