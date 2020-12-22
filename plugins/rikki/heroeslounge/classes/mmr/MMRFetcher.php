<?php namespace Rikki\Heroeslounge\classes\MMR;

use Rikki\Heroeslounge\classes\MMR\AuthCode;
use Rikki\Heroeslounge\Models\Sloth as SlothModel;
use Log;

class MMRFetcher
{
    const DEFAULT_MMR = 3000;
    const MINIMUM_MATCHES_PER_GAMEMODE = 150;

    public static function getMMR($mmrData)
    {
        if (array_key_exists("Storm League", $mmrData) && $mmrData["Storm League"]["games_played"] >= self::MINIMUM_MATCHES_PER_GAMEMODE) {
            return $mmrData["Storm League"]["mmr"];
        }

        if (array_key_exists("Unranked Draft", $mmrData) && $mmrData["Unranked Draft"]["games_played"] >= self::MINIMUM_MATCHES_PER_GAMEMODE) {
            return $mmrData["Unranked Draft"]["mmr"];
        }

        if (array_key_exists("Quick Match", $mmrData) && $mmrData["Quick Match"]["games_played"] >= self::MINIMUM_MATCHES_PER_GAMEMODE) {
            return $mmrData["Quick Match"]["mmr"];
        }
        
        return self::DEFAULT_MMR;
    }

    public static function fetchMMR()
    {
        $sloths = SlothModel::where('mmr', 0)->get();

        foreach ($sloths as $sloth) {
            set_time_limit(30);
            self::updateMMR($sloth);
        }
        Log::info("Finished fetching MMRs");
    }

    public static function updateMMRs()
    {
        $sloths = SlothModel::all()->reverse();
        foreach ($sloths as $sloth) {
            set_time_limit(30);
            self::updateMMR($sloth);
        }
        Log::info("Finished updating MMRs");
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
            if ($data != null && array_key_exists($battletag, $data)) {
                $mmrData = $data[$battletag];
                SlothModel::where('id', $sloth->id)->update(['heroesprofile_mmr' => self::getMMR($mmrData)]);
            }
        }

        // Check if we've hit the rate-limit and retry the request.
        if (array_key_exists('retry-after', $headers)) {
            $throttleTime = $headers['retry-after'][0];
            if ($throttleTime > 0) {
                set_time_limit($throttleTime + 30);
                sleep($throttleTime);
                self::updateMMR($sloth);
            }
        }
    }
}
