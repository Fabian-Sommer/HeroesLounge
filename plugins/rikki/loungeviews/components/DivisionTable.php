<?php namespace Rikki\LoungeViews\Components;

 
use Cms\Classes\ComponentBase;
use Rikki\Heroeslounge\Models\Division as Divisions;
use Rikki\Heroeslounge\Models\Team as Teams;

use Log;
use Request;

class DivisionTable extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'DivisionTable',
            'description' => 'Allows users to view Division Table Results'
        ];
    }

    public $teams = null;
    public $selTeam = null;
    public $startIndex = 0;
    public $length =  null;
    public $showScore = false;

    public function onRender()
    {
        $id = $this->property('id');
        $div = Divisions::find($id);
        $this->showScore = $this->property('showScore');
        if ($div) {
            $this->teams = $div->teams()->where('active', 1)->withPivot('win_count')->withPivot('match_count')->withPivot('bye')->withPivot('free_win_count')->
                                    whereNull('rikki_heroeslounge_teams.deleted_at')->
                                    orderBy('win_count', "DESC")->orderBy('free_win_count', "ASC")->orderBy('bye', "ASC")->orderBy('match_count', "DESC")->get();

            if ($this->property('showScore')) {
                //used for playoffs
                //we want teams that since then disbanded as well
                $this->teams = $div->getTeamsSortedByScore();
            } else {
                //calculate game wins
                foreach ($this->teams as $team) {
                    $team->game_wins = 0;
                }
                foreach ($div->matches as $match) {
                    foreach ($match->games as $game) {
                        $team = $this->teams->where('id', $game->winner_id)->first();
                        if ($game->winner_id != 0 && $game->winner_id != null && $team != null) {
                            $this->teams->where('id', $game->winner_id)->first()->game_wins++;
                        }
                    }
                }
                $this->teams = $this->teams->sortByDesc(function ($team) {
                    return 1000000*$team->pivot->win_count + 1000*$team->game_wins + $team->pivot->match_count - 0.001 * $team->pivot->free_win_count - 0.001 * $team->pivot->bye;
                });
            }

            if(null !== $this->property('teamId')) {
                $this->selTeam = $this->property('teamId');
                $zeroBasedPosition = array_search($this->selTeam,array_column($this->teams->toArray(),'id'));
             
                $this->startIndex = max($zeroBasedPosition  - $this->property('surroundingEntries')/2,0);
                $this->length = 1 + $this->property('surroundingEntries');


                $negativeAdjustment = $this->length + $this->startIndex - count($this->teams);
                if($negativeAdjustment > 0) {
                    $this->startIndex -= $negativeAdjustment;
                }
            }
        }
    }


    public function defineProperties()
    {
        return[
            'id' => [
                'title' => 'Entity',
                'description' => 'The specific Entity to take data from',
                'required' => true,
                'type' => 'dropdown',
                'placeholder' => 'Select specific Entity'
            ],
            'surroundingEntries' => [
                'title' => 'Surrounding Entries',
                'description' => 'If specified, how many teams (+/-) shall be shown around the given team',
                'type' => 'string',
                'default' => 4,
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'The Max Entries property can contain only numeric symbols'
            ],
            'teamId'=> [
                'title' => 'Entity',
                'description' => 'The team id to show max entries for',
                'type' => 'dropdown',
                'placeholder' => 'Select specific team'
            ],
            'showScore' => [
                'title' => 'Show Score',
                'description' => 'Showing score instead of # wins',
                'type' => 'checkbox',
                'default' => false
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

    public function getTeamIdOptions()
    {
        $myData = Teams::where('disbanded',false)->get();
        $retOptions = [];
        foreach ($myData as $entity) {
            $retOptions[$entity->id] = $entity->title;
        }
        return $retOptions;
    }
}
