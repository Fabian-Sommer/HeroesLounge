<?php namespace Rikki\Heroeslounge\Models;

 

use October\Rain\Database\Model;
use Rikki\Heroeslounge\Models\Game;
use October\Rain\Support\Collection;
/**
 * Model
 */
class Talent extends Model
{

    public $table = 'rikki_heroeslounge_talents';

    public $belongsTo = [
        'hero' => ['Rikki\Heroeslounge\Models\Hero']
    ];

    public $belongsToMany = [
        'gameParticipations' => ['Rikki\Heroeslounge\Models\GameParticipation',
            'table' => 'rikki_heroeslounge_gameparticipation_talent',
            'key' => 'talent_id',
            'otherKey' => 'gameparticipation_id'
        ]
    ];
    
    public $hasMany = 
    [
        'bans' => ['Rikki\Heroeslounge\Models\Bans']        
    ];

}
