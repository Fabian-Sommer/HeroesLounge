<?php namespace Rikki\Heroeslounge\classes\hotfixes;

use Rikki\Heroeslounge\Models\Division;
use Rikki\Heroeslounge\Models\Match as Matches;
use Rikki\Heroeslounge\Models\Team as Teams;
use Rikki\Heroeslounge\Models\Game;

use Carbon\Carbon;
use Db;
use Log;

class DivisionTableFix
{
    public function fixTables($s)
    {
        $divisions = $s->divisions()->get();

        foreach ($divisions as $div) {
            $teams = $div->teams()->get();

            foreach ($teams as $team) {
                $matchIds = $team->matches->map(function($match) {
                    return $match->id;
                });
                
                $freeWinCount = 0;
                foreach ($matchIds as $matchId) {
                    $freeWin = (Db::table('rikki_heroeslounge_games')->where('id', $matchId)->where('map_id', 1)->count() == 2);

                    $freeWinFekkerPlebId = Db::table('rikki_heroeslounge_match')->where('id', $matchId)->value('winner_id');
                    $correctDivision = Db::table('rikki_heroeslounge_match')->where('id', $matchId)->where('div_id', $div->id)->count();
                    if ($freeWin && $team->id == $freeWinFekkerPlebId && $correctDivision == 1) {
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
        }
    }
}
