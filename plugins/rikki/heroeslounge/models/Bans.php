<?php namespace Rikki\Heroeslounge\Models;

use Model;

/**
 * Model
 */
class Bans extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;

    /*
     * Validation
     */
    public $rules = [
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'rikki_heroeslounge_bans';


    public $belongsTo = [
        
            'hero' => ['Rikki\Heroeslounge\Models\Hero',
            'key' => 'hero_id'
        ],
        
            'talent' => ['Rikki\Heroeslounge\Models\Talent',
            'key' => 'talent_id'
        ],
        
            'season' => ['Rikki\Heroeslounge\Models\Season',
            'key' => 'season_id'
        ]

    ];
}