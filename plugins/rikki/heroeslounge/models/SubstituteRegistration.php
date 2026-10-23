<?php namespace Rikki\Heroeslounge\Models;

use Illuminate\Support\Facades\DB;
use Rikki\Heroeslounge\Classes\BaseModel;
use Rikki\Heroeslounge\Models\Sloth;
use Rikki\Heroeslounge\Models\Season;
use Rikki\Heroeslounge\Models\MatchModel;
use Rikki\Heroeslounge\Models\Team;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Log;

class SubstituteRegistration extends BaseModel
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
    public $table = 'rikki_heroeslounge_substitutes';

    public $belongsTo = [
        'subSloth' => [
            'Rikki\Heroeslounge\Models\Sloth',
            'key' => 'sub_id'
        ],
        'registrantSloth' => [
            'Rikki\Heroeslounge\Models\Sloth',
            'key' => 'registrant_id'
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

    public function approve() {
        $this->approved = true;
        $this->save();
    }
}