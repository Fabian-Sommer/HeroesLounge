<?php namespace Rikki\Heroeslounge\classes\MMR;

use Rikki\Heroeslounge\classes\MMR\AuthCode;
use Rikki\Heroeslounge\Models\Sloth as SlothModel;
use Log;

class MMRFetcher
{
    public static function fetchMMR()
    {
        $sloths = SlothModel::where('mmr', 0)->get();

        foreach ($sloths as $sloth) {
            set_time_limit(60);
            $throttleTime = MMRFetcher::updateMMRHeroesProfile($sloth);

            if ($throttleTime > 0) {
                sleep($throttleTime);
            }
        }
    }

    public static function updateMMRs()
    {
        $sloths = SlothModel::all();

        foreach ($sloths as $sloth) {
            set_time_limit(60);
            $throttleTime = MMRFetcher::updateMMRHeroesProfile($sloth);

            if ($throttleTime > 0) {
                sleep($throttleTime);
            }
        }
    }

    public static function updateMMR($sloth)
    {
        $battletag = $sloth->battle_tag;
        $battletagReformatted = str_replace("#", "_", $battletag);

        $region = "2";
        if ($sloth->region_id == 2) {
            $region = "1";
        }
        $url = 'https://www.hotslogs.com/API/Players/'.$region.'/'.urlencode($battletagReformatted);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $output = curl_exec($ch);

        curl_close($ch);

        if ($output == "null") {
            $urlNA = 'https://www.hotslogs.com/API/Players/1/'.urlencode($battletagReformatted);

            $chNA = curl_init($urlNA);
            curl_setopt($chNA, CURLOPT_RETURNTRANSFER, true);

            $output = curl_exec($chNA);

            curl_close($chNA);
        }

        SlothModel::where('id', $sloth->id)->update(['mmr' => 0, 'all_mmr' => 3000]);

        if ($output != "null") {
            $data = json_decode($output, true);

            if ($data != null) {
                if (array_key_exists("LeaderboardRankings", $data)) {
                    $mmrData = $data["LeaderboardRankings"];

                    $mmrs = array();
                    $mmrs["QuickMatch"] = ["isRanked" => false, "mmr" => -1000];
                    $mmrs["HeroLeague"] = ["isRanked" => false, "mmr" => -1000];
                    $mmrs["TeamLeague"] = ["isRanked" => false, "mmr" => -1000];
                    $mmrs["UnrankedDraft"] = ["isRanked" => false, "mmr" => -1000];
                    $mmrs["StormLeague"] = ["isRanked" => false, "mmr" => -1000];

                    for ($i = 0; $i < 5; $i++) {
                        if (array_key_exists($i, $mmrData)) {
                            $mmrs[$mmrData[$i]["GameMode"]]["isRanked"] = ($mmrData[$i]["LeagueRank"] != null);
                            $mmrs[$mmrData[$i]["GameMode"]]["mmr"] = $mmrData[$i]["CurrentMMR"];
                        }
                    }

                    $slWeight = 70;
                    $udWeight = 30;

                    $rankedWeight = 0;
                    $rankedMMR = 0;

                    $allWeight = 0;
                    $allMMR = 0;

                    if ($mmrs["StormLeague"]["isRanked"]) {
                        $rankedWeight += $slWeight;
                        $rankedMMR += $mmrs["StormLeague"]["mmr"] * $slWeight;
                    }
                    if ($mmrs["UnrankedDraft"]["isRanked"]) {
                        $rankedWeight += $udWeight;
                        $rankedMMR += $mmrs["UnrankedDraft"]["mmr"] * $udWeight;
                    }

                    if ($mmrs["StormLeague"]["mmr"] != -1000) {
                        $allWeight += $slWeight;
                        $allMMR += $mmrs["StormLeague"]["mmr"] * $slWeight;
                    }
                    if ($mmrs["UnrankedDraft"]["mmr"] != -1000) {
                        $allWeight += $udWeight;
                        $allMMR += $mmrs["UnrankedDraft"]["mmr"] * $udWeight;
                    }


                    if ($allMMR == 0) {
                        $allMMR += $mmrs["QuickMatch"]["mmr"];
                        $allWeight = 1;
                    }

                    if ($rankedWeight > 0) {
                        $weightedMMR = $rankedMMR/$rankedWeight;
                        SlothModel::where('id', $sloth->id)->update(['mmr' => $weightedMMR]);
                    }

                    if ($allWeight > 0) {
                        $weightedMMR = $allMMR/$allWeight;
                        SlothModel::where('id', $sloth->id)->update(['all_mmr' => $weightedMMR]);
                    }
                }
            }
        }
    }

    public static function updateMMRHeroesProfile($sloth)
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

        SlothModel::where('id', $sloth->id)->update(['heroesprofile_mmr' => 2900]);
        if ($output != "null") {
            $data = json_decode($output, true);

            if ($data != null) {
                if (array_key_exists($battletag, $data)) {
                    $mmrData = $data[$battletag];

                    $mmrs = array();
                    $mmrs["Quick Match"] = -1000;
                    $mmrs["Unranked Draft"] = -1000;
                    $mmrs["Storm League"] = -1000;

                    foreach ($mmrs as $key => $value) {	
                        if (array_key_exists($key, $mmrData)) {
                            $mmrs[$key] = $mmrData[$key]["mmr"];
                        }
                    }

                    $slWeight = 70;
                    $udWeight = 30;

                    $sumWeight = 0;
                    $sumMMR = 0;

                    if ($mmrs["Storm League"] != -1000) {
                        $sumWeight += $slWeight;
                        $sumMMR += $mmrs["Storm League"] * $slWeight;
                    }
                    if ($mmrs["Unranked Draft"] != -1000) {
                        $sumWeight += $udWeight;
                        $sumMMR += $mmrs["Unranked Draft"] * $udWeight;
                    }

                    if ($sumMMR == 0) {
                        $sumMMR += $mmrs["Quick Match"];
                        $sumWeight = 1;
                    }

                    if ($sumWeight > 0) {
                        $weightedMMR = $sumMMR/$sumWeight;
                        SlothModel::where('id', $sloth->id)->update(['heroesprofile_mmr' => $weightedMMR]);
                    }
                }
            }
        }

        // Returns the throttle time to wait for the next request.
        if (array_key_exists('retry-after', $headers)) {
            return $headers['retry-after'][0];
        }

        return 0;
    }
}
