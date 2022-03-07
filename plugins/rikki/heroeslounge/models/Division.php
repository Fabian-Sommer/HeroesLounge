<?php namespace Rikki\Heroeslounge\Models;

use Rikki\LoungeStatistics\classes\statistics\Statistics as Stats;
 
use October\Rain\Support\Collection;
use Model;
use Log;
/**
 * Model
 */
class Division extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    use \October\Rain\Database\Traits\SoftDelete;



    protected $dates = ['deleted_at'];

    /*
     * Validation
     */
    public $rules = [
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'rikki_heroeslounge_divisions';

    public $attachOne = ['logo' => 'System\Models\File'];

    public $belongsTo = [
        'season' => [
            'Rikki\Heroeslounge\Models\Season',
            'key' => 'season_id'
        ],
        'playoff' => [
            'Rikki\Heroeslounge\Models\Playoff',
            'key' => 'playoff_id'
        ]
    ];

    public $hasMany = [
        'matches' =>  [
            'Rikki\Heroeslounge\Models\Match',
            'key' => 'div_id',
            'otherKey' => 'id'
        ]
    ];

    public $morphToMany = [
        'timeline' => ['Rikki\Heroeslounge\Models\Timeline',
                    'name' => 'timelineable',
                    'table' => 'rikki_heroeslounge_timelineables']
    ];

    public $belongsToMany = [
        'teams' =>
        [
            'Rikki\Heroeslounge\Models\Team',
            'key' => 'div_id',
            'otherKey' => 'team_id',
            'table' => 'rikki_heroeslounge_team_division',
            'pivot' => [
                'win_count',
                'match_count',
                'bye',
                'deleted_at',
                'created_at',
                'updated_at',
                'active'
            ]
        ],
         'teams_count' =>
        [
            'Rikki\Heroeslounge\Models\Team',
            'key' => 'div_id',
            'otherKey' => 'team_id',
            'table' => 'rikki_heroeslounge_team_division',
            'count' => true
            ]
    ];

    public function getLongTitleAttribute() 
    {
        if ($this->season != null) {
            return $this->season->title . ' - ' . $this->title;
        } else if ($this->playoff != null) {
            return $this->playoff->longTitle . ' - ' . $this->title;
        }
        return $this->title;
    }

    public static function listDivisionsWithLongTitle()
    {
        return Division::all()->keyBy('id')->map(function ($division) { return $division->longTitle; })->toArray();
    }

    public function getActiveTeamsAttribute()
    {
        return $this->teams->where('pivot.active', 1);
    }

    public function scopeActive($q)
    {
        $q->join('rikki_heroeslounge_seasons as season','rikki_heroeslounge_divisions.season_id','=','season.id')->where('season.is_active',true);
    }

    //as in playoffs
    public function getTeamsSortedByScore()
    {
        $teams = $this->teams()->withPivot('win_count')->withPivot('match_count')->withPivot('bye')->withPivot('free_win_count')->
                                whereNull('rikki_heroeslounge_teams.deleted_at')->get();
        foreach ($teams as $team) {
            $team->score = 0;
            $team->map_score = 0;
        }
        foreach ($this->matches as $match) {
            if ($match->winner) {
                $winningTeam = $teams->where('id', $match->winner->id)->first();
                if ($winningTeam) {
                    $winningTeam->score += 3;
                }
            } elseif ($match->is_played) {
                //tie
                foreach($match->teams as $team) {
                    $t = $teams->where('id', $team->id)->first();
                    if ($t) {
                        $t->score += 1;
                    }
                }
            }

            foreach ($match->games as $game) {
                if ($game->winner) {
                    $winningTeam = $teams->where('id', $game->winner->id)->first();
                    if ($winningTeam) {
                        $winningTeam->map_score += 1;    
                    }
                }
                if ($game->loser) {
                    $losingTeam = $teams->where('id', $game->loser->id)->first();
                    if ($losingTeam) {
                        $losingTeam->map_score -= 1;
                    }
                }
            }

            
        }
        return $teams->sortByDesc(function ($team) {
                    return 1000*$team->score + $team->map_score;
                });
    }

    public function getDivisionTableStandings()
    {
        $teams = $this->teams()->withPivot('win_count')->withPivot('match_count')->withPivot('bye')->withPivot('free_win_count')->
                        whereNull('rikki_heroeslounge_teams.deleted_at')->get();
        
        //calculate game wins
        foreach ($teams as $team) {
            $team->game_wins = 0;
        }

        foreach ($this->matches as $match) {
            foreach ($match->games as $game) {
                if ($game->winner) {
                    $winner = $teams->where('id', $game->winner->id)->first();
                    if ($winner) {
                        $winner->game_wins++;
                    }
                }
            }
        }

        $sortedTeams = $teams->sortByDesc(function ($team) {
            return 1000000*$team->pivot->win_count + 1000*$team->game_wins + $team->pivot->match_count - 0.001 * $team->pivot->free_win_count - 0.001 * $team->pivot->bye;
        })->values()->all();

        $tempTeams = new Collection($sortedTeams);

        return $tempTeams->map(function ($team, $key) {
            $team["pivot"]["position"] = $key + 1;;
            
            return new Collection($team);
        });
    }

    public function herostatistics()
    {
        $stats = Stats::calculateHeroStatistics("division", null, $this->matches, null);
        return $stats->sortByDesc(function ($hero_array) {
            return $hero_array['picks'] * 1000000 + $hero_array['bans'];
        });
    }
}
