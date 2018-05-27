<?php namespace Rikki\Heroeslounge\Models;

 
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
        } else if ($this->playoff != null && $this->playoff->season != null) {
            return $this->playoff->season->title . ' - ' . $this->playoff->title;
        }
        return $this->title;
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
                $teams->where('id', $match->winner->id)->first()->score += 3;
            } elseif ($match->is_played) {
                //tie
                foreach($match->teams as $team) {
                    $teams->where('id', $team->id)->first()->score++;
                }
            }
            
            foreach($match->games as $game) {
                if ($game->winner) {
                    $teams->where('id', $game->winner->id)->first()->map_score += 1;
                    $teams->where('id', $match->teams->first(function ($team, $key) use ($game) {
                            return $team->id != $game->winner->id;
                        })->id)->first()->map_score -= 1;
                }
                
            }

            
        }
        return $teams->sortByDesc(function ($team) {
                    return 1000*$team->score + $team->map_score;
                });
    }
}
