<?php namespace Rikki\Heroeslounge\classes\MMR;

 
use Rikki\Heroeslounge\Models\Sloth as SlothModel;
use Log;

class MMRFetcher
{
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
        $battletagReformatted = str_replace("#", "_", $battletag);

        $url = 'https://www.hotslogs.com/API/Players/2/'.urlencode($battletagReformatted);

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

        if ($output != "null") {
            SlothModel::where('id', $sloth->id)->update(['mmr' => 0, 'all_mmr' => 0]);
            $data = json_decode($output, true);

            if ($data != null) {
                if (array_key_exists("LeaderboardRankings", $data)) {
                    $mmrData = $data["LeaderboardRankings"];

                    $mmrs = array();
                    $mmrs["QuickMatch"] = ["isRanked" => false, "mmr" => -1000];
                    $mmrs["HeroLeague"] = ["isRanked" => false, "mmr" => -1000];
                    $mmrs["TeamLeague"] = ["isRanked" => false, "mmr" => -1000];
                    $mmrs["UnrankedDraft"] = ["isRanked" => false, "mmr" => -1000];

                    for ($i = 0; $i < 4; $i++) {
                        if (array_key_exists($i, $mmrData)) {
                            $mmrs[$mmrData[$i]["GameMode"]]["isRanked"] = ($mmrData[$i]["LeagueRank"] != null);
                            $mmrs[$mmrData[$i]["GameMode"]]["mmr"] = $mmrData[$i]["CurrentMMR"];
                        }
                    }

                    $hlWeight = 50;
                    $tlWeight = 30;
                    $udWeight = 20;

                    $rankedWeight = 0;
                    $rankedMMR = 0;

                    $allWeight = 0;
                    $allMMR = 0;

                    if ($mmrs["TeamLeague"]["isRanked"]) {
                        $rankedWeight += $tlWeight;
                        $rankedMMR += $mmrs["TeamLeague"]["mmr"] * $tlWeight;
                    }
                    if ($mmrs["HeroLeague"]["isRanked"]) {
                        $rankedWeight += $hlWeight;
                        $rankedMMR += $mmrs["HeroLeague"]["mmr"] * $hlWeight;
                    }
                    if ($mmrs["UnrankedDraft"]["isRanked"]) {
                        $rankedWeight += $udWeight;
                        $rankedMMR += $mmrs["UnrankedDraft"]["mmr"] * $udWeight;
                    }

                    if ($mmrs["TeamLeague"]["mmr"] != -1000) {
                        $allWeight += $tlWeight;
                        $allMMR += $mmrs["TeamLeague"]["mmr"] * $tlWeight;
                    }
                    if ($mmrs["HeroLeague"]["mmr"] != -1000) {
                        $allWeight += $hlWeight;
                        $allMMR += $mmrs["HeroLeague"]["mmr"] * $hlWeight;
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
}
