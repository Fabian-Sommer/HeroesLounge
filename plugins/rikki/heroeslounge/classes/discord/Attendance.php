<?php namespace Rikki\Heroeslounge\classes\Discord;

use Rikki\Heroeslounge\Models\Sloth as SlothModel;
use Rikki\Heroeslounge\classes\Discord\AuthCode;
use Db;

class Attendance
{
    public static function FetchUsers()
    {
      $presentUsers = [];
      $jsonData = null;

      while ($jsonData == null || count($jsonData) >= 1000) {
          $url = 'https://discordapp.com/api/guilds/200267155479068672/members';
          if ($jsonData != null && count($jsonData) == 1000) {
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

    public static function FetchUserBySearch($discordTag)
    {
      $username = $discordTag;
      if (str_contains($discordTag, '#')) {
        $username = explode("#", $discordTag)[0];
      }
      
      $url = 'https://discordapp.com/api/guilds/200267155479068672/members/search';
      $urlData = http_build_query(array("query" => $username, "limit" => 1000));
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

      curl_close($ch);
      $jsonData = json_decode($output, true);

      $matchingUsers = [];
      foreach ($jsonData as $user) {
        if (isset($user["user"])) {
            $matchingUsers[] = $user["user"];
        }
      }

      return $matchingUsers;
    }

    public static function GetDiscordUserId($discordTag)
    {
        $presentUsers = Attendance::FetchUserBySearch($discordTag);
        if (!$presentUsers) {
          return '';
        }

        $userId = '';

        foreach ($presentUsers as $user) {
          $userTag = $user["username"];
          if (in_array("discriminator", $user) && $user["discriminator"] != "0") {
            $userTag = $user["username"] . "#" . $user["discriminator"];
          }
          if ($userTag == $discordTag) {
            $userId = $user["id"];
            break;
          }
        }

        return $userId;
    }

    public static function CheckIndividualAttendance($discordId)
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
      curl_setopt($ch, CURLOPT_FAILONERROR, true);
      $output = curl_exec($ch);
      if (curl_errno($ch)) {
          return false;
      }
      curl_close($ch);

      $memberData = json_decode($output, true);
      return (isset($memberData)) ? true : false;
    }

    public static function CreateDiscordTagArray($users)
    {
      $presntDiscordTags = [];

      foreach ($users as $user) {
        $userTag = $user["username"];
        if (in_array("discriminator", $user) && $user["discriminator"] != "0") {
          $userTag = $user["username"] . "#" . $user["discriminator"];
        }
        $presentDiscordTags[] = $userTag;
      }

      return $presentDiscordTags;
    }

    public static function migrateDiscordTagsToIds()
    {
      $sloths = SlothModel::where('discord_id', '=', "")->get();
      $presentUsers = Attendance::FetchUsers();
      if (!$presentUsers) {
        return;
      }

      foreach ($presentUsers as $user) {
        $userTag = $user["username"];
        if (in_array("discriminator", $user) && $user["discriminator"] != "0") {
          $userTag = $user["username"] . "#" . $user["discriminator"];
        }
        foreach ($sloths as $sloth) {
          if ($userTag == $sloth->discord_tag) {
            $sloth->discord_id = $user["id"];
            $sloth->save();
            break;
          }
        }
      }
    }

    public static function migrateIdsToDiscordTags()
    {
      $sloths = SlothModel::where('discord_id', '!=', "")->get();
      $presentUsers = Attendance::FetchUsers();
      if (!$presentUsers) {
        return;
      }

      foreach ($presentUsers as $user) {
        $userTag = $user["username"];
        if (in_array("discriminator", $user) && $user["discriminator"] != "0") {
          $userTag = $user["username"] . "#" . $user["discriminator"];
        }
        foreach ($sloths as $sloth) {
          if ($sloth->discord_id == $user["id"]) {
            $sloth->discord_tag = $userTag;
            $sloth->save();
            break;
          }
        }
      }
    }

    public static function getDiscordTag($discordId)
    {
      $url = 'https://discordapp.com/api/users/' . $discordId;

      $auth_header = AuthCode::getCode();
      $headers = [
        "Content-Type:application/x-www-form-urlencoded",
        $auth_header,
        "User-Agent: HeroesLounge (http://heroeslounge.gg, 0.1)"
      ];

      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_FAILONERROR, true);
      $output = curl_exec($ch);
      if (curl_errno($ch)) {
        return "";
      }
      curl_close($ch);

      $user = json_decode($output, true);
      
      $userTag = $user["username"];
      if (in_array("discriminator", $user) && $user["discriminator"] != "0") {
        $userTag = $user["username"] . "#" . $user["discriminator"];
      }
      return $userTag;
    }
}
