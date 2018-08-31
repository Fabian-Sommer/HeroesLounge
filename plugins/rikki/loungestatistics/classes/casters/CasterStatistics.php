<?php namespace Rikki\LoungeStatistics\classes\Casters;

 

use Illuminate\Support\Facades\DB;
use RainLab\User\Models\UserGroup;
use Rikki\Heroeslounge\Models\Season as Seasons;
use RainLab\User\Models\User;
use Log;

class CasterStatistics
{

    public $season = null;
    public $casters = null;
    public $casterRoundData = null;
    public $teamCastData = null;
    public $divisionGamesCast = null;
    public $divisionGamesPlayed = null;
    public $divisionGamesPercentage = null;
    public $castsPerRound = null;
    public $totalGamesCast = 0;
    public $totalGamesPlayed = 0;
    public $totalGamesPercentage = 0;

    function compareCasters($a, $b)
    {
        //title row should stay as first element
        if ($a[0] == 'Round')
        {
            return -1;
        }
        if ($b[0] == 'Round')
        {
            return 1;
        }
        return $a[$this->season->round_length + 2] < $b[$this->season->round_length + 2];
    }

    public function calculateStats()
    {
        if ($this->season == null) {
            return;
        }
        //data per caster
        $queryResult = DB::table('rikki_heroeslounge_match_caster')
                            ->select('users.id', 'users.username', 'rikki_heroeslounge_match.round', DB::raw('COUNT(rikki_heroeslounge_match.id) as matchcount'))
                            ->where('approved', 1)
                            ->join('rikki_heroeslounge_match', 'rikki_heroeslounge_match_caster.match_id', '=', 'rikki_heroeslounge_match.id')
                            ->join('rikki_heroeslounge_divisions', 'rikki_heroeslounge_match.div_id', '=', 'rikki_heroeslounge_divisions.id')
                            ->where('rikki_heroeslounge_divisions.season_id', '=', $this->season->id)
                            ->join('rikki_heroeslounge_sloths', 'rikki_heroeslounge_match_caster.caster_id', '=', 'rikki_heroeslounge_sloths.id')
                            ->join('users', 'rikki_heroeslounge_sloths.user_id', '=', 'users.id')
                            ->groupBy('rikki_heroeslounge_sloths.id', 'rikki_heroeslounge_match.round')
                            ->get();

        $queryPlayoffHelp = 

        $queryResultPlayoffs = DB::table('rikki_heroeslounge_match_caster')
                            ->select('users.id', 'users.username', DB::raw('COUNT(rikki_heroeslounge_match.id) as matchcount'))
                            ->where('approved', 1)
                            ->join('rikki_heroeslounge_match', 'rikki_heroeslounge_match_caster.match_id', '=', 'rikki_heroeslounge_match.id')
                            ->leftJoin('rikki_heroeslounge_divisions', 'rikki_heroeslounge_match.div_id', '=', 'rikki_heroeslounge_divisions.id')
                            ->join('rikki_heroeslounge_playoffs', function($join) {
                                $join->on('rikki_heroeslounge_divisions.playoff_id', '=', 'rikki_heroeslounge_playoffs.id')
                                    ->orOn('rikki_heroeslounge_match.playoff_id', '=', 'rikki_heroeslounge_playoffs.id');
                            })
                            ->where('rikki_heroeslounge_playoffs.season_id', '=', $this->season->id)
                            ->join('rikki_heroeslounge_sloths', 'rikki_heroeslounge_match_caster.caster_id', '=', 'rikki_heroeslounge_sloths.id')
                            ->join('users', 'rikki_heroeslounge_sloths.user_id', '=', 'users.id')
                            ->groupBy('rikki_heroeslounge_sloths.id', 'rikki_heroeslounge_match.round')
                            ->get();
        
        //get casters
        $this->casters = [];
        foreach($queryResult as $resultRow) {
            if (!array_key_exists($resultRow->username, $this->casters)) {
                $this->casters[$resultRow->username] = User::findOrFail($resultRow->id);
            }
        }

        foreach($queryResultPlayoffs as $resultRow) {
            if (!array_key_exists($resultRow->username, $this->casters)) {
                $this->casters[$resultRow->username] = User::findOrFail($resultRow->id);
            }
        }
    
        $this->casterRoundData = array();
        $this->casterRoundData[0] = array();
        $this->casterRoundData[0][0] = 'Round';
        for ($i = 1; $i <= $this->season->round_length; $i++) {
            $this->casterRoundData[0][$i] = $i;
        }
        $this->casterRoundData[0][$this->season->round_length + 1] = 'Playoffs';
        $this->casterRoundData[0][$this->season->round_length + 2] = 'Total';
        foreach ($this->casters as $caster) {
            $this->casterRoundData[$caster->username] = array();
            $this->casterRoundData[$caster->username][0] = $caster->id;
            for ($i = 1; $i <= $this->season->round_length + 2; $i++) {
                $this->casterRoundData[$caster->username][$i] = 0;
            }
        }
        foreach ($queryResult as $resultRow) {
            $this->casterRoundData[$resultRow->username][$resultRow->round] = $resultRow->matchcount;
            $this->casterRoundData[$resultRow->username][$this->season->round_length + 2] += $resultRow->matchcount;
        }

        foreach ($queryResultPlayoffs as $resultRow) {
            $this->casterRoundData[$resultRow->username][$this->season->round_length + 1] = $resultRow->matchcount;
            $this->casterRoundData[$resultRow->username][$this->season->round_length + 2] += $resultRow->matchcount;
        }

        

        

        
        
        uasort($this->casterRoundData, array($this, 'compareCasters'));
        

        //data per team
        $this->teamCastData = array();
        $this->castsPerRound = array();
        $this->divisionGamesCast = array();
        $this->divisionGamesPlayed = array();
        $this->divisionGamesPercentage = array();
        for ($i = 1; $i <= $this->season->round_length; $i++) {
            $this->castsPerRound[$i] = array();
            $this->castsPerRound[$i]['casts'] = 0;
            $this->castsPerRound[$i]['matches'] = 0;
        }
        foreach ($this->season->divisions as $division) {
            $this->teamCastData[$division->title] = array();
            $this->teamCastData[$division->title][0] = array();
            $this->teamCastData[$division->title][0][0] = '';
            for ($i = 1; $i <= $this->season->round_length; $i++) {
                $this->teamCastData[$division->title][0][$i] = 'Round ' . $i;
            }
            $this->teamCastData[$division->title][0][$this->season->round_length + 1] = 'Total';
            foreach ($division->activeTeams as $team) {
                $this->teamCastData[$division->title][$team->title] = array();
                $this->teamCastData[$division->title][$team->title][0] = $team->slug;
                for ($i = 1; $i <= $this->season->round_length; $i++) {
                    $this->teamCastData[$division->title][$team->title][$i] = '';
                }
                $this->teamCastData[$division->title][$team->title][$this->season->round_length + 1] = 0;
            }

            $this->divisionGamesCast[$division->title] = 0;
            $this->divisionGamesPlayed[$division->title] = 0;
            $this->divisionGamesPercentage[$division->title] = 0;
        }
        $queryResult2 = DB::table('rikki_heroeslounge_match_caster')
                            ->select('rikki_heroeslounge_divisions.title as divisionTitle', 'users.username', 'rikki_heroeslounge_match.round', 'rikki_heroeslounge_teams.title as teamTitle')
                            ->where('approved', 1)
                            ->join('rikki_heroeslounge_match', 'rikki_heroeslounge_match_caster.match_id', '=', 'rikki_heroeslounge_match.id')
                            ->where('rikki_heroeslounge_match.is_played', '=', 1)
                            ->join('rikki_heroeslounge_divisions', 'rikki_heroeslounge_match.div_id', '=', 'rikki_heroeslounge_divisions.id')
                            ->where('rikki_heroeslounge_divisions.season_id', '=', $this->season->id)
                            ->join('rikki_heroeslounge_sloths', 'rikki_heroeslounge_match_caster.caster_id', '=', 'rikki_heroeslounge_sloths.id')
                            ->join('users', 'rikki_heroeslounge_sloths.user_id', '=', 'users.id')
                            ->join('rikki_heroeslounge_team_match', 'rikki_heroeslounge_match.id', '=', 'rikki_heroeslounge_team_match.match_id')
                            ->join('rikki_heroeslounge_teams', 'rikki_heroeslounge_team_match.team_id', '=', 'rikki_heroeslounge_teams.id')
                            ->get();
        foreach ($queryResult2 as $resultRow) {
            if (!array_key_exists($resultRow->teamTitle, $this->teamCastData[$resultRow->divisionTitle])) {
                $this->teamCastData[$resultRow->divisionTitle][$resultRow->teamTitle] = array();
                $this->teamCastData[$resultRow->divisionTitle][$resultRow->teamTitle][0] = $resultRow->teamTitle;
                for ($i = 1; $i <= $this->season->round_length; $i++) {
                    $this->teamCastData[$resultRow->divisionTitle][$resultRow->teamTitle][$i] = '';
                }
                $this->teamCastData[$resultRow->divisionTitle][$resultRow->teamTitle][$this->season->round_length + 1] = 0;
            }
            if ($this->teamCastData[$resultRow->divisionTitle][$resultRow->teamTitle][$resultRow->round] == '') {
                $this->teamCastData[$resultRow->divisionTitle][$resultRow->teamTitle][$resultRow->round] = $resultRow->username;
                $this->teamCastData[$resultRow->divisionTitle][$resultRow->teamTitle][$this->season->round_length + 1]++;
                $this->divisionGamesCast[$resultRow->divisionTitle]++;
                $this->totalGamesCast++;
                $this->castsPerRound[$resultRow->round]['casts']++;
            } else {
                $this->teamCastData[$resultRow->divisionTitle][$resultRow->teamTitle][$resultRow->round] .= ' & ' . $resultRow->username;
            }
        }

        $queryResult3 = DB::table('rikki_heroeslounge_match')
                            ->select('rikki_heroeslounge_divisions.title', DB::raw('COUNT(rikki_heroeslounge_match.id) as matchcount'))
                            ->where('rikki_heroeslounge_match.is_played', '=', 1)
                            ->join('rikki_heroeslounge_divisions', 'rikki_heroeslounge_match.div_id', '=', 'rikki_heroeslounge_divisions.id')
                            ->where('rikki_heroeslounge_divisions.season_id', '=', $this->season->id)
                            ->groupBy('rikki_heroeslounge_divisions.id')
                            ->get();
        foreach ($queryResult3 as $resultRow) {
            $this->divisionGamesPlayed[$resultRow->title] = $resultRow->matchcount;
            $this->divisionGamesCast[$resultRow->title] /= 2;
            if ($resultRow->matchcount != 0) {
                $this->divisionGamesPercentage[$resultRow->title] = round(100 * $this->divisionGamesCast[$resultRow->title] / $resultRow->matchcount, 2);
            }
            $this->totalGamesPlayed += $resultRow->matchcount;
        }
        $this->totalGamesCast /= 2; //we count each match twice because joining it to two teams
        
        if ($this->totalGamesPlayed != 0) {
            $this->totalGamesPercentage = round(100 * $this->totalGamesCast / $this->totalGamesPlayed, 2);
        }

        $queryResult4 = DB::table('rikki_heroeslounge_match')
                            ->select('rikki_heroeslounge_match.round', DB::raw('COUNT(rikki_heroeslounge_match.id) as matchcount'))
                            ->where('rikki_heroeslounge_match.is_played', '=', 1)
                            ->join('rikki_heroeslounge_divisions', 'rikki_heroeslounge_match.div_id', '=', 'rikki_heroeslounge_divisions.id')
                            ->where('rikki_heroeslounge_divisions.season_id', '=', $this->season->id)
                            ->groupBy('rikki_heroeslounge_match.round')
                            ->get();

        foreach ($queryResult4 as $resultRow) {
            $this->castsPerRound[$resultRow->round]['matches'] = $resultRow->matchcount;
        }
        for ($i = 1; $i <= $this->season->round_length; $i++) {
            $this->castsPerRound[$i]['casts'] /= 2;
            if ($this->castsPerRound[$i]['matches'] != 0) {
                $this->castsPerRound[$i]['coverage'] = round(100 * $this->castsPerRound[$i]['casts'] / $this->castsPerRound[$i]['matches'], 2);
            } else {
                $this->castsPerRound[$i]['coverage'] = 'undefined';
            }
        }
    }
}
