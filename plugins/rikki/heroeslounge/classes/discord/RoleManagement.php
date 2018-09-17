<?php namespace Rikki\Heroeslounge\classes\Discord;

use Rikki\Heroeslounge\classes\Discord\AuthCode;

class RoleManagement
{
  /*
    Use a PUT request to assign a role to the user.
    Use a DELETE request to remove a role from the user.
  */
  public static function UpdateUserRole($request, $discordId, $role)
  {
    $roleIDs = [
      "Captains"      => "201017727958253568",
      "Casters"       => "201413196316278784",
      "FreeAgent"     => "476465358023163918"
    ];

    $url = 'https://discordapp.com/api/guilds/200267155479068672/members/';

    $auth_header = AuthCode::getCode();
    $headers = [
      "Content-Type:application/x-www-form-urlencoded",
      $auth_header,
      "User-Agent: HeroesLounge (http://heroeslounge.gg, 0.1)"
    ];


    $ch = curl_init($url . $discordId . '/roles/' . $roleIDs[$role]);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request);

    $output = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);
    return ($httpcode == 204) ? true : false;
  }
}
