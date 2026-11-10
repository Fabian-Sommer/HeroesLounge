<?php namespace Rikki\Heroeslounge\classes\helpers;

use Db;
use Rikki\Heroeslounge\Models\Division;
use Rikki\Heroeslounge\Models\MatchModel;
use Rikki\Heroeslounge\Models\Playoff;

/**
 * Resolves the season and the competition (division / playoff) of matches from their
 * div_id and playoff_id columns, without hydrating the matches themselves.
 *
 * Lets callers decide which matches they need with a couple of small queries, and only
 * then eager load the heavy relations (games, participations, ...) for those matches.
 */
class MatchContextResolver
{
    protected $divisions;
    protected $playoffs;

    /**
     * @param iterable $rows rows (objects) that have a div_id and a playoff_id
     */
    public function __construct($rows)
    {
        $divisionIds = [];
        $playoffIds = [];
        foreach ($rows as $row) {
            if ($row->div_id) {
                $divisionIds[$row->div_id] = true;
            }
            if ($row->playoff_id) {
                $playoffIds[$row->playoff_id] = true;
            }
        }

        $this->divisions = Division::with('season', 'playoff.season')
            ->whereIn('id', array_keys($divisionIds))
            ->get()
            ->keyBy('id');
        $this->playoffs = Playoff::with('season')
            ->whereIn('id', array_keys($playoffIds))
            ->get()
            ->keyBy('id');
    }

    /**
     * Same precedence as MatchModel::getSeasonAttribute().
     */
    public function season($divisionId, $playoffId)
    {
        $division = $this->divisions->get($divisionId);
        $playoff = $this->playoffs->get($playoffId);

        if ($division != null && $division->season != null) {
            return $division->season;
        } elseif ($playoff != null && $playoff->season != null) {
            return $playoff->season;
        } elseif ($division != null && $division->playoff != null && $division->playoff->season != null) {
            return $division->playoff->season;
        }
        return null;
    }

    /**
     * Identifies the competition a match is listed under: its division if it has one,
     * otherwise its playoff. Safe to use in HTML ids.
     */
    public function competitionKey($divisionId, $playoffId)
    {
        if ($this->divisions->has($divisionId)) {
            return 'd' . $divisionId;
        }
        if ($this->playoffs->has($playoffId)) {
            return 'p' . $playoffId;
        }
        return 'none';
    }

    public function competitionTitle($divisionId, $playoffId)
    {
        if ($division = $this->divisions->get($divisionId)) {
            return $division->longTitle;
        }
        if ($playoff = $this->playoffs->get($playoffId)) {
            return $playoff->longTitle;
        }
        return null;
    }

    /**
     * Matches (not soft deleted) the team played in.
     */
    public static function teamMatches($teamId)
    {
        return MatchModel::whereIn(
            'id',
            Db::table('rikki_heroeslounge_team_match')->where('team_id', $teamId)->select('match_id')
        );
    }

    /**
     * Matches (not soft deleted) in which the sloth played at least one game.
     */
    public static function slothMatches($slothId)
    {
        return MatchModel::whereIn(
            'id',
            Db::table('rikki_heroeslounge_gameparticipation as gp')
                ->join('rikki_heroeslounge_games as g', 'g.id', '=', 'gp.game_id')
                ->where('gp.sloth_id', $slothId)
                ->select('g.match_id')
        );
    }
}
