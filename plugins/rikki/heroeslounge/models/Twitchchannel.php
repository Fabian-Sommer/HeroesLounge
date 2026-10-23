<?php namespace Rikki\Heroeslounge\Models;

 
use Rikki\Heroeslounge\Classes\BaseModel;

/**
 * Model
 */
class Twitchchannel extends BaseModel
{
    use \Rikki\Heroeslounge\Classes\Traits\LegacyDateSerialization;

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
    public $table = 'rikki_heroeslounge_twitchchannel';

    public $belongsToMany = [
        'matches' => [
            'Rikki\Heroeslounge\Models\MatchModel',
            'key' => 'channel_id',
            'otherKey' => 'match_id',
            'table' => 'rikki_heroeslounge_match_channel'
        ]
    ];
}
