<?php namespace Rikki\Heroeslounge\Models;

 
use Rikki\Heroeslounge\Classes\BaseModel;

use Indikator\content\models\Blog as Blog;
/**
 * Model
 */
class RssEntry extends BaseModel
{
    use \Rikki\Heroeslounge\Classes\Traits\LegacyDateSerialization;


    public $belongsTo = ['blog' => ['Indikator\content\models\Blog']
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'rikki_heroeslounge_rssentry';

}
