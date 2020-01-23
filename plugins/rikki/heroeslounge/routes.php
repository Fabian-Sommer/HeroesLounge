<?php

Route::group(['prefix' => 'api/v2', 'middleware' => ['Rikki\Heroeslounge\Classes\ApiMiddleware']], function () {
    Route::resource('seasons', 'Rikki\Heroeslounge\Http\Season');
    Route::get('seasonsAll','Rikki\Heroeslounge\Http\Season@indexAll');
    Route::get('seasons/{id}/divisions', 'Rikki\Heroeslounge\Http\Season@divisions');
    Route::get('seasons/{id}/playoffs', 'Rikki\Heroeslounge\Http\Season@playoffs');
    Route::get('seasons/{id}/teams', 'Rikki\Heroeslounge\Http\Season@teams');

    Route::resource('playoffs', 'Rikki\Heroeslounge\Http\Playoff');
    Route::get('playoffsAll','Rikki\Heroeslounge\Http\Playoff@indexAll');
    Route::get('playoffs/{id}/divisions', 'Rikki\Heroeslounge\Http\Playoff@divisions');
    Route::get('playoffs/{id}/matches', 'Rikki\Heroeslounge\Http\Playoff@matches');

    Route::resource('divisions', 'Rikki\Heroeslounge\Http\Division');
    Route::get('divisionsAll','Rikki\Heroeslounge\Http\Division@indexAll');
    Route::get('divisions/{id}/matches', 'Rikki\Heroeslounge\Http\Division@matches');
    Route::get('divisions/{id}/teams', 'Rikki\Heroeslounge\Http\Division@teams');
    
    Route::resource('teams','Rikki\Heroeslounge\Http\Team');
    Route::get('teamsAll','Rikki\Heroeslounge\Http\Team@indexAll');
    Route::get('teams/{team}/matches', 'Rikki\Heroeslounge\Http\Team@matches');
    Route::get('teams/{team}/sloths', 'Rikki\Heroeslounge\Http\Team@sloths');
    Route::get('teams/{team}/sloths/{sloth}', 'Rikki\Heroeslounge\Http\Team@sloth');
    Route::get('teams/{team}/sloths/{sloth}/timelines', 'Rikki\Heroeslounge\Http\Team@slothTimelines');
    Route::get('teams/{team}/timelines', 'Rikki\Heroeslounge\Http\Team@timelines');

    Route::resource('sloths','Rikki\Heroeslounge\Http\Sloth');
    Route::get('slothsAll','Rikki\Heroeslounge\Http\Sloth@indexAll');
    Route::get('slothDiscordId/{discord_id}', 'Rikki\Heroeslounge\Http\Sloth@byDiscordId');

    Route::get('matchesAll','Rikki\Heroeslounge\Http\Match@indexAll');
    Route::get('matches/{id}/caster','Rikki\Heroeslounge\Http\Match@caster');
    Route::get('matches/{id}/channels', 'Rikki\Heroeslounge\Http\Match@channels');
    Route::get('matches/{id}/games','Rikki\Heroeslounge\Http\Match@games');
    Route::get('matches/{id}/replays','Rikki\Heroeslounge\Http\Match@replays');
    Route::get('matches/today/{tz1?}/{tz2?}','Rikki\Heroeslounge\Http\Match@getMatchesForToday');
    Route::get('matches/forDate/{date}/{tz1?}/{tz2?}','Rikki\Heroeslounge\Http\Match@getMatchesForDate');
    Route::get('matches/withApprovedCastBetween/{startdate}/{enddate}','Rikki\Heroeslounge\Http\Match@withApprovedCastBetween');
    
    Route::resource('games', 'Rikki\Heroeslounge\Http\Game');
    Route::get('gamesAll','Rikki\Heroeslounge\Http\Game@indexAll');
    Route::get('gamesAllWithPlayers','Rikki\Heroeslounge\Http\Game@indexAllWithPlayers');
    Route::get('games/{id}/replay', 'Rikki\Heroeslounge\Http\Game@replay');

    Route::resource('bans','Rikki\Heroeslounge\Http\Bans');
    Route::get('bansAll','Rikki\Heroeslounge\Http\Bans@indexAll');
    
    Route::resource('heroes','Rikki\Heroeslounge\Http\Heroes');
    Route::get('heroesAll','Rikki\Heroeslounge\Http\Heroes@indexAll');

    Route::resource('talents','Rikki\Heroeslounge\Http\Talents');
    Route::get('talentsAll','Rikki\Heroeslounge\Http\Talents@indexAll');

    Route::resource('roles', 'Rikki\Heroeslounge\Http\Slothrole');
    Route::get('rolesAll', 'Rikki\Heroeslounge\Http\Slothrole@indexAll');

    Route::resource('applications', 'Rikki\Heroeslounge\Http\Applications');
    Route::resource('channel', 'Rikki\Heroeslounge\Http\Twitchchannel');
    Route::resource('timeline', 'Rikki\Heroeslounge\Http\Timeline');
    Route::get('maps', 'Rikki\Heroeslounge\Http\Map@getEnabled');
    Route::get('logos', 'Rikki\Heroeslounge\Http\Team@logos');
});

Route::resource('api/v1/matches', 'Rikki\Heroeslounge\Http\Match');
Route::get('api/v1/teams/{team}/logo', 'Rikki\Heroeslounge\Http\Team@logo');
Route::get('api/v1/matches/{id}/teams','Rikki\Heroeslounge\Http\Match@teams');
