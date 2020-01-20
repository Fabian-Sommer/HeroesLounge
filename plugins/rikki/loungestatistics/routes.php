<?php

Route::group(['prefix' => 'api/v2/{key}', 'middleware' => ['Rikki\Heroeslounge\Classes\ApiMiddleware']], function () {
    Route::get('seasons/{id}/casterstatistics', 'Rikki\LoungeStatistics\Http\Season@casterstatistics');
    Route::get('divisions/{id}/herostatistics', 'Rikki\LoungeStatistics\Http\Division@herostatistics');
    Route::get('sloths/{sloth}/herostatistics', 'Rikki\LoungeStatistics\Http\Sloth@herostatistics');
    Route::get('sloths/{sloth}/season/{season}/herostatistics', 'Rikki\LoungeStatistics\Http\Sloth@seasonHerostatistics');
    Route::get('teams/{team}/herostatistics', 'Rikki\LoungeStatistics\Http\Team@herostatistics');
    Route::get('teams/{team}/season/{season}/herostatistics', 'Rikki\LoungeStatistics\Http\Team@seasonHerostatistics');
    Route::get('teams/{team}/mapstatistics', 'Rikki\LoungeStatistics\Http\Team@mapstatistics');
    Route::get('teams/{team}/season/{season}/mapstatistics', 'Rikki\LoungeStatistics\Http\Team@seasonMapstatistics');
});
