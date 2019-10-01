<?php namespace Rikki\Heroeslounge\classes\hotslogs;

use Rikki\Heroeslounge\classes\MMR\AuthCode;
use Rikki\Heroeslounge\Models\Sloth as SlothModel;
use Log;

class IDFetcher
{
    public static function fetchIDs()
    {
        $sloths = SlothModel::all();

        foreach ($sloths as $sloth) {
            set_time_limit(60);
            $throttleTime = IDFetcher::fetchIDHeroesProfile($sloth);

            if ($throttleTime > 0) {
                sleep($throttleTime);
            }
        }
    }

    public static function fetchID($sloth)
    {
        $battletag = $sloth->battle_tag;
        
        $sloth->hotslogs_id = null;
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

        if ($output != "null") {
            $data = json_decode($output, true);

            if ($data != null) {
                if (array_key_exists("PlayerID", $data)) {
                    $sloth->hotslogs_id = $data["PlayerID"];
                }
            }
        }
        $sloth->save();
    }

    public static function fetchIDHeroesProfile($sloth)
    {
        $battletag = $sloth->battle_tag;
        $region = $sloth->getHeroesProfileRegionId();
        
        $sloth->heroesprofile_id = null;

        $url = 'https://api.heroesprofile.com/api/Player?mode=json&api_token=' . AuthCode::getHeroesProfileKey() . '&battletag=' . urlencode($battletag) . '&region=' . $region;

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
        if ($output != "null") {
            $data = json_decode($output, true);
            if ($data != null) {
                if (array_key_exists("blizz_id", $data)) {
                    $sloth->heroesprofile_id = $data["blizz_id"];
                }
            }
        }
        $sloth->save();

        // Returns the throttle time to wait for the next request.
        if (array_key_exists('retry-after', $headers)) {
            return $headers['retry-after'][0];
        }

        return 0;
    }
}
