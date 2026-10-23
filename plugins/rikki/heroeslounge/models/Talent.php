<?php namespace Rikki\Heroeslounge\Models;

 

use Rikki\Heroeslounge\Classes\BaseModel;
use Rikki\Heroeslounge\Models\Game;
use October\Rain\Support\Collection;
/**
 * Model
 */
class Talent extends BaseModel
{
    use \Rikki\Heroeslounge\Classes\Traits\LegacyDateSerialization;


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
