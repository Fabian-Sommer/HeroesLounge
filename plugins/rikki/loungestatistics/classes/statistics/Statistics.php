<?php namespace Rikki\LoungeStatistics\classes\Statistics;

use Rikki\Heroeslounge\Models\Hero;
use Rikki\Heroeslounge\Models\Map;

use October\Rain\Support\Collection;

class Statistics
{
    /*
        Current supported types are:
            - division -> Division api endpoint         => Expects $rawData of type Matches.
            - sloth -> sloth hero statistics endpoint   => Expects $rawData of type gameParticipations.
            - slothAll -> sloth statistics component    => Expects $rawData of type gameParticipations.        
    */
    public static function calculateHeroStatistics($type, $rawData, $season)
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
            $heroesArray = Self::calculateSlothStatistics($type, $heroesArray, $rawData, $season);
        } else if ($type == "division") {
            $heroesArray = Self::calculateDivisionStatistics($heroesArray, $rawData);
        }

        $heroesCollection =  new Collection($heroesArray);
        return $heroesCollection->reject(function ($hero_array) use ($type) {
            if ($type == "sloth" || $type == "slothAll") {
                return $hero_array['picks'] + $hero_array['bans_by_team'] +  $hero_array['bans_against_team'] == 0;
            } else if ($type == "division") {
                return $hero_array['picks'] + $hero_array['bans'] == 0;
            }
        });
    }

    private static function calculateSlothStatistics($type, $heroesArray, $rawData, $season)
    {
        foreach ($rawData as $gP) {
            $game = $gP->game;
            $team = $gP->team;
            if ($gP->hero == null || $game == null || $team == null) {
                continue;
            }

            if ($season == null || ($gP->game != null && $gP->game->match != null && $gP->game->match->belongsToSeason($season))) {
                // Find out if we are team one in the replay.
                $isTeamOne = ($game->team_one_id == $team->id);
                
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
                if ($game->winner_id == $team->id) {
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

    private static function calculateDivisionStatistics($heroesArray, $rawData)
    {
        foreach($rawData as $match) {
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

    // $rawData expects gameParticipations.
    public static function calculateMapStatistics($rawData, $season)
    {
        $allMaps = Map::all()->sortBy('title');
        $mapArray = [];
        foreach ($allMaps as $map) {
            $mapArray[$map->title] = [];
            $mapArray[$map->title]['map'] = $map;
            $mapArray[$map->title]['picks_by'] = 0;
            $mapArray[$map->title]['picks_vs'] = 0;
            $mapArray[$map->title]['winrate'] = 0;
        }

        foreach ($rawData as $gP) {
            if ($season == null || ($gP->game != null && $gP->game->match != null && $gP->game->match->belongsToSeason($season))) {
                $game = $gP->game;
                $team = $gP->team;
                if ($game == null or $team == null) {
                    continue;
                }

                if ($game->map && $game->replay) {
                    if ($team->title == $game->getSecondPickTeam()) {
                        $mapArray[$game->map->title]['picks_by']++;
                    } else {
                        $mapArray[$game->map->title]['picks_vs']++;
                    }

                    if ($game->winner_id == $team->id) {
                        $mapArray[$game->map->title]['winrate']++;
                    }
                }
            }
        }

        $mapCollection = new Collection($mapArray);
        return $mapCollection->reject(function ($map_array) {
            return $map_array['picks_by'] + $map_array['picks_vs'] == 0;
        });
    }
}
