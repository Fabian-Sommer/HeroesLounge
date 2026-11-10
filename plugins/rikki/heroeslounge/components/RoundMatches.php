<?php namespace Rikki\Heroeslounge\Components;

 
use Cms\Classes\ComponentBase;
use Rikki\Heroeslounge\Models\MatchModel;
use Rikki\Heroeslounge\Models\Team as Teams;
use Rikki\Heroeslounge\Models\Sloth;
use Rikki\Heroeslounge\Models\Season as Seasons;
use Rikki\Heroeslounge\Models\Division as Divisions;
use Rikki\Heroeslounge\classes\helpers\MatchContextResolver;

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
    public $groups = [];
    public $showLogo = false;
    public $showName = false;
    public $round = null;
    public $lazy = false;
    public $byCompetition = false;

    public function onRun()
    {
        $this->addCss('/plugins/rikki/heroeslounge/assets/css/heroeslounge.css');
        $this->addJs('/plugins/rikki/heroeslounge/assets/js/lazy-content.js');
    }

    public function onRender()
    {
        $this->prepareVars();
        $type = $this->property('type');
        $id = $this->property('id');

        if ($this->round === null && $this->byCompetition) {
            // Only the competition list and the matches of the first one are needed up front,
            // the other competitions are loaded when their tab is opened.
            if (!$this->lazy) {
                $this->loadGroups();
            }
            return;
        }

        if ($type == 'sloth') {
            $data = Sloth::findOrFail($id)->gameParticipations->map(function ($gp) {
                return $gp->game ? $gp->game->match : null;
            })
            ->reject(function ($item) {
                return $item == null;
            })
            ->unique('id')
            ->sortByDesc('created_at');
            $this->matches = $data->groupBy(function ($match) {
                return $match->division ? $match->division->longTitle : ($match->playoff ? $match->playoff->longTitle : null);
            });
            return;
        }

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
    }

    /**
     * Loads the competition tabs, with the matches of the first one.
     */
    public function onLoadMatches()
    {
        $this->prepareVars();
        $this->loadGroups();
        return [
            '#' . $this->alias . '_container' => $this->renderPartial('@groups'),
        ];
    }

    /**
     * Loads the matches of one competition tab.
     */
    public function onLoadMatchGroup()
    {
        $this->prepareVars();
        $key = (string) input('group');
        $this->loadGroups($key);
        $group = collect($this->groups)->firstWhere('key', $key);
        return [
            '#' . $this->alias . '_group_' . preg_replace('/[^a-z0-9]/', '', $key) => $this->renderPartial('@showMatchGroup', [
                'matchGroup' => $group ? $group['matches'] : [],
                'showLogo' => $this->showLogo,
                'showName' => $this->showName,
            ]),
        ];
    }

    protected function prepareVars()
    {
        $this->showLogo = ($this->property('showLogo') == true) ? true : false;
        $this->showName = ($this->property('showName') == true) ? true : false;
        $this->round = $this->property('round');
        $this->lazy = ($this->property('lazy') == true) ? true : false;
        $this->byCompetition = in_array($this->property('type'), ['team']);
    }

    protected function matchesQuery()
    {
        return MatchContextResolver::teamMatches($this->property('id'));
    }

    /**
     * Groups the matches by competition, most recently played competition first, and loads
     * the matches of the group with the given key (default: the first group).
     */
    protected function loadGroups($loadKey = null)
    {
        $rows = $this->matchesQuery()
            ->selectRaw('div_id, playoff_id, max(created_at) as latest')
            ->groupBy('div_id', 'playoff_id')
            ->toBase()
            ->get();
        $resolver = new MatchContextResolver($rows);

        // Most recently played competition first. Competitions are grouped by title, like before;
        // a group is identified by its most recent competition.
        $rows = $rows->sortBy(function ($row) use ($resolver) {
            return $resolver->competitionKey($row->div_id, $row->playoff_id);
        })->sortByDesc('latest');

        $groups = [];
        foreach ($rows as $row) {
            $title = $resolver->competitionTitle($row->div_id, $row->playoff_id);
            if (!isset($groups[$title])) {
                $groups[$title] = [
                    'key' => $resolver->competitionKey($row->div_id, $row->playoff_id),
                    'title' => $title,
                    'pairs' => [],
                    'matches' => null,
                ];
            }
            $groups[$title]['pairs'][] = [$row->div_id, $row->playoff_id];
        }
        $groups = array_values($groups);

        $loadKey = $loadKey ?? ($groups[0]['key'] ?? null);
        foreach ($groups as &$group) {
            if ($group['key'] === $loadKey) {
                $group['matches'] = $this->matchesOfCompetition($group['pairs']);
            }
        }
        unset($group);
        $this->groups = $groups;
    }

    protected function matchesOfCompetition($pairs)
    {
        return $this->matchesQuery()
            ->where(function ($query) use ($pairs) {
                foreach ($pairs as list($divisionId, $playoffId)) {
                    $query->orWhere(function ($query) use ($divisionId, $playoffId) {
                        $divisionId === null ? $query->whereNull('div_id') : $query->where('div_id', $divisionId);
                        $playoffId === null ? $query->whereNull('playoff_id') : $query->where('playoff_id', $playoffId);
                    });
                }
            })
            ->with('teams', 'teams.logo')
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->get();
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
            ],
            'lazy' => [
                'title' => 'Lazy',
                'description' => 'Only load the matches once the list becomes visible (team without round)',
                'default' => false,
                'type' => 'checkbox'
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
