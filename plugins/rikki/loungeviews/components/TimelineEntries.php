<?php namespace Rikki\LoungeViews\Components;

use Cms\Classes\ComponentBase;
use Rikki\Heroeslounge\Models\Division;
use Rikki\Heroeslounge\Models\Season;
use Rikki\Heroeslounge\Models\Sloth;
use Rikki\Heroeslounge\Models\Team;
use Rikki\Heroeslounge\Models\Match;
use Rikki\Heroeslounge\Models\Timeline;
use October\Rain\Support\Collection;
use Request;

class TimelineEntries extends ComponentBase
{
    public $timeline = null;

    public function componentDetails()
    {
        return [
            'name' => 'Timeline',
            'description' => 'Displays a timeline for a specified type.'
        ];
    }


    public function onRun()
    {
        $this->addJs('/plugins/rikki/heroeslounge/assets/js/ResizeSensor.js');
        $this->addJs('/plugins/rikki/heroeslounge/assets/js/ElementQueries.js');
        $this->addJs('/plugins/rikki/heroeslounge/assets/js/timeline.js');
        $this->addJs('/plugins/rikki/heroeslounge/assets/js/modernizr.js');
        $this->addCss('/plugins/rikki/heroeslounge/assets/css/timeline.css');
    }

    public function onRender()
    {
        $type = $this->property('type');
        $id = $this->property('id');
        $subsequent = $this->property('subsequent');
        $timeline = null;
        if ($subsequent) {
            switch ($type) {
                case 'season':
                    $timeline = new Collection([]);
                    Timeline::with('seasons', 'divisions.season', 'matches.teams.divisions.season', 'teams.divisions.season', 'teams.logo')->orderBy('created_at', 'desc')->chunk(100, function ($someTimelines) use ($id, $timeline) {
                        foreach ($allTimelines as $timelineEntry) {
                            $include = false;
                            if (!empty($timelineEntry->seasons)) {
                                foreach ($timelineEntry->seasons as $season) {
                                    if ($season->id == $id) {
                                        $include = true;
                                        break;
                                    }
                                }
                            }
                            if (!$include && !empty($timelineEntry->divisions)) {
                                foreach ($timelineEntry->divisions as $division) {
                                    if ($division->season->id == $id) {
                                        $include = true;
                                        break;
                                    }
                                }
                            }
                            if (!$include && !empty($timelineEntry->matches)) {
                                foreach ($timelineEntry->matches as $match) {
                                    foreach ($match->teams as $team) {
                                        foreach ($team->divisions as $division) {
                                            if ($division->season->id == $id) {
                                                $include = true;
                                                break(3);
                                            }
                                        }
                                    }
                                }
                            }
                            if (!$include && !empty($timelineEntry->teams)) {
                                foreach ($timelineEntry->teams as $team) {
                                    foreach ($team->divisions as $division) {
                                        if ($division->season->id == $id) {
                                            $include = true;
                                            break(2);
                                        }
                                    }
                                }
                            }
                            if (!$include && !empty($timelineEntry->sloths)) {
                                foreach ($timelineEntry->sloths as $sloth) {
                                    foreach ($sloth->teams as $key => $team) {
                                        foreach ($sloth->team->divisions as $division) {
                                            if ($division->season->id == $id) {
                                                $include = true;
                                                break(2);
                                            }
                                        }
                                    }
                                }
                            }
                            if ($include) {
                                $timeline->push($timelineEntry);
                                if ($timeline->count() == $this->property('maxItems')) {
                                    return false;
                                    break;
                                }
                            }
                        }
                    });
                    break;
                case 'division':
                    $timeline = new Collection([]);
                    Timeline::with('divisions', 'matches.teams.divisions', 'teams.divisions', 'sloths.teams.divisions', 'teams.logo')->orderBy('created_at', 'desc')
                    ->chunk(100, function ($someTimelines) use ($id, $timeline) {
                        foreach ($someTimelines as $timelineEntry) {
                            $include = false;
                            if (!empty($timelineEntry->divisions)) {
                                foreach ($timelineEntry->divisions as $division) {
                                    if ($division->id == $id) {
                                        $include = true;
                                        break;
                                    }
                                }
                            }
                            if (!$include && !empty($timelineEntry->matches)) {
                                foreach ($timelineEntry->matches as $match) {
                                    foreach ($match->teams as $team) {
                                        foreach ($team->divisions as $division) {
                                            if ($division->id == $id) {
                                                $include = true;
                                                break(2);
                                            }
                                        }
                                    }
                                }
                            }
                            if (!$include && !empty($timelineEntry->teams)) {
                                foreach ($timelineEntry->teams as $team) {
                                    foreach ($team->divisions as $division) {
                                        if ($division->id == $id) {
                                            $include = true;
                                            break(2);
                                        }
                                    }
                                }
                            }
                            if (!$include && !empty($timelineEntry->sloths)) {
                                foreach ($timelineEntry->sloths as $sloth) {
                                    foreach ($sloth->teams as $key => $team) {
                                        foreach ($team->divisions as $division) {
                                            if ($division->id == $id) {
                                                $include = true;
                                                break(2);
                                            }
                                        }
                                    }
                                }
                            }
                            if ($include) {
                                $timeline->push($timelineEntry);
                                if ($timeline->count() == $this->property('maxItems')) {
                                    return false;
                                    break;
                                }
                            }
                        }
                    });
                    break;
                case 'team':
                    $timeline = new Collection([]);
                    Timeline::with('teams', 'matches.teams', 'teams.logo')->orderBy('created_at', 'desc')->chunk(100, function ($someTimelines) use ($id, $timeline) {
                        foreach ($someTimelines as $timelineEntry) {
                            $include = false;
                            if (!empty($timelineEntry->teams)) {
                                foreach ($timelineEntry->teams as $team) {
                                    if ($team->id == $id) {
                                        $include = true;
                                        break;
                                    }
                                }
                            }
                            if (!$include && !empty($timelineEntry->matches)) {
                                foreach ($timelineEntry->matches as $match) {
                                    foreach ($match->teams as $team) {
                                        if ($team->id == $id) {
                                            $include = true;
                                            break(2);
                                        }
                                    }
                                }
                            }
                            if ($include) {
                                $timeline->push($timelineEntry);
                                if ($timeline->count() == $this->property('maxItems')) {
                                    return false;
                                    break;
                                }
                            }
                        }
                    });
                    break;
                case 'sloth':
                    $timeline = new Collection([]);
                    Timeline::with('sloths')->orderBy('created_at', 'desc')->chunk(100, function ($someTimelines) use ($id, $timeline) {
                        foreach ($someTimelines as $timelineEntry) {
                            $include = false;
                            if (!empty($timelineEntry->sloths)) {
                                foreach ($timelineEntry->sloths as $sloth) {
                                    if ($sloth->id == $id) {
                                        $include = true;
                                        break;
                                    }
                                }
                            }
                            if ($include) {
                                $timeline->push($timelineEntry);
                                if ($timeline->count() == $this->property('maxItems')) {
                                    return false;
                                    break;
                                }
                            }
                        }
                    });
                    break;
                case 'match':
                    $timeline = new Collection([]);
                    Timeline::with('matches')->orderBy('created_at', 'desc')->chunk(100, function ($someTimelines) use ($id, $timeline) {
                        foreach ($someTimelines as $timelineEntry) {
                            $include = false;
                            if (!empty($timelineEntry->matches)) {
                                foreach ($timelineEntry->matches as $match) {
                                    if ($match->id == $id) {
                                        $include = true;
                                    }
                                }
                            }
                            if ($include) {
                                $timeline->push($timelineEntry);
                                if ($timeline->count() == $this->property('maxItems')) {
                                    break;
                                }
                            }
                        }
                    });
                    break;
                case 'site':
                    $timeline = Timeline::where('rikki_heroeslounge_timeline.id', '>=', '0');
                    break;
            }
        } else {
            switch ($type) {
                case 'season':
                    $timeline = Season::find($id)->timeline->orderBy('created_at', 'desc')->get($this->property('maxItems'));
                    break;
                case 'division':
                    $timeline = Division::find($id)->timeline->orderBy('created_at', 'desc')->get($this->property('maxItems'));
                    break;
                case 'team':
                    $timeline = Team::find($id)->timeline->orderBy('created_at', 'desc')->get($this->property('maxItems'));
                    break;
                case 'sloth':
                    $timeline = Sloth::find($id)->timeline->orderBy('created_at', 'desc')->get($this->property('maxItems'));
                    break;
                case 'match':
                    $timeline = Match::find($id)->timeline->orderBy('created_at', 'desc')->get($this->property('maxItems'));
                    break;
                case 'site':
                    $timeline = new Collection([]);
                    Timeline::with('matches', 'seasons', 'divisions', 'teams', 'sloths')->orderBy('created_at', 'desc')->chunk(100, function ($someTimelines) use ($id, $timeline) {
                        foreach ($someTimelines as $timelineEntry) {
                            if (empty($timelineEntry->matches) && empty($timelineEntry->divisions) && empty($timelineEntry->seasons) && empty($timelineEntry->teams) && empty($timelineEntry->sloths)) {
                                $timeline->push($timelineEntry);
                                if ($timeline->count() == $this->property('maxItems')) {
                                    return false;
                                    break;
                                }
                            }
                        }
                    });
                    break;
            }
        }

        if ($this->property('maxPerSubentity') != -1) {
            $filteredTimeline = [];
            $entriesForSloth = [];
            $entriesForTeam = [];
            $entriesForMatch = [];
            $entriesForDivision = [];
            $entriesForSeason = [];
            $entryCount = 0;

            $maxPerSubentity = $this->property('maxPerSubentity');
            foreach ($timeline as $timelineEntry) {
                $skip = false;
                if ($type != 'sloth') {
                    foreach ($timelineEntry->sloths as $sloth) {
                        if (array_key_exists($sloth->id, $entriesForSloth)) {
                            if ($entriesForSloth[$sloth->id] >= $maxPerSubentity) {
                                $skip = true;
                                break;
                            }
                        }
                    }
                }
                if (!$skip and $type != 'match') {
                    foreach ($timelineEntry->matches as $match) {
                        if (array_key_exists($match->id, $entriesForMatch)) {
                            if ($entriesForMatch[$match->id] >= $maxPerSubentity) {
                                $skip = true;
                                break;
                            }
                        }
                    }
                }
                if (!$skip and $type != 'team') {
                    foreach ($timelineEntry->teams as $team) {
                        if (array_key_exists($team->id, $entriesForTeam)) {
                            if ($entriesForTeam[$team->id] >= $maxPerSubentity) {
                                $skip = true;
                                break;
                            }
                        }
                    }
                }
                if (!$skip and $type != 'division') {
                    foreach ($timelineEntry->divisions as $division) {
                        if (array_key_exists($division->id, $entriesForDivision)) {
                            if ($entriesForDivision[$division->id] >= $maxPerSubentity) {
                                $skip = true;
                                break;
                            }
                        }
                    }
                }
                if (!$skip and $type != 'season') {
                    foreach ($timelineEntry->seasons as $season) {
                        if (array_key_exists($season->id, $entriesForSeason)) {
                            if ($entriesForSeason[$season->id] >= $maxPerSubentity) {
                                $skip = true;
                                break;
                            }
                        }
                    }
                }
                if (!$skip) {
                    if ($entryCount < $this->property('maxItems')) {
                        $filteredTimeline[] = $timelineEntry;
                        $entryCount++;
                    } else {
                        break;
                    }


                    if ($type != 'sloth') {
                        foreach ($timelineEntry->sloths as $sloth) {
                            if (array_key_exists($sloth->id, $entriesForSloth)) {
                                $entriesForSloth[$sloth->id]++;
                            } else {
                                $entriesForSloth[$sloth->id] = 1;
                            }
                        }
                    }
                    if ($type != 'match') {
                        foreach ($timelineEntry->matches as $match) {
                            if (array_key_exists($match->id, $entriesForMatch)) {
                                $entriesForMatch[$match->id]++;
                            } else {
                                $entriesForMatch[$match->id] = 1;
                            }
                        }
                    }
                    if ($type != 'team') {
                        foreach ($timelineEntry->teams as $team) {
                            if (array_key_exists($team->id, $entriesForTeam)) {
                                $entriesForTeam[$team->id]++;
                            } else {
                                $entriesForTeam[$team->id] = 1;
                            }
                        }
                    }
                    if ($type != 'division') {
                        foreach ($timelineEntry->divisions as $division) {
                            if (array_key_exists($division->id, $entriesForDivision)) {
                                $entriesForDivision[$division->id]++;
                            } else {
                                $entriesForDivision[$division->id] = 1;
                            }
                        }
                    }
                    if ($type != 'season') {
                        foreach ($timelineEntry->seasons as $season) {
                            if (array_key_exists($season->id, $entriesForSeason)) {
                                $entriesForSeason[$season->id]++;
                            } else {
                                $entriesForSeason[$season->id] = 1;
                            }
                        }
                    }
                }
            }
            $this->timeline = $filteredTimeline;
        } else {
            $this->timeline = $timeline;
        }
    }

    public function defineProperties()
    {
        return [
            'type' => [
                'title' => 'Type',
                'description' => 'Entity type of which the timeline shall be shown',
                'type' => 'dropdown',
                'placeholder' => 'Select Entity',
                'required' => 'true',
                'default' => 'all',
                'options' => ['site' => 'Site', 'season' => 'Season', 'division' => 'Division', 'team' => 'Team', 'sloth' => 'Sloth']
            ],
            'subsequent' => [
                'title' => 'Subsequent',
                'description' => 'Show messages of entities that belong to the selected entity as well',
                'type' => 'checkbox',
                'default' => true
            ],
            'maxItems' => [
                'title' => 'Items',
                'description' => 'The number of timeline items to get.',
                'default' => 8,
                'type' => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'The timeline item property can contain only numeric symbols'
            ],
            'maxPerSubentity' => [
                'title' => 'Items',
                'description' => 'The maximum number of items per subentity to get. -1 to ignore limit',
                'default' => -1,
                'type' => 'string',
                'validationPattern' => '^[-]?[0-9]+$',
                'validationMessage' => 'The timeline item property can contain only numeric symbols'
            ],
            'id' => [
                'title' => 'Entity',
                'description' => 'The specific Entity to take data from - not needed for All',
                'default' => 'Site',
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
            case 'sloth':
                $myData = Sloth::all();
                break;
            case 'team':
                $myData = Team::all();
                break;
            case 'season':
                $myData = Season::all();
                break;
            case 'division':
                $myData = Division::all();
                break;
            /* CURRENTLY NOT IMPLEMENTED */
            /*case 'playoff':
                $myData = Playoffs::find($id);
                break;*/
            case 'site':
                $myData[0] = 'Site';
                return $myData;
        }
        $retOptions = [];
        foreach ($myData as $entity) {
            $retOptions[$entity->id] = $entity->title;
        }
        return $retOptions;
    }
}
