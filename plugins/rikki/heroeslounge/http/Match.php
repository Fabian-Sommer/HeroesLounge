<?php namespace Rikki\Heroeslounge\Http;

use Backend\Classes\Controller;
use Rikki\Heroeslounge\Models\Match as MatchModel;
use Rikki\Heroeslounge\Models\Game as GameModel;
use Log;
use Carbon\Carbon;
use Rikki\Heroeslounge\Classes\Helpers\TimezoneHelper;

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

    public function channels($id)
    {
        return MatchModel::findOrFail($id)->channels;
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
      return $retVal;
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
        return $retVal;
    }

    private function formatTimezone($tz1, $tz2)
    {
        if ($tz2) {
            return $tz1 . '/' . $tz2;
        } else {
            return $tz1;
        }
    }

    private function getMatchesForDate($start, $isPlayed)
    {
        // Convert to default/server timezone for db query
        $start->setTime(0,0,0)->setTimezone(TimezoneHelper::DEFAULT_TIMEZONE);
        $end = $start->copy()->addDay();

        if (!$isPlayed) {
            return MatchModel::whereBetween('wbp', [$start, $end])->where('is_played',false)->get();
        } else {
            return MatchModel::whereBetween('wbp', [$start, $end])->get();
        }
    }

    public function getTodaysMatches($tz1 = TimezoneHelper::DEFAULT_TIMEZONE, $tz2 = "")
    {
        $date = Carbon::today(Self::formatTimezone($tz1, $tz2));

        return getMatchesByDate($date, false);
    }

    public function getMatchesByDate($date, $tz1 = TimezoneHelper::DEFAULT_TIMEZONE, $tz2 = "")
    {
        $date = Carbon::createFromFormat('Y-m-d', $date, Self::formatTimezone($tz1, $tz2));

        return getMatchesByDate($date);
    }

    public function caster($id)
    {
        return Matchmodel::findOrFail($id)->casters;
    }

    public function withApprovedCastBetween($startdate, $enddate)
    {
        return MatchModel::whereDate('wbp','>=',date($startdate))->whereDate('wbp','<=',date($enddate))->get()->filter(function ($match) {
            return !$match->getAcceptedCasters()->isEmpty();
        })->each(function ($match, $key) {
            return [$match, $match->channel, $match->division, $match->playoff, $match->teams, $match->getAcceptedCasters()];
        });
    }
}
