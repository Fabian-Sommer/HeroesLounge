<?php namespace Rikki\Heroeslounge\Models;

 

use October\Rain\Database\Model;

/**
 * Model
 */
class GameParticipation extends Model
{
    use \October\Rain\Database\Traits\Validation;


    public $table = 'rikki_heroeslounge_gameparticipation';

    public $rules = [
        'title' => 'required'
    ];

    public $belongsTo = [
        'game' => [
            'Rikki\Heroeslounge\Models\Game',
            'key' => 'game_id'
        ],
        'sloth' => [
            'Rikki\Heroeslounge\Models\Sloth',
            'key' => 'sloth_id',
            'otherKey' => 'id'
        ],
        'hero' => [
            'Rikki\Heroeslounge\Models\Hero',
            'key' => 'hero_id',
            'otherKey' => 'id'
        ],
        'team' => [
            'Rikki\Heroeslounge\Models\Team',
            'key' => 'team_id',
            'otherKey' => 'id'
        ]
    ];

    public $belongsToMany = [
    	'talents' => ['Rikki\Heroeslounge\Models\Talent',
            'table' => 'rikki_heroeslounge_gameparticipation_talent',
            'key' => 'gameparticipation_id',
            'otherKey' => 'talent_id'
        ]
    ];

    public function scopeByTeam($q)
    {
        return $q->orderBy('team_id', 'asc');
    }

    public function getOrderedTalentsAttribute($value)
    {
        return $this->talents()->orderBy('talent_tier', 'asc')->get();
    }
}
