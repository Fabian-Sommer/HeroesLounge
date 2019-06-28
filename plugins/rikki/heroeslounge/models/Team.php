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
        'website_url' => ['url', 'regex:/^((?!porn).)*$/u']
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
            //'sloths_count' => ['Rikki\Heroeslounge\Models\Sloth', 'count' => true],
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
        'sloths' =>
        [
            'Rikki\Heroeslounge\Models\Sloth',
            'key' => 'team_id',
            'otherKey' => 'sloth_id',
            'table' => 'rikki_heroeslounge_sloth_team',
            'pivot' => ['is_captain'],
            'pivotModel' => 'Rikki\Heroeslounge\Models\SlothTeamPivot'
        ],
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

    //only for signups
    public function isEligibleForSeason($season)
    {
        foreach ($this->sloths as $key => $sloth) {
            if ($sloth->isSignedUpForSeason($season)) {
                return $sloth;
            }
        }
        return true;
    }

    //only for signups
    public function isEligibleForPlayoff($playoff)
    {
        foreach ($this->sloths as $key => $sloth) {
            if ($sloth->isSignedUpForPlayoff($playoff)) {
                return $sloth;
            }
        }
        return true;
    }

    public function getCaptainAttribute()
    {
        return $this->sloths->filter(function ($sloth) {
            return $sloth->pivot->is_captain;
        })->first();
    }
  
    public function getSlothratingAttribute()
    {       
        $slothsMmr = $this->sloths->map(function ($sloth) {
            return $sloth->mmr;
        })->toArray();

        $usedMmr = array_filter($slothsMmr, function ($v) {
            return ($v != 0);
        });

        return round((count($usedMmr) != 0) ? array_sum($usedMmr)/count($usedMmr) : 0);
    }

    public function getSlothratingMedianAttribute()
    {
        $slothsMmr = $this->sloths->map(function ($sloth) {
            return $sloth->mmr;
        })->toArray();

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
        return $slothsMmr = $this->sloths->sortByDesc('mmr')->first()->mmr;
    }

    public function getLowestMMRAttribute()
    {
        return $slothsMmr = $this->sloths->sortBy('mmr')->first()->mmr;
    }

    public function getTopFiveMMRAttribute()
    {
        $topFivePlayers = $this->sloths->reject(function ($sloth) {
            return $sloth->mmr == 0;
        })->sortByDesc('mmr')->chunk(5);
        if ($topFivePlayers->count() == 0) {
            return 0;
        }
        return $topFivePlayers[0]->reduce(function ($carry, $sloth) {
            return $carry + $sloth->mmr;
        }) / $topFivePlayers[0]->count();
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
