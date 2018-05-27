<?php
namespace Rikki\Heroeslounge\Models;

use October\Rain\Database\Model;

//use Rikki\Heroeslounge\Classes\BelongsToMorph;

/**
 * Class Timeline
 * @package App
 */
class Timeline extends Model
{
    public $table = 'rikki_heroeslounge_timeline';

    public $morphedByMany = [
        'sloths'  => ['Rikki\Heroeslounge\Models\Sloth',
                    'name' => 'timelineable',
                    'table' => 'rikki_heroeslounge_timelineables'],
        'teams' => ['Rikki\Heroeslounge\Models\Team',
                    'name' => 'timelineable',
                    'table' => 'rikki_heroeslounge_timelineables'],
        'divisions' => ['Rikki\Heroeslounge\Models\Division',
                    'name' => 'timelineable',
                    'table' => 'rikki_heroeslounge_timelineables'],
        'seasons' => ['Rikki\Heroeslounge\Models\Season',
                    'name' => 'timelineable',
                    'table' => 'rikki_heroeslounge_timelineables'],
        'matches' => ['Rikki\Heroeslounge\Models\Match',
                    'name' => 'timelineable',
                    'table' => 'rikki_heroeslounge_timelineables']
    ];

    public $belongsToMany = [
        'sloths_backend' => ['Rikki\Heroeslounge\Models\Sloth',
                            'name' => 'timelineable',
                            'table' => 'rikki_heroeslounge_timelineables',
                            'key' => 'timeline_id',
                            'otherKey' => 'timelineable_id'
                    ],
        'teams_backend' => ['Rikki\Heroeslounge\Models\Team',
                            'name' => 'timelineable',
                            'table' => 'rikki_heroeslounge_timelineables',
                            'key' => 'timeline_id',
                            'otherKey' => 'timelineable_id'
                    ],
    ];
}
