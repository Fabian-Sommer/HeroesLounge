<?php namespace Rikki\Heroeslounge\Models;

use October\Rain\Database\Model;
use Illuminate\Support\Facades\DB;
use Rikki\Heroeslounge\classes\ReplayParsing\ReplayParsing;
use October\Rain\Support\Collection;
use Log;

/**
 * Model
 */
class Game extends Model
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'rikki_heroeslounge_games';

    public $attachOne = [
        'replay' => ['System\Models\File'],
        'draft' => ['System\Models\File'],
    ];

    public $hasMany = [
        'gameParticipations' =>
        [
            'Rikki\Heroeslounge\Models\GameParticipation',
            'scope' => 'byTeam'
        ]
    ];

    public $belongsTo = [
        'match' => [
            'Rikki\Heroeslounge\Models\Match',
            'key' => 'match_id',
            'otherKey' => 'id'
        ],
        'winner' => [
            'Rikki\HeroesLounge\Models\Team',
            'key' => 'winner_id',
            'otherKey' => 'id'
        ],
        'map' => [
            'Rikki\HeroesLounge\Models\Map',
            'key' => 'map_id',
            'otherKey' => 'id'
        ],
        'teamOneFirstBan' => [
            'Rikki\Heroeslounge\Models\Hero',
            'key' => 'team_one_ban_one_id',
            'otherKey' => 'id'
        ],
        'teamOneSecondBan' => [
            'Rikki\Heroeslounge\Models\Hero',
            'key' => 'team_one_ban_two_id',
            'otherKey' => 'id'
        ],
        'teamTwoFirstBan' => [
            'Rikki\Heroeslounge\Models\Hero',
            'key' => 'team_two_ban_one_id',
            'otherKey' => 'id'
        ],
        'teamTwoSecondBan' => [
            'Rikki\Heroeslounge\Models\Hero',
            'key' => 'team_two_ban_two_id',
            'otherKey' => 'id'
        ],
        'teamOneThirdBan' => [
            'Rikki\Heroeslounge\Models\Hero',
            'key' => 'team_one_ban_three_id',
            'otherKey' => 'id'
        ],
        'teamTwoThirdBan' => [
            'Rikki\Heroeslounge\Models\Hero',
            'key' => 'team_two_ban_three_id',
            'otherKey' => 'id'
        ],
        'teamOne' => [
            'Rikki\Heroeslounge\Models\Team',
            'key' => 'team_one_id',
            'otherKey' => 'id'
        ],
        'teamTwo' => [
            'Rikki\Heroeslounge\Models\Team',
            'key' => 'team_two_id',
            'otherKey' => 'id'
        ]
    ];

    public function getLoserAttribute()
    {
        if (!$this->winner) {
            return null;
        }
        if ($this->teamTwo != null && $this->winner_id == $this->team_one_id) {
            return $this->teamTwo;
        }
        if ($this->teamOne != null && $this->winner_id == $this->team_two_id) {
            return $this->teamOne;
        }
        if ($this->match != null) {
            $winner_id = $this->winner_id;
            return $this->match->teams->filter(function ($team) use ($winner_id) {
                return $team->id != $winner_id;
            })->first();
        }
        return null;
    }

    public function beforeUpdate()
    {
        if ($this->isDirty('winner_id') && isset($this->getOriginal()['winner_id'])) {
            DB::table('rikki_heroeslounge_team_match')
                ->where('team_id', $this->getOriginal()['winner_id'])
                ->where('match_id', $this->match_id)
                ->decrement('team_score');
        }
    }

    public function afterSave()
    {
        if ($this->isDirty('winner_id')) {
            $this->updateTeamScore();
        }
    }

    public function afterDelete()
    {
        DB::table('rikki_heroeslounge_team_match')->where('team_id', $this->winner->id)->where('match_id', $this->match_id)->where('team_score', '>', 0)->decrement('team_score');
    }



    public function updateTeamScore()
    {
        DB::table('rikki_heroeslounge_team_match')
            ->where('team_id', $this->winner->id)
            ->where('match_id', $this->match_id)
            ->increment('team_score');
    }

    public function getTeamOneParticipants()
    {
        return $this->gameParticipations->where('team_id', $this->team_one_id)->sortBy('draft_order');
    }

    public function getTeamTwoParticipants()
    {
        return $this->gameParticipations->where('team_id', $this->team_two_id)->sortBy('draft_order');
    }

    public function getFirstPickTeam()
    {
        $participation = $this->gameParticipations->where('draft_order', 1)->first();
        return ($participation ? ($participation->team ? $participation->team->title : '') : '');
    }

    public function getSecondPickTeam()
    {
        $participation = $this->gameParticipations->where('draft_order', 2)->first();
        return ($participation ? ($participation->team ? $participation->team->title : '') : '');
    }

    public function getTeamsGrouped()
    {
        $retVal = [];
        $retVal[] = $this->getTeamOneParticipants();
        $retVal[] = $this->getTeamTwoParticipants();
        return $retVal;
    }

    public function getTeamOneKills()
    {
        return $this->getTeamOneParticipants()->sum('kills');
    }

    public function getTeamTwoKills()
    {
        return $this->getTeamTwoParticipants()->sum('kills');
    }

    public function getTeamOneBans()
    {
        $c = [];
    	if ($this->teamOneFirstBan) {
    		array_push($c, $this->teamOneFirstBan);
    	}
        if ($this->teamOneSecondBan) {
    		array_push($c, $this->teamOneSecondBan);
    	}
        if ($this->teamOneThirdBan) {
            array_push($c, $this->teamOneThirdBan);
        }
    	return new Collection($c);
    }

    public function getTeamTwoBans()
    {
    	$c = [];
        if ($this->teamTwoFirstBan) {
            array_push($c, $this->teamTwoFirstBan);
        }
        if ($this->teamTwoSecondBan) {
            array_push($c, $this->teamTwoSecondBan);
        }
        if ($this->teamTwoThirdBan) {
            array_push($c, $this->teamTwoThirdBan);
        }
        return new Collection($c);
    }

    //parses the associated replay, erasing previous results
    public function parseReplay()
    {
        ReplayParsing::parseReplayAndSave($this);
    }
}
