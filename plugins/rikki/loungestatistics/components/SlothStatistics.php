<?php namespace Rikki\LoungeStatistics\Components;

use Cms\Classes\ComponentBase;
use Rikki\Heroeslounge\Models\Hero;
use Rikki\Heroeslounge\Models\Team;
use Rikki\Heroeslounge\Models\Sloth;
use Rikki\Heroeslounge\Models\Season;
use Rikki\Heroeslounge\Models\Map;
use Rikki\Heroeslounge\Models\GameParticipation as GP;

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
    public $allseasons = null;

    public function init()
    {
        $this->addJs('assets/js/datatables.min.js');
        $this->addJs('assets/js/loungestatistics.js');
        $this->addCss('assets/css/datatables.min.css');
        $this->sloth = Sloth::where('id', $this->property('sloth_id'))
                        ->with('gameParticipations', 'gameParticipations.team', 'gameParticipations.game', 'gameParticipations.game.map', 'gameParticipations.game.match', 'gameParticipations.hero', 'gameParticipations.game.teamOneFirstBan', 'gameParticipations.game.teamOneSecondBan', 'gameParticipations.game.teamTwoFirstBan', 'gameParticipations.game.teamTwoSecondBan')
                        ->first();
        $this->allseasons = Season::where('reg_open', false)->get()->sortByDesc('created_at');

        $this->calculateStats($this->allseasons->first());
    }

    public function calculateStats($season)
    {
        $allHeroes = Hero::all()->sortBy('title');
        $heroesArray = [];
        foreach ($allHeroes as $hero) {
            $heroesArray[$hero->title] = [];
            $heroesArray[$hero->title]['hero'] = $hero;
            $heroesArray[$hero->title]['picks'] = 0;
            $heroesArray[$hero->title]['winrate'] = 0;
            $heroesArray[$hero->title]['bans_against_team'] = 0;
            $heroesArray[$hero->title]['bans_by_team'] = 0;
            $heroesArray[$hero->title]['kills'] = 0;
            $heroesArray[$hero->title]['assists'] = 0;
            $heroesArray[$hero->title]['deaths'] = 0;
            $heroesArray[$hero->title]['siege_dmg'] = 0;
            $heroesArray[$hero->title]['hero_dmg'] = 0;
            $heroesArray[$hero->title]['dmg_taken'] = 0;
            $heroesArray[$hero->title]['healing'] = 0;
            $heroesArray[$hero->title]['xp'] = 0;
        }
        $allMaps = Map::all()->sortBy('title');
        $mapArray = [];
        foreach ($allMaps as $map) {
            $mapArray[$map->title] = [];
            $mapArray[$map->title]['map'] = $map;
            $mapArray[$map->title]['picks_by'] = 0;
            $mapArray[$map->title]['picks_vs'] = 0;
            $mapArray[$map->title]['winrate'] = 0;
        }
        $gamecount = 0;
        foreach ($this->sloth->gameParticipations as $gP) {
            if ($gP->hero == null) {
                continue;
            }
            if ($season == null || ($gP->game != null && $gP->game->match != null && $gP->game->match->belongsToSeason($season))) {
                $game = $gP->game;
                $team = $gP->team;
                if ($game == null or $team == null) {
                    continue;
                }
                $gamecount = $gamecount + 1;
                //find out which team we are
                $winner = ($game->winner_id == $team->id);
                $tO = ($game->team_one_id == $team->id);

                if ($game->map && $game->replay) {
                    if ($team->title == $game->getSecondPickTeam()) {
                        $mapArray[$game->map->title]['picks_by']++;
                    } else {
                        $mapArray[$game->map->title]['picks_vs']++;
                    }
                    if ($winner) {
                        $mapArray[$game->map->title]['winrate']++;
                    }
                }
                
                $game->getTeamOneBans()->each( function ($item) use ($tO, &$heroesArray) {
                    if ($tO) {
                        $heroesArray[$item->title]['bans_by_team']++;
                    } else {
                        $heroesArray[$item->title]['bans_against_team']++;
                    }
                });
                $game->getTeamTwoBans()->each( function ($item) use ($tO, &$heroesArray) {
                    if ($tO) {
                        $heroesArray[$item->title]['bans_against_team']++;
                    } else {
                        $heroesArray[$item->title]['bans_by_team']++;
                    }
                });
                $heroesArray[$gP->hero->title]['picks']++;
                if ($winner) {
                    $heroesArray[$gP->hero->title]['winrate']++;
                }
                $hero = $gP->hero;
                $heroesArray[$hero->title]['kills'] += $gP->kills;
                $heroesArray[$hero->title]['assists'] += $gP->assists;
                $heroesArray[$hero->title]['deaths'] += $gP->deaths;
                $heroesArray[$hero->title]['siege_dmg'] += $gP->siege_damage;
                $heroesArray[$hero->title]['hero_dmg'] += $gP->hero_damage;
                $heroesArray[$hero->title]['dmg_taken'] += $gP->damage_taken;
                $heroesArray[$hero->title]['healing'] += $gP->healing;
                $heroesArray[$hero->title]['xp'] += $gP->experience_contribution;
            }
        }
        
        $heroes2 = new Collection($heroesArray);
        $filteredHeroes = $heroes2->reject(function ($hero_array) {
            return $hero_array['picks'] + $hero_array['bans_by_team'] + $hero_array['bans_against_team'] == 0;
        });
        foreach ($filteredHeroes as $key => $hero_array) {
            if ($hero_array['picks'] > 0) {
                $hero_array['winrate'] = round($hero_array['winrate'] / (0.01 * $hero_array['picks']),2);
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
            if ($this->sloth->gameParticipations->count() > 0) {
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

        $maps2 = new Collection($mapArray);
        $filteredMaps = $maps2->reject(function ($map_array) {
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
        if (input('season_id') == -1) {
            $this->calculateStats(null);
        } else {
            $this->calculateStats(Season::find(input('season_id')));
        }
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
