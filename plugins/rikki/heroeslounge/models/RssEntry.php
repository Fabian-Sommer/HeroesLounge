<?php namespace Rikki\Heroeslounge\Models;

 
use Model;

use Indikator\content\models\Blog as Blog;
/**
 * Model
 */
class RssEntry extends Model
{

    public $belongsTo = ['blog' => ['Indikator\content\models\Blog']
    ];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'rikki_heroeslounge_rssentry';

}
