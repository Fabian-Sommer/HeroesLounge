<?php namespace Rikki\Heroeslounge\Models;

use Model;

/**
 * Model
 */
class SlothRole extends Model
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
    public $table = 'rikki_heroeslounge_sloth_roles';

    public $hasMany = ['sloths' => 'rikki_heroeslounge_sloths'];
}
