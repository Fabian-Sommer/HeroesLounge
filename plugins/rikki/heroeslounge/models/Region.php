<?php namespace Rikki\Heroeslounge\Models;

 
use October\Rain\Database\Model;
/**
 * Model
 */
class Region extends Model
{
    public $hasMany = [
        'seasons' => ['Rikki\Heroeslounge\Models\Season'],
        'sloths' => ['Rikki\Heroeslounge\Models\Sloth'],
        'teams' => ['Rikki\Heroeslounge\Models\Team'],
    ];

    public $table = 'rikki_heroeslounge_regions';
    
}
