<?php namespace Rikki\Heroeslounge\Http;

use Backend\Classes\Controller;
use Rikki\Heroeslounge\Models\Game as GameModel;
use Carbon\Carbon;
/**
 * Game Back-end Controller
 */
class Game extends Controller
{
    public $implement = [
        'Mohsin.Rest.Behaviors.RestController'
    ];

    public $restConfig = 'config_rest.yaml';

    public function indexAll()
    {
        return GameModel::all();
    }

    public function indexAllWithPlayers()
    {
        $retVal = [];
        $games = GameModel::with('gameParticipations')->with('gameParticipations.hero')->with('map')->with('teamOneFirstBan')->with('teamOneSecondBan')->with('teamTwoFirstBan')->with('teamTwoSecondBan')->with('teamOne')->with('teamTwo')->get();
        $games->each(function($g) use(&$retVal)
        {
            $gameData = ['Team One' => ($g->teamOne != null ? $g->teamOne->title : null),
                         'Team Two' => ($g->teamTwo != null ? $g->teamTwo->title : null),
                         'Winner' => ($g->winner ? $g->winner->title : 'None'),
                         'Map' => ($g->map ? $g->map->title : 'None'),
                         'Duration' => $g->duration,
                         'TeamOneFirstBan' => ($g->teamOneFirstBan ? $g->teamOneFirstBan->title : 'None'),
                         'TeamOneSecondBan' => ($g->teamOneSecondBan ? $g->teamOneSecondBan->title : 'None'),
                         'TeamTwoFirstBan' => ($g->teamTwoFirstBan ? $g->teamTwoFirstBan->title : 'None'),
                         'TeamTwoSecondBan' => ($g->teamTwoSecondBan ? $g->teamTwoSecondBan->title : 'None'),
                         'TeamOneLevel' => $g->team_one_level,
                         'TeamTwoLevel' => $g->team_two_level,
                         'Players' => $g->gameParticipations->map(function ($gamePart, $key) {
                            $r = [];
                            $r['Name'] = $gamePart->title;
                            $r['Hero'] = $gamePart->hero->title;
                            $r['Team'] = ($gamePart->team ? $gamePart->team->title : 'None');
                            $r['Draft Position'] = $gamePart->draft_order;
                            $r['Kills'] = $gamePart->kills;
                            $r['Deaths'] = $gamePart->deaths;
                            $r['Assists'] = $gamePart->assists;
                            $r['Experience Contribution'] = $gamePart->experience_contribution;
                            $r['Healing'] = $gamePart->healing;
                            $r['Siege Damage'] = $gamePart->siege_damage;
                            $r['Hero Damage'] = $gamePart->hero_damage;
                            $r['Damage Taken'] = $gamePart->damage_taken;
                            return $r;
                         })];

            $retVal[] = $gameData;
        });
        return $retVal;
    }
}
