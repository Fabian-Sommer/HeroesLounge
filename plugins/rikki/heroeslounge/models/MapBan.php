<?php namespace Rikki\Heroeslounge\Models;

use Illuminate\Support\Facades\DB;
use Rikki\Heroeslounge\Classes\BaseModel;
use Rikki\Heroeslounge\Models\Map;
use Rikki\Heroeslounge\Models\MatchModel;
use Rikki\Heroeslounge\Models\Team;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Log;

class MapBan extends BaseModel
{
    use \Rikki\Heroeslounge\Classes\Traits\LegacyDateSerialization;

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
    public $table = 'rikki_heroeslounge_mapbans';

    public $belongsTo = [
        'map' => [
            'Rikki\Heroeslounge\Models\Map',
            'key' => 'map_id'
        ],
        'team' => [
            'Rikki\Heroeslounge\Models\Team',
            'key' => 'team_id',
            'otherKey' => 'id'
        ],
        'match' => [
            'Rikki\Heroeslounge\Models\MatchModel',
            'key' => 'match_id',
            'otherKey' => 'id'
        ]
    ];

}