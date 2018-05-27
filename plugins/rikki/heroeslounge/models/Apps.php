<?php namespace Rikki\Heroeslounge\Models;

use Model;

/**
 * Model
 */
class Apps extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /*
     * Validation
     */
    public $rules = [
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'rikki_heroeslounge_team_apps';

    public $belongsTo = [
        'sloth' => [
            'Rikki\Heroeslounge\Models\Sloth',
            'key'=>'user_id',
            'otherKey'=>'user_id'
            ],
        'team' => [
            'Rikki\Heroeslounge\Models\Team',
            'key' => 'team_id'
            ]
    ];
}
