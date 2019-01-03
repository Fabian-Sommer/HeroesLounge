<?php namespace Rikki\Heroeslounge\Http;

use Backend\Classes\Controller;
use Rikki\Heroeslounge\Models\Match as MatchModel;
use Rikki\Heroeslounge\Models\Game as GameModel;
use Log;
use Carbon\Carbon;
/**
 * Match Back-end Controller
 */
class Match extends Controller
{
    public $implement = [
        'Mohsin.Rest.Behaviors.RestController'
    ];

    public $restConfig = 'config_rest.yaml';

    public function indexAll()
    {
        return MatchModel::all();
    }

    public function teams($id)
    {
        return MatchModel::findOrFail($id)->teams;
    }

    public function replays($id)
    {
        $retVal = [];
        $games = MatchModel::find($id)->games;
        $games->each(function($g) use(&$retVal)
        {
            $retVal[] = $g->replay;
        });
      return json_encode($retVal);
    }

    public function games($id)
    {
        $retVal = [];
        $games = MatchModel::find($id)->games;
        $games->each(function($g) use(&$retVal)
        {
            $gameData = ['Team One' => ($g->teamOne != null ? $g->teamOne->title : $g->match->teams[0]->title),
                         'Team Two' => ($g->teamTwo != null ? $g->teamTwo->title : $g->match->teams[1]->title),
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
                            $r['Team'] = $gamePart->team->title;
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
        return json_encode($retVal);
    }
    
    public function getTodaysMatches()
    {
        return json_encode(MatchModel::whereDate('wbp','=',date(Carbon::today()))->where('is_played',false)->get());
    }

    public function caster($id)
    {
        return json_encode(Matchmodel::findOrFail($id)->casters);
    }

    public function withApprovedCastBetween($startdate, $enddate)
    {
        return json_encode(MatchModel::whereDate('wbp','>=',date($startdate))->whereDate('wbp','<=',date($enddate))->get()->filter(function ($match) {
            return !$match->getAcceptedCasters()->isEmpty();
        })->each(function ($match, $key) {
            return [$match, $match->division, $match->playoff, $match->teams, $match->getAcceptedCasters()];
        }));
    }
}
