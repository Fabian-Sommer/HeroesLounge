<?php
namespace Rikki\Heroeslounge\Models;

 
use October\Rain\Database\Model;
use Rikki\Heroeslounge\Models\Timeline;
use Rikki\Heroeslounge\Models\Sloth as SlothModel;

/**
 * Model
 */
class Team extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    use \October\Rain\Database\Traits\SoftDelete;

    use \October\Rain\Database\Traits\Sluggable;

    protected $dates = ['deleted_at'];


    /*
     * Validation
     */
    public $rules = [
        'title' => 'unique:rikki_heroeslounge_teams,title|required|between:4,127',
        'slug' => 'unique:rikki_heroeslounge_teams,slug|required|between:2,50',
        'twitch_url' => ['url', 'regex:/^http[s]?:\/\/(www\.)?twitch\.tv\/[a-zA-Z0-9][\w]{2,24}(\/)?$/u'],
        'facebook_url' => ['url', 'regex:/^http[s]?:\/\/(www\.)?facebook\.com\/[A-Za-z0-9\.]{3,}(\/)?$/u'],
        'twitter_url' => ['url', 'regex:/^http[s]?:\/\/(www\.)?twitter\.com\/([a-zA-Z0-9_]+)(\/)?$/u'],
        'youtube_url' => ['url', 'regex:/^http[s]?:\/\/(www\.)?youtube\.com\/(channel|user|c)\/([a-zA-Z0-9_\-]+)(\/)?$/u'],
        'website_url' => ['url', 'regex:/^((?!porn).)*$/u'],
        'type' => 'required|regex:/[1-2]/'
    ];

    protected $slugs = ['slug' => 'title'];
    protected $revisionable = ['title'];

    /**
     * @var string The database table used by the model.
     */
    public $table = 'rikki_heroeslounge_teams';

    public $attachOne = [
        'logo' => 'System\Models\File',
        'smallLogo' => 'System\Models\File',
        'banner' => 'System\Models\File'
    ];

    public $hasMany = [
            'amateur_sloths' => ['Rikki\Heroeslounge\Models\Sloth', 'key' => 'team_id', 'order' => 'is_captain desc'],
            'divs_sloths' => ['Rikki\Heroeslounge\Models\Sloth', 'key' => 'divs_team_id', 'order' => 'is_divs_captain desc'],
            'sloths_count' => ['Rikki\Heroeslounge\Models\Sloth', 'count' => true],
            'apps' => ['Rikki\Heroeslounge\Models\Apps'],
            'gameParticipants' => ['Rikki\Heroeslounge\Models\GameParticipation']
            ];
    

    public $morphMany = [
        'revision_history' => ['System\Models\Revision','title' => 'revisionable']
    ];

    public $morphToMany = [
        'timeline' => ['Rikki\Heroeslounge\Models\Timeline',
                        'name' => 'timelineable',
                        'table' => 'rikki_heroeslounge_timelineables']
    ];

    public $belongsTo = [
        'region' => ['Rikki\Heroeslounge\Models\Region'],
    ];

    public $belongsToMany = [
        'divisions' =>
        [
            'Rikki\Heroeslounge\Models\Division',
            'key' => 'team_id',
            'otherKey' => 'div_id',
            'table' => 'rikki_heroeslounge_team_division',
            'pivot' => [
                'win_count',
                'match_count',
                'bye',
                'deleted_at',
                'created_at',
                'updated_at',
                'active'
            ]
        ],
        'active_divisions' =>
        [
            'Rikki\Heroeslounge\Models\Division',
            'key' => 'team_id',
            'otherKey' => 'div_id',
            'table' => 'rikki_heroeslounge_team_division',
            'pivot' => [
                'win_count',
                'match_count',
                'bye',
                'deleted_at',
                'created_at',
                'updated_at',
                'active'
            ],
            'scope' => 'active'
        ],
         'divisions_count' =>
        [
            'Rikki\Heroeslounge\Models\Division',
            'table' => 'rikki_heroeslounge_team_division',
            'key' => 'team_id',
            'otherKey' => 'div_id',
            'table' => 'rikki_heroeslounge_team_division','count' => true
            ],
        'matches' =>
        [
            'Rikki\Heroeslounge\Models\Match',
            'key' => 'team_id',
            'otherKey' => 'match_id',
            'table' => 'rikki_heroeslounge_team_match',
            'pivot' => ['team_score']
        ],
        'seasons' =>
        [
            'Rikki\Heroeslounge\Models\Season',
            'key' => 'team_id',
            'otherKey' => 'season_id',
            'table' => 'rikki_heroeslounge_season_team'
        ],
        'playoffs' =>
        [
            'Rikki\Heroeslounge\Models\Playoff',
            'key' => 'team_id',
            'otherKey' => 'playoff_id',
            'table' => 'rikki_heroeslounge_team_playoff',
            'pivot' => ['seed'] 
        ],
    ];


    public function scopeWithMatches($query)
    {
        $query->with('matches', 'matches.games', 'matches.games.map', 'matches.games.gameParticipations', 'matches.games.gameParticipations.hero', 'matches.games.teamOneFirstBan', 'matches.games.teamOneSecondBan', 'matches.games.teamTwoFirstBan', 'matches.games.teamTwoSecondBan');
    }

    public function getSlothsAttribute()
    {
        if ($this->type == 1) {
            return $this->amateur_sloths;
        } else {
            return $this->divs_sloths;
        }
    }

    public function getCaptainAttribute()
    {
        if ($this->type == 1) {
            return $this->sloths->where('is_captain', true)->first();
        } else {
            return $this->sloths->where('is_divs_captain', true)->first();
        }
    }
  
    public function getSlothratingAttribute()
    {
        $slothsMmr = '';
        if ($this->type == 1) {
            $slothsMmr = SlothModel::where('team_id', $this->id)->lists('mmr');
        } else {
            $slothsMmr = SlothModel::where('divs_team_id', $this->id)->lists('mmr');
        }
        
        $usedMmr = array_filter($slothsMmr, function ($v) {
            return ($v != 0);
        });

        return round((count($usedMmr) != 0) ? array_sum($usedMmr)/count($usedMmr) : 0);
    }

    public function getSlothratingMedianAttribute()
    {
        $slothsMmr = '';
        if ($this->type == 1) {
            $slothsMmr = SlothModel::where('team_id', $this->id)->lists('mmr');
        } else {
            $slothsMmr = SlothModel::where('divs_team_id', $this->id)->lists('mmr');
        }

        $usedMmr = array_filter($slothsMmr, function ($v) {
            return ($v != 0);
        });

        sort($usedMmr, SORT_NUMERIC);

        $count = count($usedMmr);

        if ($count % 2 == 0) {
            return $usedMmr[$count / 2];
        } else {
            return (($usedMmr[($count / 2)] + $usedMmr[($count / 2) - 1]) / 2);
        }
    }

    public function getHighestMMRAttribute()
    {
        $slothsMmr = '';
        if ($this->type == 1) {
            $slothsMmr = SlothModel::where('team_id', $this->id)->orderBy('mmr', 'desc')->first();
        } else {
            $slothsMmr = SlothModel::where('divs_team_id', $this->id)->orderBy('mmr', 'desc')->first();
        }

        return $slothMmr->mmr;
    }

    public function getLowestMMRAttribute()
    {
        $slothsMmr = '';
        if ($this->type == 1) {
            $slothsMmr = SlothModel::where('team_id', $this->id)->where('mmr', '<>', 0)->orderBy('mmr', 'asc')->first();
        } else {
            $slothsMmr = SlothModel::where('divs_team_id', $this->id)->where('mmr', '<>', 0)->orderBy('mmr', 'asc')->first();
        }

        return $slothMmr->mmr;
    }

    public function getNumOfPlayersAttribute()
    {
        return $this->sloths->count();
    }

    public function getGamesAttribute()
    {
        return $this->matches->map(function ($item) {
            return $item->games;
        })->flatten();
    }

    public function getMostRecentSeasonAttribute()
    {
        return $this->seasons->sortByDesc('created_at')->first();
    }

    public function beforeUpdate()
    {
        if ($this->isDirty('disbanded')) {
            if ($this->disbanded) {
                $this->_saveTimelineEntry('Team.InActive');
            } 
        }
    }


    public function beforeDelete()
    {
        $this->_saveTimelineEntry('Team.Deleted');
    }

    private function _saveTimelineEntry($timelineEntryType)
    {
        $timeline = new Timeline();
        $timeline->type = $timelineEntryType;
        $timeline->save();
        $timeline->teams()->add($this);
    }
}
