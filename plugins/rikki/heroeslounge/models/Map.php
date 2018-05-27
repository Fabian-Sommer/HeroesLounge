<?php namespace Rikki\Heroeslounge\Models;

use Model;

/**
 * Model
 */
class Map extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    use \October\Rain\Database\Traits\SoftDelete;

    protected $dates = ['deleted_at'];

    /*
     * Validation
     */
    public $rules = [
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'rikki_heroeslounge_maps';

    public function afterCreate()
    {
        Rikki\Heroeslounge\classes\Heroes\HeroUpdater::addTranslationsToMaps();
    }
}
