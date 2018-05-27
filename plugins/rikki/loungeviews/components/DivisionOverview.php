<?php namespace Rikki\LoungeViews\Components;

 
use Cms\Classes\ComponentBase;
use Rikki\Heroeslounge\Models\Division;
use Rikki\Heroeslounge\Models\Season;

use Redirect;
use Flash;

class DivisionOverview extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'DivisionOverview',
            'description' => 'Displays a Division Overview'
        ];
    }
    public $div = null;
    public function init()
    {
        $s = Season::with('divisions')->where('slug',$this->param('slug'))->first();
        if($s)
        {
            $this->div = $s->divisions()->where('slug',$this->param('divslug'))->first();
            if (!$this->div) {
                Flash::error('Division could not be found!');
            } else {
                $this->page->title = $this->div->title;
                $component = $this->addComponent(
                                'Rikki\LoungeViews\Components\RecentResults',
                                'recentResults',
                                [
                                    'deferredBinding'   => true,
                                    'maxItems'           => $this->property('maxItems'),
                                    'type' => 'division'
                                ]
                            );
                $component = $this->addComponent(
                    'Rikki\Heroeslounge\Components\UpcomingMatches',
                    'upcomingMatches',
                    [
                        'deferredBinding'   => true,
                        'daysInFuture'           => $this->property('daysInFuture'),
                        'showLogo'          => true,
                        'showName' => false,
                        'showCasters' => false,
                        'type' => 'division'
                    ]
                );
                $component = $this->addComponent(
                    'Rikki\LoungeViews\Components\DivisionTable',
                    'divisionTable',
                    [
                        'deferredBinding'   => true
                    ]
                );
                $component = $this->addComponent(
                    'Rikki\Heroeslounge\Components\RoundMatches',
                    'roundMatches',
                    [
                        'deferredBinding'   => true,
                        'showLogo' => true,
                        'showName' => true,
                        'type' => 'division'
                    ]
                );
                $component = $this->addComponent(
                    'Rikki\LoungeViews\Components\TimelineEntries',
                    'timeLine',
                    [
                        'deferredBinding'   => true,
                        'maxItems' => 15,
                        'type' => 'division'
                    ]
                );
            }
        }
       
    }

    public function onRun()
    {
        $this->addJs('/plugins/rikki/heroeslounge/assets/js/ResizeSensor.js');
        $this->addJs('/plugins/rikki/heroeslounge/assets/js/ElementQueries.js');
        $this->addCss('/plugins/rikki/heroeslounge/assets/css/heroeslounge.css');
    }
    public function defineProperties()
    {
        return [
            'maxItems' => [
                'title' => 'MaxItems',
                'description' => 'The number of RecentResults  items to get.',
                'default' => 7,
                'type' => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'The timeline item property can contain only numeric symbols'
            ],
            'daysInFuture' => [
                  'title' => 'Days in Future',
                'description' => 'Number of days to grab Matches from',
                'default' => 7,
                'type' => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'The daysInFuture property can contain only numeric symbols'
            ]
        ];
    }
}
