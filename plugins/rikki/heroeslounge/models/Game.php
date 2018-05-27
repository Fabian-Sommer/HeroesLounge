<?php namespace Rikki\Heroeslounge\Models;

use \October\Rain\Database\Model;
use Illuminate\Support\Facades\DB;
use Rikki\Heroeslounge\classes\ReplayParsing\ReplayParsing;
use October\Rain\Support\Collection;

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
    	if ($this->teamOneFirstBan && $this->teamOneSecondBan) {
    		return new Collection([$this->teamOneFirstBan, $this->teamOneSecondBan]);
    	} else if ($this->teamOneFirstBan) {
    		return new Collection([$this->teamOneFirstBan]);
    	} else if ($this->teamOneSecondBan) {
    		return new Collection([$this->teamOneSecondBan]);
    	}
    	return new Collection;
    }

    public function getTeamTwoBans()
    {
    	if ($this->teamTwoFirstBan && $this->teamTwoSecondBan) {
    		return new Collection([$this->teamTwoFirstBan, $this->teamTwoSecondBan]);
    	} else if ($this->teamTwoFirstBan) {
    		return new Collection([$this->teamTwoFirstBan]);
    	} else if ($this->teamTwoSecondBan) {
    		return new Collection([$this->teamTwoSecondBan]);
    	}
    	return new Collection;
    }

    //parses the associated replay, erasing previous results
    public function parseReplay()
    {
        ReplayParsing::parseReplayAndSave($this);
    }
}
