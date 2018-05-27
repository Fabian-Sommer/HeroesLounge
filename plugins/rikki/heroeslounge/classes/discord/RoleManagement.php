<?php namespace Rikki\Heroeslounge\classes\Discord;
  

// Awaiting OAUTH implementation before it can be used!
class roleManagement
{
  $roleIDs = [
    "Lounge Master"     => "200988760027037698",
    "Captains"          => "201017727958253568",
    "Casters"           => "201413196316278784",
    "Moderators"        => "204616285605068800",
    "Bots"              => "210315208361508865",
    "Organisation"      => "217956384124174337",
    "VIP"               => "225240536712216577",
    "BetterTTV"         => "225287941914624000",
    "CoCasters"         => "235058412960874498",
    "FreeAgent"         => "257186837964128256",
    "editor"            => "277019160997789698",
    "playoff_org"       => "278871948250054657",
    "DevGods"           => "278972557057851392",
    "WebDevs"           => "279386891391074304",
    "TestStaff"         => "283575440004874240",
    "gamewisp"          => "287210061753745408",
    "Patreon"           => "290499804868640768",
    "temp_playoff"      => "318862983830831105",
    "BlogEditor"        => "322770036362182656",
    "Muted"             => "337860519019151361",
    "mYi"               => "341227790386987019",
    "Staff"             => "342012583928397837",
    "Twitch Moderator"  => "348468038258917376",
    "Twitch Subscriber" => "353969241453494292",
    "Patreon VIP"       => "353978221919993856",
    "Stream Elements"   => "355445823644893194"
  ];

  $url = 'https://discordapp.com/api/guilds/200267155479068672/members/';
  $headers = [
    "Content-Type:application/x-www-form-urlencoded",
    "Authorization: Bot MjMxNjQzOTQyMTc1NjM3NTA2.CtkrjA.oZNv_GVZGJmSO8xZ3j9bU5KSqlI", // Probably needs changing to one with permissions.
    "User-Agent: HeroesLounge (http://heroeslounge.gg, 0.1)"
  ];

  public function updateUserRole($request, $userID, $role)
  {
    $ch = curl_init($url . $memberID . '/roles/' . $roleIDs[$role]);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request);

    $output = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);
    // 204 success response.
    return ($httpcode == 204) ? true : false;
  }
}
