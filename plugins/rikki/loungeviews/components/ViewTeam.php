<?php namespace Rikki\LoungeViews\Components;

 
use Cms\Classes\ComponentBase;
use Rikki\Heroeslounge\Models\Match;
use Rikki\Heroeslounge\Models\Team as Teams;
use Rikki\Heroeslounge\Models\Season as Seasons;
use Rikki\Heroeslounge\Models\Division as Divisions;
use Rikki\Heroeslounge\Models\Match as Matches;


use Input;
use Request;
use Carbon\Carbon;

class ViewTeam extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'View Team',
            'description' => 'Allows users to view a certain team'
        ];
    }

    public $team = null;
    public $season = null;
    public function init()
    {
        $this->addCss('/plugins/martin/ssbuttons/assets/css/social-sharing-nb.css');
        $team = Teams::where('slug', $this->param('slug'))->first();
        if ($team) 
        {
            $this->team = $team;
            $component = $this->addComponent(
                'Rikki\LoungeViews\Components\RecentResults',
                'recentResults',
                [
                    'deferredBinding'   => true,
                    'maxItems'           => $this->property('maxItems'),
                    'type' => 'team',
                    'id' => $this->team->id
                ]
            );
    
            $component = $this->addComponent(
                'Rikki\LoungeViews\Components\DivisionTable',
                'divisionTable',
                [
                    'deferredBinding'   => true,
                    'surroundingEntries'    => 4
                ]
            );
            $component = $this->addComponent(
                            'Rikki\Heroeslounge\Components\UpcomingMatches',
                            'upcomingMatches',
                            [
                                'deferredBinding'   => true,
                                'daysInFuture'  => $this->property('daysInFuture'),
                                'showLogo'  => true,
                                'showName' => false,
                                'type' => 'team',
                                'showCasters' => false,
                                'id' => $this->team->id
                            ]
                        );
                     
            $component = $this->addComponent(
                'Rikki\Heroeslounge\Components\RoundMatches',
                'roundMatches',
                [
                    'deferredBinding'   => true,
                    'showLogo' => true,
                    'showName' => true,
                    'type' => 'team',
                    'id' => $this->team->id,
                    'round' => null
                ]
            );
            $component = $this->addComponent(
                'Rikki\LoungeViews\Components\TimelineEntries',
                'timeLine',
                [
                    'deferredBinding'   => true,
                    'maxItems' => $this->property('maxItems'),
                    'maxPerSubentity' => -1,
                    'type' => 'team',
                    'id' => $this->team->id
                ]
            );
            $component = $this->addComponent(
                'Rikki\LoungeStatistics\Components\TeamStatistics',
                'teamStatistics',
                [
                    'deferredBinding'   => true,
                    'team_id' => $this->team->id
                ]
            );
        }
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
            'daysInFuture' => [
                  'title' => 'Days in Future',
                'description' => 'Number of days to grab Matches from',
                'default' => 14,
                'type' => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'The daysInFuture property can contain only numeric symbols'
            ]
        ];
    }
}
