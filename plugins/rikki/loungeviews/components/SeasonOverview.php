<?php namespace Rikki\LoungeViews\Components;

use Cms\Classes\ComponentBase;
use Rikki\Heroeslounge\Models\Season;

class SeasonOverview extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'SeasonOverview',
            'description' => 'Displays a SeasonOverview'
        ];
    }

    public $season = null;
    public $user = null;

    public function init()
    {
        $this->season = Season::where('slug',$this->param('slug'))->with('divisions', 'playoffs')->first();
        if ($this->season) 
        {
            $this->page->title = $this->season->title;
            if($this->season->reg_open)
            {
                $component = $this->addComponent(
                    'Rikki\LoungeViews\Components\ParticipationOverview',
                    'participationOverview',
                    [
                        'deferredBinding'   => true
                    ]
                );
            }
        }
    }

    public function defineProperties()
    {
       return [];
    }
}
