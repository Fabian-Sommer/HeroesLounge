<?php namespace Rikki\LoungeStatistics\Components;

use Cms\Classes\ComponentBase;
use Rikki\Heroeslounge\classes\helpers\MatchContextResolver;
use Rikki\Heroeslounge\Models\Sloth;
use Rikki\Heroeslounge\Models\Season;
use Rikki\Heroeslounge\Models\GameParticipation;
use Rikki\LoungeStatistics\classes\statistics\Statistics as Stats;

use October\Rain\Support\Collection;
use Db;

class SlothStatistics extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Sloth Statistics',
            'description' => 'Provides HeroesLounge Sloth Statistics'
        ];
    }

    public $sloth = null;
    public $heroes = null;
    public $maps = null;
    public $participatedSeasons = null;
    public $selectedSeason = null;

    /**
     * id, hero_id, div_id, playoff_id of every participation of the sloth in a (not deleted)
     * match, with a resolver for the seasons of those matches.
     */
    protected $participationIndex = null;
    protected $resolver = null;

    public function init()
    {
        // Runs for every AJAX request on the page, so no data is loaded here.
        $this->addJs('assets/js/datatables.min.js');
        $this->addJs('assets/js/loungestatistics.js');
        $this->addCss('assets/css/datatables.min.css');
    }

    public function onRender()
    {
        $this->loadSloth();
        $this->participatedSeasons = $this->participationIndex
            ->filter(function ($gP) { return $gP->hero_id != null; })
            ->map(function ($gP) { return $this->resolver->season($gP->div_id, $gP->playoff_id); })
            ->filter()
            ->keyBy('id')
            ->sortByDesc('created_at')->values();

        $this->selectedSeason = $this->participatedSeasons
            ->filter(function ($season) { return $season->is_active; })
            ->last() ?? $this->participatedSeasons->first();

        $this->calculateStats($this->selectedSeason);
    }

    protected function loadSloth()
    {
        $this->sloth = Sloth::findOrFail($this->property('sloth_id'));
        $this->participationIndex = Db::table('rikki_heroeslounge_gameparticipation as gp')
            ->join('rikki_heroeslounge_games as g', 'g.id', '=', 'gp.game_id')
            ->join('rikki_heroeslounge_match as m', 'm.id', '=', 'g.match_id')
            ->leftJoin('rikki_heroeslounge_heroes as h', 'h.id', '=', 'gp.hero_id')
            ->whereNull('m.deleted_at')
            ->where('gp.sloth_id', $this->sloth->id)
            ->select('gp.id', 'h.id as hero_id', 'm.div_id', 'm.playoff_id')
            ->get();
        $this->resolver = new MatchContextResolver($this->participationIndex);
    }

    /**
     * The participations to calculate the statistics from: those in the given season,
     * or all of them when no season is given.
     */
    protected function participations($season)
    {
        $query = GameParticipation::where('sloth_id', $this->sloth->id);
        if ($season != null) {
            $ids = $this->participationIndex
                ->filter(function ($gP) use ($season) {
                    $gPSeason = $this->resolver->season($gP->div_id, $gP->playoff_id);
                    return $gPSeason && $gPSeason->id == $season->id;
                })
                ->pluck('id');
            if ($ids->isEmpty()) {
                return collect();
            }
            $query->whereIn('id', $ids);
        }
        return $query
            ->with('hero', 'game', 'game.map', 'game.replay', 'game.teamOneFirstBan', 'game.teamOneSecondBan', 'game.teamOneThirdBan', 'game.teamTwoFirstBan', 'game.teamTwoSecondBan', 'game.teamTwoThirdBan')
            ->get();
    }

    public function calculateStats($season)
    {
        // The participations are already limited to the season.
        $gameParticipations = $this->participations($season);
        $mapStats = Stats::calculateMapStatisticsForSloth($gameParticipations, null);
        $this->maps = $mapStats->sortByDesc(function ($map_array) {
            return $map_array['picks_by'] + $map_array['picks_vs'];
        });
        $gamecount = $mapStats->reduce(function ($carry, $map) {
            return $carry + $map["picks_by"] + $map["picks_vs"];
        }, 0);

        $heroStats = Stats::calculateHeroStatistics("slothAll", $gameParticipations, null, null);
        foreach ($heroStats as $key => $hero_array) {
            if ($hero_array['picks'] > 0) {
                $hero_array['winrate'] = round($hero_array['wins'] / (0.01 * $hero_array['picks']),2);
                $hero_array['winrate'] .= '%';
                $hero_array['kills'] = round($hero_array['kills'] / ($hero_array['picks']),1);
                $hero_array['assists'] = round($hero_array['assists'] / ($hero_array['picks']),1);
                $hero_array['deaths'] = round($hero_array['deaths'] / ($hero_array['picks']),1);
                $hero_array['siege_dmg'] = round($hero_array['siege_dmg'] / ($hero_array['picks']),0);
                $hero_array['hero_dmg'] = round($hero_array['hero_dmg'] / ($hero_array['picks']),0);
                $hero_array['dmg_taken'] = round($hero_array['dmg_taken'] / ($hero_array['picks']),0);
                $hero_array['healing'] = round($hero_array['healing'] / ($hero_array['picks']),0);
                $hero_array['xp'] = round($hero_array['xp'] / ($hero_array['picks']),0);
            } else {
                $hero_array['winrate'] = '-';
                $hero_array['kills'] = '-';
                $hero_array['assists'] = '-';
                $hero_array['deaths'] = '-';
                $hero_array['siege_dmg'] = '-';
                $hero_array['hero_dmg'] = '-';
                $hero_array['dmg_taken'] = '-';
                $hero_array['healing'] = '-';
                $hero_array['xp'] = '-';
            }
            if ($gamecount > 0) {
                $hero_array['pick_popularity'] = round($hero_array['picks'] / (0.01 * $gamecount),1);
                $hero_array['bat_popularity'] = round($hero_array['bans_against_team'] / (0.01 * $gamecount),1);
                $hero_array['bbt_popularity'] = round($hero_array['bans_by_team'] / (0.01 * $gamecount),1);
            } else {
                $hero_array['pick_popularity'] = '-';
                $hero_array['bat_popularity'] = '-';
                $hero_array['bbt_popularity'] = '-';
            }
            $heroStats[$key] = $hero_array;
        }
        $this->heroes = $heroStats->sortByDesc(function ($hero_array) {
            return $hero_array['picks'] + $hero_array['bans_by_team'] + $hero_array['bans_against_team'];
        });
    }

    public function onSeasonChange()
    {
        $this->loadSloth();
        $season_id = input('season_id');
        if ($season_id == -1) {
            $this->selectedSeason = null;
        } else {
            $this->selectedSeason = Season::find($season_id);
        }
        $this->calculateStats($this->selectedSeason);
        return [
            '#slothstatistics' => $this->renderPartial('@stats')
        ];
    }

    public function defineProperties()
    {
       return [
            'sloth_id' => [
                'title' => 'Sloth ID',
                'description' => 'Sloth ID to grab data from',
                'default' => 0,
                'type' => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'The team_id property can contain only numeric symbols'
            ]
        ];
    }
}
