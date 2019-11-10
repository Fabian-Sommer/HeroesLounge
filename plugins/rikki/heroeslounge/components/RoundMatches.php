<?php namespace Rikki\Heroeslounge\Components;

 
use Cms\Classes\ComponentBase;
use Rikki\Heroeslounge\Models\Match;
use Rikki\Heroeslounge\Models\Team as Teams;
use Rikki\Heroeslounge\Models\Sloth;
use Rikki\Heroeslounge\Models\Season as Seasons;
use Rikki\Heroeslounge\Models\Division as Divisions;

use Request;
use Log;

class RoundMatches extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Recent Results',
            'description' => 'Allows users to view Recent Results'
        ];
    }

    public $matches = null;
    public $showLogo = false;
    public $showName = false;
    public $round = null;

    public function onRun()
    {
        $this->addCss('/plugins/rikki/heroeslounge/assets/css/heroeslounge.css');
    }
    public function onRender()
    {
        $this->showLogo = ($this->property('showLogo') == true) ? true : false;
        $this->showName = ($this->property('showName') == true) ? true : false;
        $type = $this->property('type');
        $id = $this->property('id');
        $this->round = $this->property('round');
        if ($type != 'sloth') {
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
            }
            if ($this->round !== null) {
                $this->matches = $myData->matches()->with('teams', 'teams.logo')->where('round', $this->round)->get();
            } else {
                $data = $myData->matches()->with('teams', 'teams.logo', 'division')->orderBy('created_at', 'DESC')->get();
                $this->matches = $data->groupBy(function ($match) {
                    return $match->division ? $match->division->longTitle : ($match->playoff ? $match->playoff->longTitle : null);
                });
            }
        } else {
            $data = Sloth::findOrFail($id)->gameParticipations->map(function ($gp) {
                return $gp->game ? $gp->game->match : null;
            })
            ->reject(function ($item) {
                return $item == null;
            })
            ->unique('id')
            ->sortByDesc('created_at');
            Log::info(json_encode($data));
            $this->matches = $data->groupBy(function ($match) {
                return $match->division ? $match->division->longTitle : ($match->playoff ? $match->playoff->longTitle : null);
            });
        }
    }

    public function defineProperties()
    {
        return [
            'showLogo' => [
                'title' => 'Show Logo',
                'description' => 'Decides whether or not to show Team Logos',
                'default' => false,
                'type' => 'checkbox'
            ],
            'showName' => [
                'title' => 'Show TeamName',
                'description' => 'Decides whether or not to show Team Names',
                'default' => true,
                'type' => 'checkbox'
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
            ],
            'round' => [
                'title' => 'Round',
                'description' => 'The specific round to take data from',
                'default' => 1,
                'type' => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'The Max Item property can contain only numeric symbols'
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
