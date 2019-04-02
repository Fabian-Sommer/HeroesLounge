<?php namespace Rikki\LoungeStatistics\Components;

use Cms\Classes\ComponentBase;
use Rikki\Heroeslounge\Models\Hero;
use Rikki\Heroeslounge\Models\Team;
use Rikki\Heroeslounge\Models\Map;
use Rikki\Heroeslounge\Models\Season;
use Rikki\Heroeslounge\Models\GameParticipation;

use October\Rain\Support\Collection;
use Db;
use Log;

class TeamStatistics extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Team Statistics',
            'description' => 'Provides HeroesLounge Team Statistics'
        ];
    }

    public $team = null;
    public $heroes = null;
    public $maps = null;
    public $participatedSeasons = null;
    public $selectedSeason = null;

    public function init()
    {
        $this->addJs('assets/js/datatables.min.js');
        $this->addJs('assets/js/loungestatistics.js');
        $this->addCss('assets/css/datatables.min.css');

        $this->team = Team::where('id', $this->property('team_id'))
                        ->with('matches', 'matches.games', 'matches.games.map', 'matches.games.gameParticipations', 'matches.games.gameParticipations.hero', 'matches.games.teamOneFirstBan', 'matches.games.teamOneSecondBan', 'matches.games.teamTwoFirstBan', 'matches.games.teamTwoSecondBan', 'matches.games.teamOneThirdBan', 'matches.games.teamTwoThirdBan')
                        ->first();

        $teamSeasonsSet = [];
        foreach ($this->team->matches as $match) {
            foreach ($match->associatedSeasons() as $season) {
                if ($season) {
                    $teamSeasonsSet[$season->id] = $season;

                    if ($season->is_active && ($this->selectedSeason == null || $season->created_at < $this->selectedSeason->created_at)) {
                        $this->selectedSeason = $season;
                    }
                }
            }
        }

        $this->participatedSeasons = array_values($teamSeasonsSet);
        usort($this->participatedSeasons, function($a, $b) {
            if ($a->created_at == $b->created_at) return 0;
            return $a->created_at > $b->created_at ? 1 : -1;
        });

        if ($this->selectedSeason == null) {
            $participatedSeasonsCount = count($this->participatedSeasons);
            if ($participatedSeasonsCount > 0) {
                $this->selectedSeason = $this->participatedSeasons[$participatedSeasonsCount - 1];
            }
        }

        $this->calculateStats($this->selectedSeason);
    }

    public function calculateStats($season)
    {
        $allHeroes = Hero::all()->sortBy('title');
        $heroesArray = [];
        foreach ($allHeroes as $hero) {
            $heroesArray[$hero->title] = [];
            $heroesArray[$hero->title]['hero'] = $hero;
            $heroesArray[$hero->title]['picks'] = 0;
            $heroesArray[$hero->title]['popularity'] = 0;
            $heroesArray[$hero->title]['winrate'] = 0;
            $heroesArray[$hero->title]['bans_against_team'] = 0;
            $heroesArray[$hero->title]['bans_by_team'] = 0;
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
        $game_count = 0;
        foreach ($this->team->matches as $match) {
            if ($season == null or $match->belongsToSeason($season)) {
                foreach ($match->games as $game) {
                    $game_count++;

                    //find out which team we are
                    $winner = ($game->winner_id == $this->team->id);
                    $tO = ($game->team_one_id == $this->team->id);

                    if ($game->map && $game->replay) {
                        if ($this->team->title == $game->getSecondPickTeam()) {
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
                    foreach ($game->gameParticipations as $gp) {
                        if ($gp->hero == null) {
                            continue;
                        }
                        if ($gp->team_id == $this->team->id) {
                            $heroesArray[$gp->hero->title]['picks']++;
                            if ($winner) {
                                $heroesArray[$gp->hero->title]['winrate']++;
                            }
                        }
                    }
                }
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
            } else {
                $hero_array['winrate'] = '-';
            }
            if ($game_count > 0) {
                $hero_array['pick_popularity'] = round($hero_array['picks'] / (0.01 * $game_count),1);
                $hero_array['bat_popularity'] = round($hero_array['bans_against_team'] / (0.01 * $game_count),1);
                $hero_array['bbt_popularity'] = round($hero_array['bans_by_team'] / (0.01 * $game_count),1);
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
        $season_id = input('season_id');
        if ($season_id == -1) {
            $this->selectedSeason = null;
        } else {
            $this->selectedSeason = Season::find($season_id);
        }
        $this->calculateStats($this->selectedSeason);
        return [
            '#teamstatistics' => $this->renderPartial('@stats')
        ];
    }

    public function defineProperties()
    {
       return [
            'team_id' => [
                'title' => 'Team ID',
                'description' => 'Team ID to grab data from',
                'default' => 0,
                'type' => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'The team_id property can contain only numeric symbols'
            ]
        ];
    }
}
