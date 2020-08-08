<?php namespace Rikki\Heroeslounge\classes\MMR;

use Rikki\Heroeslounge\classes\MMR\AuthCode;
use Rikki\Heroeslounge\Models\Sloth as SlothModel;
use Log;

class MMRFetcher
{
    const DEFAULT_MMR = 3000;
    const MINIMUM_MATCHES_PER_GAMEMODE = 150;

    public static function fetchMMR()
    {
        $sloths = SlothModel::where('mmr', 0)->get();

        foreach ($sloths as $sloth) {
            set_time_limit(30);
            MMRFetcher::updateMMR($sloth);
        }
    }

    public static function updateMMRs()
    {
        $sloths = SlothModel::all();

        foreach ($sloths as $sloth) {
            set_time_limit(30);
            MMRFetcher::updateMMR($sloth);
        }
    }

    public static function updateMMR($sloth)
    {
        $battletag = $sloth->battle_tag;
        $region = $sloth->getHeroesProfileRegionId();

        $url = 'https://api.heroesprofile.com/api/Player/MMR/?mode=json&api_token=' . AuthCode::getHeroesProfileKey() . '&battletag=' . urlencode($battletag) . '&region=' . $region;

        $ch = curl_init($url);
        $headers = [];

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, function($curl, $header) use (&$headers) {
            $len = strlen($header);
            $header = explode(':', $header, 2);
            if (count($header) < 2) {
                return $len;
            } 

            $headers[strtolower(trim($header[0]))][] = trim($header[1]);
            return $len;
        });

        $output = curl_exec($ch);

        curl_close($ch);

        SlothModel::where('id', $sloth->id)->update(['heroesprofile_mmr' => self::DEFAULT_MMR]);
        if ($output != "null") {
            $data = json_decode($output, true);

            if ($data != null) {
                if (array_key_exists($battletag, $data)) {
                    $mmrData = $data[$battletag];

                    $mmrs = array();
                    $mmrs["Quick Match"] = -1000;
                    $mmrs["Unranked Draft"] = -1000;
                    $mmrs["Storm League"] = -1000;

                    $games_played = array();
                    $games_played["Quick Match"] = 0;
                    $games_played["Unranked Draft"] = 0;
                    $games_played["Storm League"] = 0;

                    foreach ($mmrs as $key => $value) {	
                        if (array_key_exists($key, $mmrData)) {
                            $mmrs[$key] = $mmrData[$key]["mmr"];
                            $games_played[$key] = $mmrData[$key]["games_played"];
                        }
                    }

                    $slWeight = 70;
                    $udWeight = 30;

                    $sumWeight = 0;
                    $sumMMR = 0;

                    if ($mmrs["Storm League"] != -1000 && $games_played["Storm League"] >= self::MINIMUM_MATCHES_PER_GAMEMODE) {
                        $sumWeight += $slWeight;
                        $sumMMR += $mmrs["Storm League"] * $slWeight;
                    }
                    if ($mmrs["Unranked Draft"] != -1000 && $games_played["Unranked Draft"] >= self::MINIMUM_MATCHES_PER_GAMEMODE) {
                        $sumWeight += $udWeight;
                        $sumMMR += $mmrs["Unranked Draft"] * $udWeight;
                    }

                    if ($sumMMR == 0 && $games_played["Quick Match"] >= self::MINIMUM_MATCHES_PER_GAMEMODE) {
                        $sumMMR += $mmrs["Quick Match"];
                        $sumWeight = 1;
                    } else {
                        if ($mmrs["Storm League"] != -1000) {
                            $sumWeight += $slWeight;
                            $sumMMR += $mmrs["Storm League"] * $slWeight;
                        }
                        if ($mmrs["Unranked Draft"] != -1000) {
                            $sumWeight += $udWeight;
                            $sumMMR += $mmrs["Unranked Draft"] * $udWeight;
                        }
                    }

                    if ($sumWeight > 0) {
                        $weightedMMR = $sumMMR/$sumWeight;
                        SlothModel::where('id', $sloth->id)->update(['heroesprofile_mmr' => $weightedMMR]);
                    }
                }
            }
        }

        // Check if we've hit the rate-limit and retry the request.
        if (array_key_exists('retry-after', $headers)) {
            $throttleTime = $headers['retry-after'][0];

            if ($throttleTime > 0) {
                sleep($throttleTime);
                MMRFetcher::updateMMRHeroesProfile($sloth);
            }
        }
    }
}
