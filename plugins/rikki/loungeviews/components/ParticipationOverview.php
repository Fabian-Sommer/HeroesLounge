<?php namespace Rikki\LoungeViews\Components;


use Cms\Classes\ComponentBase;
use Rikki\Heroeslounge\Models\Season as Seasons;


class ParticipationOverview extends ComponentBase
{
    
    public $season = null;
    public function componentDetails()
    {
        return [
            'name'        => 'Participation Overview',
            'description' => 'Shows Teams & Free Agents for certain season'
        ];
    }



    public function onRender()
    {
        $this->season = Seasons::find($this->property('id'));
        if($this->season)
        {
            $this->page->title = $this->season->title.' Participation';
        }

    }

    public function defineProperties()
    {
        return[
            'id' => [
                'title' => 'Entity',
                'description' => 'The specific Season to take data from',
                'type' => 'dropdown',
                'placeholder' => 'Select specific Season'
            ]
        ];
    }

    public function getIdOptions()
    {
        $myData = Seasons::all();
        $retOptions = [];
        foreach ($myData as $entity) {
            $retOptions[$entity->id] = $entity->title;
        }
        return $retOptions;
    }
}
