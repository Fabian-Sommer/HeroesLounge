<?php namespace Rikki\LoungeStatistics\classes\Statistics;

use Rikki\Heroeslounge\Models\Hero;
use Rikki\Heroeslounge\Models\Map;
use October\Rain\Support\Collection;

class Statistics
{
    /*
        Current supported types are:
            - division -> Division api endpoint         => Expects $matches to be defined.
            - sloth -> sloth hero statistics endpoint   => Expects $gameParticipations to be defined.
            - slothAll -> sloth statistics component    => Expects $gameParticipations to be defined.        
    */
    public static function calculateHeroStatistics($type, $gameParticipations, $matches, $season)
    {
        $allHeroes = Hero::all()->sortBy('title');
        $heroesArray = [];
        foreach ($allHeroes as $hero) {
            $heroesArray[$hero->title] = [];
            $heroesArray[$hero->title]['hero'] = $hero;
            $heroesArray[$hero->title]['picks'] = 0;
            $heroesArray[$hero->title]['wins'] = 0;

            if ($type == "sloth") {
                $heroesArray[$hero->title]['bans_by_team'] = 0;
                $heroesArray[$hero->title]['bans_against_team'] = 0;
            } else if ($type == "slothAll") {
                $heroesArray[$hero->title]['bans_by_team'] = 0;
                $heroesArray[$hero->title]['bans_against_team'] = 0;
                $heroesArray[$hero->title]['kills'] = 0;
                $heroesArray[$hero->title]['assists'] = 0;
                $heroesArray[$hero->title]['deaths'] = 0;
                $heroesArray[$hero->title]['siege_dmg'] = 0;
                $heroesArray[$hero->title]['hero_dmg'] = 0;
                $heroesArray[$hero->title]['dmg_taken'] = 0;
                $heroesArray[$hero->title]['healing'] = 0;
                $heroesArray[$hero->title]['xp'] = 0;
            } else if ($type == "division") {
                $heroesArray[$hero->title]['bans'] = 0;
            }
        }

        if ($type == "sloth" || $type == "slothAll") {
            $heroesArray = Self::calculateSlothStatistics($type, $heroesArray, $gameParticipations, $season);
        } else if ($type == "division") {
            $heroesArray = Self::calculateDivisionStatistics($heroesArray, $matches);
        }

        return new Collection($heroesArray);
    }

    private static function calculateSlothStatistics($type, $heroesArray, $gameParticipations, $season)
    {
        foreach ($gameParticipations as $gP) {
            $game = $gP->game;
            $teamId = $gP->team_id;
            if ($gP->hero == null || $game == null || $teamId == null) {
                continue;
            }

            if ($season == null || ($gP->game != null && $gP->game->match != null && $gP->game->match->belongsToSeason($season))) {
                // Find out if we are team one in the replay.
                $isTeamOne = ($game->team_one_id == $teamId);
                
                $game->getTeamOneBans()->each( function ($item) use ($isTeamOne, &$heroesArray) {
                    if ($isTeamOne) {
                        $heroesArray[$item->title]['bans_by_team']++;
                    } else {
                        $heroesArray[$item->title]['bans_against_team']++;
                    }
                });
                $game->getTeamTwoBans()->each( function ($item) use ($isTeamOne, &$heroesArray) {
                    if ($isTeamOne) {
                        $heroesArray[$item->title]['bans_against_team']++;
                    } else {
                        $heroesArray[$item->title]['bans_by_team']++;
                    }
                });

                $heroesArray[$gP->hero->title]['picks']++;
                if ($game->winner_id == $teamId) {
                    $heroesArray[$gP->hero->title]['wins']++;
                }

                if ($type == "slothAll") {
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
        }

        return $heroesArray;
    }

    private static function calculateDivisionStatistics($heroesArray, $matches)
    {
        foreach($matches as $match) {
            foreach ($match->games as $game) {
                $game->getTeamOneBans()->each( function ($item) use (&$heroesArray) {
                    $heroesArray[$item->title]['bans']++;
                });
                $game->getTeamTwoBans()->each( function ($item) use (&$heroesArray) {
                    $heroesArray[$item->title]['bans']++;
                });
                foreach ($game->gameParticipations as $gP) {
                    if ($gP->hero == null) {
                        continue;
                    }

                    $heroesArray[$gP->hero->title]['picks']++;

                    if ($game->winner_id == $gP->team->id) {
                        $heroesArray[$gP->hero->title]['wins']++;
                    }                    
                }
            }
        }

        return $heroesArray;
    }

    public static function calculateHeroStatisticsForTeam($team, $season)
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
        
        $game_count = 0;
        foreach ($team->matches as $match) {
            if ($season == null or $match->belongsToSeason($season)) {
                foreach ($match->games as $game) {
                    $game_count++;

                    //find out which team we are
                    $winner = ($game->winner_id == $team->id);
                    $tO = ($game->team_one_id == $team->id);

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
                        if ($gp->team_id == $team->id) {
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
        return $filteredHeroes;
    }

    public static function calculateMapStatisticsForSloth($gameParticipations, $season)
    {
        $games = [];
        $teamIds = [];
        $gameParticipationsInSeason = [];
        $i = 0;
        foreach ($gameParticipations as $gP) {
            if ($season == null || ($gP->game != null && $gP->game->match != null && $gP->game->match->belongsToSeason($season))) {
                $game = $gP->game;
                $teamId = $gP->team_id;
                if ($game != null && $teamId != null) {
                    $games[$i] = $game;
                    $teamIds[$i] = $teamId;
                    $gameParticipationsInSeason[$i] = $gP;
                    $i = $i + 1;
                }
            }
        }

        $mapArray = Self::analyzeGamesForMapStats($games, $teamIds, $gameParticipationsInSeason);
        $mapCollection = new Collection($mapArray);
        return $mapCollection->reject(function ($map_array) {
            return $map_array['picks_by'] + $map_array['picks_vs'] == 0;
        });
    }

    public static function calculateMapStatisticsForTeam($team, $season)
    {
        $games = [];
        $teamIds = [];
        $i = 0;
        foreach ($team->matches as $match) {
            if ($season == null or $match->belongsToSeason($season)) {
                foreach ($match->games as $game) {
                    $games[$i] = $game;
                    $teamIds[$i] = $team->id;
                    $i = $i + 1;
                }
            }
        }

        $mapArray = Self::analyzeGamesForMapStats($games, $teamIds, null);
        $maps2 = new Collection($mapArray);
        return $maps2->reject(function ($map_array) {
            return $map_array['picks_by'] + $map_array['picks_vs'] == 0;
        });
    }

    // $teamIds has a teamId entry for every game in $games.
    // $gameParticipations has a participation entry for game in $games when analyzing sloths.
    public static function analyzeGamesForMapStats($games, $teamIds, $gameParticipations) {
        $allMaps = Map::all()->sortBy('title');
        $mapArray = [];
        foreach ($allMaps as $map) {
            $mapArray[$map->title] = [];
            $mapArray[$map->title]['map'] = $map;
            $mapArray[$map->title]['picks_by'] = 0;
            $mapArray[$map->title]['picks_vs'] = 0;
            $mapArray[$map->title]['winrate'] = 0;
        }
        foreach ($games as $i=>$game) {
            if ($game->map && $game->replay) {
                if (SELF::isInSecondPickTeam($i, $games, $teamIds, $gameParticipations)) {
                    $mapArray[$game->map->title]['picks_by']++;
                } else {
                    $mapArray[$game->map->title]['picks_vs']++;
                }
                if ($game->winner_id == $teamIds[$i]) {
                    $mapArray[$game->map->title]['winrate']++;
                }
            }
        }
        foreach ($mapArray as $key => $map_array) {
            if ($map_array['picks_by'] + $map_array['picks_vs'] != 0) {
                $map_array['winrate'] = round($map_array['winrate']/(($map_array['picks_by'] + $map_array['picks_vs'])*0.01),2);
                $map_array['winrate'] .= '%';
            } else {
                $map_array['winrate'] = '-';
            }
            $mapArray[$key] = $map_array;
        }
        return $mapArray;
    }

    private static function isInSecondPickTeam($i, $games, $teamIds, $gameParticipations) {
        if ($gameParticipations != null) {
            return $teamIds[$i] == $gameParticipations[$i]->isInSecondPickTeam();
        } else {
            return $teamIds[$i] == $game->getSecondPickTeamId();
        }
    }
}
