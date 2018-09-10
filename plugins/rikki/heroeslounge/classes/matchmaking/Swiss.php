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
    public $pairings; //Stores potential matches
    public $byeId; //Stores the ID of the BYE team
    public $matchesToHandle; //Stores unscheduled/unplayed matches to take care off after deporting teams
    public $currentRound;
    public $currentStandings;

   //Function for checking if a key exists in a twodimensional array
    public function in_array_r($needle, $haystack, $strict = false)
    {
        foreach ($haystack as $item) {
            if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && $this->in_array_r($needle, $item, $strict))) {
                return true;
            }
        }

        return false;
    }

    //Function to permutate a list of pairs for all possible options
    function pairedPerms($arr){
        $val1=$arr[0];
        $pairs_per_set=sizeof($arr)/2;
        foreach($arr as $v1){  // $arr is preserved/static
            $arr=array_slice($arr,1);  // modify/reduce second foreach's $arr
            foreach($arr as $v2){
                if($val1==$v1){
                    $first[]=[$v1,$v2];  // unique pairs as 2-d array containing first element
                }else{
                    $other[]=[$v1,$v2]; // unique pairs as 2-d array not containing first element
                }
            }
        }

        for($i=0; $i<$pairs_per_set; ++$i){  // add one new set of pairs per iteration
            if($i==0){
                foreach($first as $pair){
                    $perms[]=[$pair]; // establish an array of sets containing just one pair
                }
            }else{
                $expanded_perms=[];
                foreach($perms as $set){
                    $values_in_set=[];  // clear previous data from exclusion array
                    array_walk_recursive($set,function($v)use(&$values_in_set){$values_in_set[]=$v;}); // exclude pairs containing these values
                    $candidates=array_filter($other,function($a)use($values_in_set){
                        return !in_array($a[0],$values_in_set) && !in_array($a[1],$values_in_set);
                    });
                    if($i<$pairs_per_set-1){
                        $candidates=array_slice($candidates,0,sizeof($candidates)/2);  // omit duplicate causing candidates
                    }
                    foreach($candidates as $cand){
                        $expanded_perms[]=array_merge($set,[$cand]); // add one set for every new qualifying pair
                    }
                }
                $perms=$expanded_perms;  // overwrite earlier $perms data with new forked data
            }
        }
        return $perms;
    }

    //Repair division table if matchmaking had to be rolled back
    public function repairDivTable($div){
        $teams = $div->teams()->get();

        foreach ($teams as $team) {
            $matchIds = Db::table('rikki_heroeslounge_team_match')->where('team_id', $team->id)->lists('match_id');


            $freeWinCount = 0;
            foreach ($matchIds as $matchId) {
                $freeWin = (Db::table('rikki_heroeslounge_games')->where('id', $matchId)->where('map_id', 1)->count() == 2);

                $freeWinFekkerPlebId = Db::table('rikki_heroeslounge_match')->where('id', $matchId)->value('winner_id');

                if ($freeWin && $team->id == $freeWinFekkerPlebId) {
                    $freeWinCount += 1;
                }
            }

            $gameCount = DB::table('rikki_heroeslounge_match')
                ->whereIn('id', $matchIds)
                ->where('is_played', 1)
                ->where('div_id', $div->id)
                ->count();

            $winCount = DB::table('rikki_heroeslounge_match')
                ->whereIn('id', $matchIds)
                ->where('winner_id', $team->id)
                ->where('div_id', $div->id)
                ->count();

            DB::table('rikki_heroeslounge_team_division')
                ->where('team_id', $team->id)
                ->where('div_id', $div->id)
                ->update(['win_count' => $winCount, 'match_count' => $gameCount, 'free_win_count' => $freeWinCount]);
        }

        Log::debug("Repaired division table for " . $div->slug);
    }

    //Creates a match from a pairing (two team IDs)
    public function createMatch($pairing, $div)
    {
        if ($pairing[0] != null && $pairing[1] != null) {
            $teamA = Teams::find($pairing[0]);
            $teamB = Teams::find($pairing[1]);

            $match = new Match;
            $match->div_id = $div->id;
            $match->round = $div->season->current_round;
            $match->schedule_date = date('Y-m-d H:i:s', strtotime('next sunday 23:55'));
            $match->tbp = date('Y-m-d H:i:s', strtotime('+1 weeks sunday 23:55'));

            $match->save();
            $match->teams()->save(Teams::find($pairing[0]));
            $match->teams()->save(Teams::find($pairing[1]));


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

            Log::debug("Created a match between team IDs " . $teamA->id ." and " . $teamB->id . " in division " . $div->slug);
        }
    }

    //Checks if pairing is valid
    public function isValidPairing($tId, $oppId, $div, $skipPairingCheck = false)
    {
        //Check if team would play itself
        if($tId == $oppId){
            return false;
        }

        //Check if teams played each other before
        foreach (Teams::find($tId)->matches()->get() as $match) {
            if (count($match->teams()->where('team_id', $oppId)->get())==1) {
                return false;
            }
        }

        //See if pairing check has to be skipped in case we're fixing broken pairs
        if (!$skipPairingCheck) {
            //Check if potential opponent is already paired for this round
            foreach ($this->pairings as $pairing) {
                if ($pairing[0] == $oppId || $pairing[1] == $oppId) {
                    return false;
                }
            }
        }

        return true;
    }

    //Check potential pairings
    public function checkPairings($orgTeam, $div)
    {
        //Iterate over list of teams to find potential opponents, sorted from best to worst team
        foreach ($this->currentStandings as $team) {
            if ($this->isValidPairing($orgTeam->id, $team->id, $div)) {
                return $team->id;
            }
        }

        return null;
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
        return [$byeReceiver->id, $byeTeam->id];
    }

    //In case matchmaking ends with an impossible pair, iterate backwards over the pairs to find a pairing to break up which allows for a full set of proper pairs
    public function repairPairs($brokenPair, $div)
    {
        Log::debug('No match found for team IDs ' . $brokenPair[0] . ' and ' . $brokenPair[1] . '. Breaking up matchups to create new match.');

        $foundSolution = false;

        $teamsToShuffle = [$brokenPair[0], $brokenPair[1]]; //Holds teams to create new pairs from
        $keysToRemove = []; //Holds the keys of the pairs that were mixed up, old pairs have to be removed

        end($this->pairings); //Move the internal pointer to the last item in the array;

        while($foundSolution == false){
            $currentPair = current($this->pairings); //Get the current pair the pointer is at
            $keysToRemove[] = key($this->pairings); //Get the key so we can remove the pair later

            //Add the teams of the pair to the list of teams
            $teamsToShuffle[] = $currentPair[0];
            $teamsToShuffle[] = $currentPair[1];

            //Create permutations of the pairs
            $pairPerms = $this->pairedPerms($teamsToShuffle);

            //Go over all the sets of pairs
            foreach($pairPerms as $sets){
                $setIsValid = true;

                //Go over each pair
                foreach($sets as $pairs){
                    //Check if the pair is valid or not, if not skip to the next set
                    if(!$this->isValidPairing($pairs[0], $pairs[1], $div, true)){
                        $setIsValid = false;
                        break;
                    }
                }

                if($setIsValid){ //The set is valid
                    //Remove all the old pairs that were used for new ones
                    foreach($keysToRemove as $key){
                        unset($this->pairings[$key]);
                    }

                    //Create all the new ones
                    foreach($sets as $pairs){
                        $this->pairings[] = [$pairs[0], $pairs[1]];
                    }

                    //Stop the while loop
                    $foundSolution = true;
                    break;
                }

            }

            prev($this->pairings); //Move the pointer back
        }

        if(!$foundSolution){
            Log::error("Didn't find a solution for the broken pair [" . $brokenPair[0] . ", " . $brokenPair[1] . "] in division " . $div->slug);
        }

        return $foundSolution;
    }

    //Do memes OSsloth
    public function prepare($s)
    {
            set_time_limit(600);
            Log::info('Starting matchmaking for season ' . $s->slug . ', round ' . ($s->current_round + 1));

            $this->byeId = Teams::where("title", "BYE!")->first()->id;
            Log::info('Selected BYE team id: ' . $this->byeId);

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
        Log::info('Selected BYE team id: ' . $this->byeId);
        $this->currentRound = $s->current_round;
        $this->makeMatches($div);
    }

    public function makeMatches($div)
    {
        Log::info('Starting matchmaking for division ' . $div->slug);
        $this->pairings = [];
        $unmatchedTeams = [];
        $pairsCorrect = true;

        $this->currentStandings = $this->sortedTeams($div);
        $teams = $this->currentStandings;
        $teamCount = count($teams);

        Log::info("There are " . $teamCount . " teams in division " . $div->slug);

        if (count($teams) % 2 == 1) {
            $this->pairings[] = $this->selectBye($div);
            $teamCount += 1;
        }

        foreach ($teams as $team) {
            if (!$this->in_array_r($team->id, $this->pairings)) {
                $matchedId = $this->checkPairings($team, $div);

                if ($matchedId != null) {
                    $this->pairings[] = [$team->id, $matchedId];
                } else {
                    $unmatchedTeams[] = $team->id;
                }
            }
        }

        if ($teamCount / 2 != count($this->pairings)) {
            $brokenPair = [$unmatchedTeams[0], $unmatchedTeams[1]];

            $pairsCorrect = $this->repairPairs($brokenPair, $div);
        }

        if(!$pairsCorrect){
            Log::error("Couldn't create proper pairs for division ". $div->slug .". No matches have been created!");
        } else {


            try{
                foreach ($this->pairings as $pairing) {
                    $this->createMatch($pairing, $div);
                }

                Log::info("Successfully made pairings for " . $div->slug);
            } catch(\Exception $e){
                Db::rollback();
                $this->repairDivTable($div);

                Log::error("Transaction failed for " . $div->slug);

                throw $e;
            } catch(\Throwable $e){
                Db::rollback();
                $this->repairDivTable($div);

                Log::error("Transaction failed for " . $div->slug);

                throw $e;
            }


        }


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

        foreach ($div->teams()->where('active', 1)->get() as $team) {
            //Games is now two weeks old
            $prevWeekMatch = $team->matches()->where('div_id', $div->id)->where('round', $r-1)->first();
            $remove = false;
            if ($prevWeekMatch && false == $prevWeekMatch->is_played) {
                $this->handleMatch($prevWeekMatch);
                $remove = true;
                $team->save();
                $inactiveTeamCount += 1;
                $logMessage .= 'Team '.$team->title.' was set inactive due to not playing a match within two weeks. Match ID: ' . $prevWeekMatch->id . "\n";
            }
            //Game is now one week old
            $schedulePrevMatch = $team->matches()->where('div_id', $div->id)->where('round', $r)->first();
            if ($schedulePrevMatch && null == $schedulePrevMatch->wbp) {
                $this->handleMatch($schedulePrevMatch);
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

        $this->handleMatches();
        $this->matchesToHandle = []; // Resets the matchesToHandle array after a division has been handled.
    }

    private function handleMatch($match)
    {
        $this->matchesToHandle[] = $match;
    }

    private function handleMatches()
    {
        if (is_array($this->matchesToHandle) && !empty($this->matchesToHandle)) {
            Log::debug("Handling unplayed/unscheduled matches: " . implode("\n", $this->matchesToHandle));
            foreach ($this->matchesToHandle as $match) {
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
}
