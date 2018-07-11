<?php namespace Rikki\Heroeslounge\Models;

 
use Model;
use Rikki\Heroeslounge\Models\Apps as Applications;

/**
 * Model
 */
class Season extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    use \October\Rain\Database\Traits\SoftDelete;

    use \October\Rain\Database\Traits\Sluggable;


    protected $dates = ['deleted_at'];

    /*
     * Validation
     */
    public $rules = [
    ];
    protected $slugs = ['slug' => 'title'];

    public $belongsTo = ['region' => ['Rikki\Heroeslounge\Models\Region']
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'rikki_heroeslounge_seasons';

    public $attachOne = ['logo' => 'System\Models\File'];

    public $hasMany = [
        'divisions' => ['Rikki\Heroeslounge\Models\Division'],
        'divisions_count' => [
            'Rikki\Heroeslounge\Models\Division',
            'count' => true],
        'playoffs' => ['Rikki\Heroeslounge\Models\Playoff']
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
            'key' => 'season_id',
            'otherKey' => 'team_id',
            'table' => 'rikki_heroeslounge_season_team'
        ],
        'teams_count' =>
        [
            'Rikki\Heroeslounge\Models\Team',
            'key' => 'season_id',
            'otherKey' => 'team_id',
            'table' => 'rikki_heroeslounge_season_team',
            'count' => true
        ],
        'free_agents' =>
        [
            'Rikki\Heroeslounge\Models\Sloth',
            'key' => 'season_id',
            'otherKey' => 'sloth_id',
            'table' => 'rikki_heroeslounge_season_freeagent'
        ],
        'free_agents_count' =>
        [
            'Rikki\Heroeslounge\Models\Sloth',
            'key' => 'season_id',
            'otherKey' => 'sloth_id',
            'table' => 'rikki_heroeslounge_season_freeagent',
            'count' => true
        ]
    ];

    public $hasManyThrough = [
        'matches' => [
            'Rikki\Heroeslounge\Models\Match',
            'key' => 'season_id',
            'through' => 'Rikki\Heroeslounge\Models\Division',
            'throughKey' => 'div_id'
        ]
    ];

    public function beforeUpdate()
    {
        if ($this->isDirty('reg_open')) {
            if ($this->reg_open == false) {
                $this->teams->each(function ($team) {
                    $team->accepting_apps = false;
                    $team->save();
                    $appsToTeam = Applications::where("team_id", $team->id)->get();
                    $appsToTeam->each(function ($model) {
                        $model->withdrawn = 1;
                        $model->save();
                    });
                });
            }
        }
    }
}
