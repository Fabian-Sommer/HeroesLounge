<?php namespace Rikki\LoungeStatistics\Components;

use Cms\Classes\ComponentBase;
use Rikki\Heroeslounge\Models\Team;
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

    public function init()
    {
        $this->addJs('assets/js/datatables.min.js');
        $this->addJs('assets/js/loungestatistics.js');
        $this->addCss('assets/css/datatables.min.css');

        $this->sloth = Sloth::where('id', $this->property('sloth_id'))
            ->with('gameParticipations', 'gameParticipations.team', 'gameParticipations.game', 'gameParticipations.game.map', 'gameParticipations.game.match', 'gameParticipations.hero', 'gameParticipations.game.teamOneFirstBan', 'gameParticipations.game.teamOneSecondBan', 'gameParticipations.game.teamOneThirdBan', 'gameParticipations.game.teamTwoFirstBan', 'gameParticipations.game.teamTwoSecondBan', 'gameParticipations.game.teamTwoThirdBan')
            ->first();

        $this->participatedSeasons = $this->sloth->gameParticipations
            ->filter(function ($gP) { return $gP->hero != null && $gP->game != null && $gP->game->match != null; })
            ->map(function ($gP) { return $gP->game->match->season; })
            ->filter(function ($season) { return $season != null; })
            ->groupBy('id')->map(function ($group) { return $group[0]; })  // unique does not work on these
            ->sortByDesc('created_at')->values();

        $this->selectedSeason = $this->participatedSeasons
            ->filter(function ($season) { return $season->is_active; })
            ->last() ?? $this->participatedSeasons->first();

        $this->calculateStats($this->selectedSeason);
    }

    public function calculateStats($season)
    {       
        $data = $this->sloth->gameParticipations;
        $rawHeroStats = Stats::calculateHeroStatistics("slothAll", $data, $season);
        $rawMapStats = Stats::calculateMapStatistics($data, $season);
        $gamecount = $rawMapStats->reduce(function ($carry, $map) {
            return $carry + $map["picks_by"] + $map["picks_vs"];
        }, 0);

        $filteredHeroes = $rawHeroStats->reject(function ($hero_array) {
            return $hero_array['picks'] + $hero_array['bans_by_team'] + $hero_array['bans_against_team'] == 0;
        });
        foreach ($filteredHeroes as $key => $hero_array) {
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
            if ($this->sloth->gameParticipations->count() > 0 && $gamecount > 0) {
                $hero_array['pick_popularity'] = round($hero_array['picks'] / (0.01 * $gamecount),1);
                $hero_array['bat_popularity'] = round($hero_array['bans_against_team'] / (0.01 * $gamecount),1);
                $hero_array['bbt_popularity'] = round($hero_array['bans_by_team'] / (0.01 * $gamecount),1);
            } else {
                $hero_array['pick_popularity'] = '-';
                $hero_array['bat_popularity'] = '-';
                $hero_array['bbt_popularity'] = '-';
            }
            $filteredHeroes[$key] = $hero_array;
        }
        $this->heroes = $filteredHeroes->sortByDesc(function ($hero_array) {
            return $hero_array['picks'] + $hero_array['bans_by_team'] + $hero_array['bans_against_team'];
        });


        $filteredMaps = $rawMapStats->reject(function ($map_array) {
            return $map_array['picks_by'] + $map_array['picks_vs'] == 0;
        });
        foreach ($filteredMaps as $key => $map_array) {
            $map_array['winrate'] = round($map_array['winrate']/(($map_array['picks_by'] + $map_array['picks_vs'])*0.01),2);
            $map_array['winrate'] .= '%';
            $filteredMaps[$key] = $map_array;
        }
        $this->maps = $filteredMaps->sortByDesc(function ($map_array) {
            return $map_array['picks_by'] + $map_array['picks_vs'];
        });
    }

    public function onSeasonChange()
    {
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
