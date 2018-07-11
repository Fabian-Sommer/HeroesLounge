<?php namespace Rikki\Heroeslounge\Models;

 

use October\Rain\Database\Model;
use Rikki\Heroeslounge\Models\Game;
use October\Rain\Support\Collection;
/**
 * Model
 */
class Hero extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    use \October\Rain\Database\Traits\SoftDelete;


    public $table = 'rikki_heroeslounge_heroes';

    public $rules = [
        'title' => 'required'
    ];

    public $hasMany = [
        'gameParticipations' => ['Rikki\Heroeslounge\Models\GameParticipation'],
        'talents' => ['Rikki\Heroeslounge\Models\Talent'],
        'part_count' => ['Rikki\Heroeslounge\Models\GameParticipation', 'count' => true],
        'bans' => ['Rikki\Heroeslounge\Models\Bans']
    ];
 
    public function getBansAttribute($value)
    {
        return Game::where('team_one_ban_one_id', $this->id)
                ->orWhere('team_two_ban_one_id', $this->id)
                ->orWhere('team_one_ban_two_id', $this->id)
                ->orWhere('team_two_ban_two_id', $this->id)
                ->get();
    }

    public function getTeamStatistics($team)
    {
        return new Collection([$this->getTeamPicks($team), $this->getTeamBans($team), $this->getTeamVsBans($team), $this->getTeamWinrate($team)]);
    }

    public function getTeamPicks($team)
    {
        return $this->gameParticipations->where('team_id', $team->id)->count();
    }

    public function getTeamBans($team)
    {
        return $team->games->where('team_one_ban_one_id', $this->id)
                ->where('team_one_id', $team->id)
                ->count()
                +
                $team->games->where('team_one_ban_two_id', $this->id)
                ->where('team_one_id', $team->id)
                ->count()
                +
                $team->games->where('team_two_ban_one_id', $this->id)
                ->where('team_two_id', $team->id)
                ->count()
                +
                $team->games->where('team_two_ban_two_id', $this->id)
                ->where('team_two_id', $team->id)
                ->count()
                +
                $team->games->where('team_one_ban_three_id', $this->id)
                ->where('team_one_id', $team->id)
                ->count()
                +
                $team->games->where('team_two_ban_three_id', $this->id)
                ->where('team_two_id', $team->id)
                ->count();
    }

    public function getTeamVsBans($team)
    {
        return $team->games->where('team_one_ban_one_id', $this->id)
                ->where('team_one_id', $team->id)
                ->count()
                +
                $team->games->where('team_one_ban_two_id', $this->id)
                ->where('team_one_id', $team->id)
                ->count()
                +
                $team->games->where('team_two_ban_one_id', $this->id)
                ->where('team_two_id', $team->id)
                ->count()
                +
                $team->games->where('team_two_ban_two_id', $this->id)
                ->where('team_two_id', $team->id)
                ->count()
                +
                $team->games->where('team_one_ban_three_id', $this->id)
                ->where('team_one_id', $team->id)
                ->count()
                +
                $team->games->where('team_two_ban_three_id', $this->id)
                ->where('team_two_id', $team->id)
                ->count();
    }

    public function getTeamWinrate($team)
    {
        if ($this->getTeamPicks($team) != 0) {
            return round($this->gameParticipations->where('team_id', $team->id)->where('winner_id', $team->id)->count() / $this->getTeamPicks($team), 2);
        } else {
            return '';
        }
    }
}
