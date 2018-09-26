<?php namespace Rikki\Heroeslounge\classes\hotslogs;

use Rikki\Heroeslounge\Models\Sloth as SlothModel;
use Log;

class IDFetcher
{
    public static function fetchIDs()
    {
        $sloths = SlothModel::all();

        foreach ($sloths as $sloth) {
            set_time_limit(30);
            IDFetcher::fetchID($sloth);
        }
    }

    public static function fetchID($sloth)
    {
        $battletag = $sloth->battle_tag;
        
        SlothModel::where('id', $sloth->id)->update(['hotslogs_id' => null]);
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
                    SlothModel::where('id', $sloth->id)->update(['hotslogs_id' => $data["PlayerID"]]);
                }
            }
        }
    }
}
