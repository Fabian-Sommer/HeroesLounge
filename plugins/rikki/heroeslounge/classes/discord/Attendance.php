<?php namespace Rikki\Heroeslounge\classes\Discord;

use Rikki\Heroeslounge\Models\Sloth as SlothModel;
use Rikki\Heroeslounge\classes\Discord\AuthCode;
use Db;

class Attendance
{
    public function FetchUsers()
    {
      $presentUsers = [];
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
                  $presentUsers[] = $user["user"];
              }
          }
      }

      return $presentUsers;
    }

    public function CheckAttendance()
    {
        $discordTags = Db::table("rikki_heroeslounge_sloths")->where("is_captain", 1)->whereIn('team_id', Db::table('rikki_heroeslounge_team_division')->whereIn('div_id', [1,2,3,4,5])->lists('team_id'))->lists("discord_tag");
        $presentUsers = $this->FetchUsers();
        $presentDiscordTags = $this->CreateDiscordTagArray($presentUsers);

        $data = ($presentDiscordTags != false) ? array_udiff($discordTags, $presentDiscordTags, "strcasecmp") : [];

        $emails = Db::table("users")->whereIn("id", SlothModel::whereIn("discord_tag", $data)->lists("user_id"))->lists("email");

        return $emails;
    }

    public function GetDiscordUserId($discordTag)
    {
        $presentUsers = $this->FetchUsers();
        $userId = '';

        if ($presentUsers) {
          foreach ($presentUsers as $user) {
            $userTag = $user["username"] . "#" . $user["discriminator"];
            if ($userTag == $discordTag) {
              $userId = $user["id"];
              break;
            }
          }
        }
        return $userId
    }

    public function CheckIndividualAttendance($discordId)
    {
      $url = 'https://discordapp.com/api/guilds/200267155479068672/members/' . $discordId;

      $auth_header = AuthCode::getCode();
      $headers = [
        "Content-Type:application/x-www-form-urlencoded",
        $auth_header,
        "User-Agent: HeroesLounge (http://heroeslounge.gg, 0.1)"
      ];

      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

      $output = curl_exec($ch);

      if (curl_errno($ch)) {
          return false;
      }

      $memberData = json_decode($output, true);

      curl_close($ch);

      return (isset($memberData)) ? true : false;

    }

    public function CreateDiscordTagArray($users)
    {
      $presntDiscordTags = [];

      foreach ($users as $user) {
        $presentDiscordTags[] = $user["username"] . "#" . $user["discriminator"];
      }

      return $presentDiscordTags;
    }

    public function migrationDiscordTagsToIds ()
    {
      $sloths = SlothModel::all();
      $presentUsers = $this->FetchUsers();

      if ($presentUsers) {
        foreach ($presentUsers as $user) {
          $userTag = $user["username"] . "#" . $user["discriminator"];
          foreach ($sloths as $sloth) {
            if ($userTag == $sloth->discord_tag) {
              $sloth->discord_id = $user["id"];
              $sloth->save();
              break;
            }
          }
        }
      }

    }
}
