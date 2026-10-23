<?php namespace Rikki\Heroeslounge\Models;

 
use Rikki\Heroeslounge\Classes\BaseModel;
/**
 * Model
 */
class Region extends BaseModel
{
    use \Rikki\Heroeslounge\Classes\Traits\LegacyDateSerialization;

    public $hasMany = [
        'seasons' => ['Rikki\Heroeslounge\Models\Season'],
        'sloths' => ['Rikki\Heroeslounge\Models\Sloth'],
        'teams' => ['Rikki\Heroeslounge\Models\Team'],
    ];

    public $table = 'rikki_heroeslounge_regions';
    
}
