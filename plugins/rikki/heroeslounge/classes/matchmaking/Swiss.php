<?php namespace Rikki\Heroeslounge\classes\Matchmaking;


use Rikki\Heroeslounge\Models\Division;
use Rikki\Heroeslounge\Models\Match;
use Rikki\Heroeslounge\Models\Team as Teams;
use Rikki\Heroeslounge\Models\Game;

use Carbon\Carbon;
use Db;
use Log;

class Swiss
{
    public $byeId = 204; //Stores the ID of the BYE team
    public $matchesToHandle; //Stores unscheduled/unplayed matches to take care off after deporting teams
    public $currentRound;
    private static $pythonPath = 'python2.7 ';

    public function createMatches($pairings, $div) {
        $timeoffset = ($pairings[0][0]->region_id - 1)*36000;
        $schedule_date = date('Y-m-d H:i:s', strtotime('next sunday 21:55')+$timeoffset);
        $tbp = date('Y-m-d H:i:s', strtotime('+1 weeks sunday 21:55')+$timeoffset);
        $created_at = date('Y-m-d H:i:s' , strtotime('now'));
        foreach ($pairings as $pairing) {
            $this->createMatch($pairing, $div, $div->season->current_round, $created_at, $schedule_date, $tbp);
        }
    }

    //Creates a match from a pairing
    public function createMatch($pairing, $div, $round, $created_at, $schedule_date, $tbp)
    {
        $teamA = $pairing[0];
        $teamB = $pairing[1];

        $mid = Db::select('select MAX(ID) as id from rikki_heroeslounge_match');
        $match_id = $mid[0]->id + 1;
        Db::insert('insert into rikki_heroeslounge_match (id , div_id , round , created_at , updated_at , schedule_date , tbp) values (?,?,?,?,?,?,?)' , [$match_id , $div->id , $round , $created_at , $created_at , $schedule_date , $tbp]);

        $match = Match::find($match_id);
        $match->teams()->save($teamA);
        $match->teams()->save($teamB);

        //In case it's a BYE assign the win
        if ($teamA->id == $this->byeId || $teamB->id == $this->byeId) {
            $winner = ($teamA->id == $this->byeId) ? $teamB : $teamA;
            $match->winner = $winner;
            $match->is_played = 1;
            $match->wbp = date('Y-m-d H:i:s', strtotime('yesterday 12:00'));

            for ($i = 0; $i < 2;$i++) {
                $m = rand(1, 13);
                $g = new Game;
                $g->match_id = $match->id;
                $g->map_id = $m;

                $g->winner_id = $winner->id;

                $g->save();
            }

            $match->save();

            DB::table('rikki_heroeslounge_team_division')
              ->where('team_id', $winner->id)
              ->where('div_id', $div->id)
              ->increment('free_win_count');
        }

        Log::info("Created a match between team IDs " . $teamA->id ." and " . $teamB->id . " in division " . $div->slug);
    }

    //Checks if pairing is valid
    public function isValidPairing($tId, $oppId, $div)
    {
        //Check if teams played each other before
        foreach (Teams::find($tId)->matches()->get() as $match) {
            if (count($match->teams()->where('team_id', $oppId)->get())==1) {
                return false;
            }
        }

        return true;
    }

    //Sorts team from best to worst by default, unless $order is ASC
    //Teams sorted by: Wins (Most) > Number of free wins (Most) > BYEs (Least) > Number of Games (Least)
    //BYEs prefer teams with most games played, least free wins
    public function sortedTeams($div, $order = "DESC")
    {
        $teams = $div->teams()->where('active', 1)->withPivot('win_count')->withPivot('match_count')->withPivot('bye')->withPivot('free_win_count')->where('rikki_heroeslounge_teams.disbanded', 0)->whereNull('rikki_heroeslounge_teams.deleted_at')->orderBy('win_count', $order)->orderBy('free_win_count', "DESC")->orderBy('bye', "ASC")->orderBy('match_count', "ASC")->get();

        if($this->currentRound <= 2){
            $teams = $teams->sortBy(function($team, $key){
                return $team->slothrating;
            });
        }

        return $teams;
    }

    //Find a team at the bottom of the standings to receive a BYE
    public function selectBye($div)
    {
        $teams = $div->teams()->where('active', 1)->withPivot('win_count')->withPivot('match_count')->withPivot('bye')->withPivot('free_win_count')->where('rikki_heroeslounge_teams.disbanded', 0)->whereNull('rikki_heroeslounge_teams.deleted_at')->orderBy('win_count', "ASC")->orderBy('free_win_count', "ASC")->orderBy('bye', "ASC")->orderBy('match_count', "DESC")->get();

        if($this->currentRound <= 2){
            $teams = $teams->sortBy(function($team, $key){
                return $team->slothrating;
            });
        }

        $byeReceiver = null;
        $byeTeam = Teams::where("title", "BYE!")->firstOrFail();

        foreach ($teams as $team) {
            if ($this->isValidPairing($team->id, $byeTeam->id, $div)) {
                $byeReceiver = $team;
                break;
            }
        }

        Log::info("Selected " . $byeReceiver->title . " [" . $byeReceiver->id . "] to receive a BYE in " . $div->slug);
        return $byeReceiver;
    }

    public function findMatching($div, $teams) {
        $matches = $div->matches;
        $teams = $teams->values();
        defined('DS') or define('DS', DIRECTORY_SEPARATOR);
        @chdir('public_html'); //working directory may already be here depending from where this is called, the @ suppresses the error in that case
        $graph_file_path = 'plugins'.DS.'rikki'.DS.'heroeslounge'.DS.'classes'.DS.'matchmaking'.DS.'matching.txt';
        $file = fopen($graph_file_path, "w");

        $vertex_count = $teams->count();

        for ($i = 0; $i < $vertex_count; $i++) {
            $teamMatches = $matches->filter(function ($match) use ($teams, $i)  {
                return $match->teams->contains(function ($team) use ($teams, $i) {
                    return $teams[$i]->id == $team->id;
                });
            });
            for ($j = $i+1; $j < $vertex_count; $j++) {
                //check if the teams already played
                $weight = 0;
                $teamsPlayed = $teamMatches->contains(function ($match) use ($teams, $j) {
                    return $match->teams->contains(function ($team) use ($teams, $j) {
                        return $teams[$j]->id == $team->id;
                    });
                });
                if ($teamsPlayed) {
                    $weight += 1000000;
                }
                if ($this->currentRound <= 2) {
                    $weight += abs($teams[$i]->slothrating - $teams[$j]->slothrating);
                } else {
                    $winDifference = abs($teams[$i]->pivot->win_count - $teams[$j]->pivot->win_count);
                    $lossDifference = abs(($teams[$i]->pivot->match_count - $teams[$i]->pivot->win_count) - ($teams[$j]->pivot->match_count - $teams[$j]->pivot->win_count)); 
                    $weight += pow(10, $winDifference);
                    $weight += pow(9, $lossDifference);
                }

                fwrite($file, $i." ".$j." ".$weight."\n");
            }
        }

        fclose($file);
        
        exec(Swiss::$pythonPath.'plugins'.DS.'rikki'.DS.'heroeslounge'.DS.'classes'.DS.'matchmaking'.DS.'matching.py '.$graph_file_path.' 2>&1', $output, $state);
        $matching = json_decode($output[0]);
        $team_pairs = [];
        foreach ($matching as $key => $m) {
            $team_pairs[] = [$teams[$m[0]] , $teams[$m[1]]];
        }
        unlink($graph_file_path);
        return $team_pairs;
    }

    public function makeMatches($div)
    {
        Log::info('Starting matchmaking for division ' . $div->slug);
        $pairings = [];
        $unmatchedTeams = [];
        $pairsCorrect = true;

        $teams = $this->sortedTeams($div);
        $teamCount = count($teams);

        Log::info("There are " . $teamCount . " teams in division " . $div->slug);
        $byeTeam = Teams::where("title", "BYE!")->firstOrFail();

        $byePair = null;
        if (count($teams) % 2 == 1) {
            $teamWithBye = $this->selectBye($div);
            $teams = $teams->reject(function ($team) use ($teamWithBye) {
                return $team->id == $teamWithBye->id;
            });
            $byePair = [$teamWithBye , $byeTeam];
        }      

        $pairings = $this->findMatching($div, $teams);
        
        if ($byePair != null) {
            $pairings[] = $byePair;
        }

        Log::info("Matching for " . $div->slug . " : " . json_encode($pairings));

        $this->createMatches($pairings, $div);

        Log::info("Successfully ran MM for " . $div->slug);
    }

    public function saveUnsavedMatches($div)
    {
        foreach ($div->matches()->where('is_played', false)->get() as $match) {
            if ($match->games()->count() > 0) {
                $match->determineWinnerAndSave();
            }
        }
    }

    //Current Round hasn't been updated yet
    public function findInactiveTeams($div, $r)
    {
        $inactiveTeamCount = 0;
        $logMessage = "";
        $matchesToHandle = [];

        foreach ($div->teams()->where('active', 1)->get() as $team) {
            //Games is now two weeks old
            $prevWeekMatch = $team->matches()->where('div_id', $div->id)->where('round', $r-1)->first();
            $remove = false;
            if ($prevWeekMatch && false == $prevWeekMatch->is_played) {
                $matchesToHandle[] = $prevWeekMatch;
                $remove = true;
                $team->save();
                $inactiveTeamCount += 1;
                $logMessage .= 'Team '.$team->title.' was set inactive due to not playing a match within two weeks. Match ID: ' . $prevWeekMatch->id . "\n";
            }
            //Game is now one week old
            $schedulePrevMatch = $team->matches()->where('div_id', $div->id)->where('round', $r)->first();
            if ($schedulePrevMatch && null == $schedulePrevMatch->wbp) {
                $matchesToHandle[] = $schedulePrevMatch;
                $remove = true;
                $team->save();
                $inactiveTeamCount += 1;
                $logMessage .= 'Team '.$team->title.' was set inactive due to not scheduling a match within one week.' . $schedulePrevMatch->id . "\n";
            }
            if ($remove == true) {
                $team->pivot->active = false;
                $team->pivot->save();
            }
        }

        $logMessage .= "\n" . $inactiveTeamCount . ' teams were set inactive due to not scheduling/playing on time in division ' . $div->slug;


        Log::info($logMessage);

        if (!empty($matchesToHandle)) {
            Log::debug("Handling unplayed/unscheduled matches: " . implode("\n", $matchesToHandle));
            foreach ($matchesToHandle as $match) {
                $match->is_played = 1;
                $match->winner_id = null;
                $match->games->each(function ($game) {
                    $game->delete();
                });
                $g1 = new Game;
                $g1->winner_id = $match->teams[0]->id;
                $g1->match_id = $match->id;
                $g1->map_id = 1;
                $g1->save();
                $g2 = new Game;
                $g2->map_id = 1;
                $g2->winner_id = $match->teams[1]->id;
                $g2->match_id = $match->id;
                $g2->save();

                $match->save();
            }
        }
    }

    //MM for entire season
    public function doMM($s)
    {
        set_time_limit(600);
        Log::info('Starting matchmaking for season ' . $s->slug . ', round ' . ($s->current_round + 1));

        $this->byeId = Teams::where("title", "BYE!")->first()->id;

        $divs = $s->divisions()->get();

        foreach ($divs as $div) {
            $this->saveUnsavedMatches($div);
            $this->findInactiveTeams($div, $s->current_round);
        }

        $s->current_round += 1;
        $this->currentRound = $s->current_round;
        Log::info("Updated current_round to " . $s->current_round);

        if ($s->current_round == $s->round_length) {
            $s->mm_active = 0;
            Log::info('Reached last round. Matchmaking for ' . $s->slug . ' turned off');
        }
        $s->save();

        foreach ($divs as $div) {
            $this->makeMatches($div);
        }
    }

    public function doMMSingleDivision($s, $div){
        set_time_limit(600);
        $this->byeId = Teams::where("title", "BYE!")->first()->id;
        $this->currentRound = $s->current_round;
        $this->makeMatches($div);
    }
}
