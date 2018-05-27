<?php namespace Rikki\Heroeslounge\classes\hotfixes;

use Rikki\Heroeslounge\Models\Division as Divisions;
use Rikki\Heroeslounge\Models\Match as Matches;
use Rikki\Heroeslounge\Models\Team as Teams;
use Rikki\Heroeslounge\Models\Season as Seasons;
use Rikki\Heroeslounge\Models\Game;
use Rikki\Heroeslounge\classes\Matchmaking\Swiss;

use Carbon\Carbon;
use Db;

class DivFiveMM
{
    public function EverythingIsBurningPleaseSendHelp()
    {

        $div = Divisions::where('slug', 'division-5')->first();
        $s = Seasons::where('slug', 'season-4')->first();

        $mm = new Swiss;
        $mm->byeId = Teams::where("title", "BYE!")->first()->id;
        $mm->calculateSloths($div, $s->current_round);
        $mm->makeMatches($div);


    }
}
