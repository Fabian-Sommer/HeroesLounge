<?php
namespace Rikki\Heroeslounge\Models;

use Rikki\Heroeslounge\Classes\BaseModel;


/**
 * Class Timeline
 * @package App
 */
class Timeline extends BaseModel
{
    use \Rikki\Heroeslounge\Classes\Traits\LegacyDateSerialization;

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
        'matches' => ['Rikki\Heroeslounge\Models\MatchModel',
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
