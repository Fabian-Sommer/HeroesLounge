<?php namespace Rikki\Heroeslounge\classes\Discord;

  
use Rikki\Heroeslounge\Models\Sloth as SlothModel;
use Rikki\Heroeslounge\classes\Discord\AuthCode;
use Db;

class Attendance
{
    public function FetchUsers()
    {
        $presentDiscordTags = [];

        $jsonData = null;
                                
        while (count($jsonData) >= 1000 || $jsonData == null) {
            $url = 'https://discordapp.com/api/guilds/200267155479068672/members';
            if (count($jsonData) == 1000) {
                $urlData = http_build_query(array("limit" => 1000, "after" => $jsonData[999]["user"]["id"]));
            } else {
                $urlData = http_build_query(array("limit" => 1000));
            }

            $auth_header = AuthCode::getCode();
            
            $headers = [
                "Content-Type:application/x-www-form-urlencoded",
                $auth_header,
                "User-Agent: HeroesLounge (http://heroeslounge.gg, 0.1)"
            ];

            $ch = curl_init($url . "?" . $urlData);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

            $output = curl_exec($ch);

            if (curl_errno($ch)) {
                return false;
            }

            $jsonData = json_decode($output, true);

            curl_close($ch);

                            
            foreach ($jsonData as $user) {
                if (isset($user["user"])) {
                    $presentDiscordTags[] = $user["user"]["username"] . "#" . $user["user"]["discriminator"];
                }
            }
        }

        return $presentDiscordTags;
    }

    public function CheckAttendance()
    {
        $discordTags = Db::table("rikki_heroeslounge_sloths")->where("is_captain", 1)->whereIn('team_id', Db::table('rikki_heroeslounge_team_division')->whereIn('div_id', [1,2,3,4,5])->lists('team_id'))->lists("discord_tag");
        $presentDiscordTags = $this->FetchUsers();

        $data = ($presentDiscordTags != false) ? array_udiff($discordTags, $presentDiscordTags, "strcasecmp") : [];

        $emails = Db::table("users")->whereIn("id", SlothModel::whereIn("discord_tag", $data)->lists("user_id"))->lists("email");

        return $emails;
    }

    public function IsOnServer($discordTag)
    {
        $presentDiscordTags = $this->FetchUsers();
        
        return ($presentDiscordTags != false) ? in_array($discordTag, $presentDiscordTags) : false;
    }
}
