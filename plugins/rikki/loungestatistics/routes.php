<?php

Route::group(['prefix' => 'api/v1'], function () {
    Route::get('seasons/{id}/casterstatistics', 'Rikki\LoungeStatistics\Http\Season@casterstatistics');
    Route::get('divisions/{id}/herostatistics', 'Rikki\LoungeStatistics\Http\Division@herostatistics');
    Route::get('sloths/{sloth}/herostatistics', 'Rikki\LoungeStatistics\Http\Sloth@herostatistics');
    Route::get('sloths/{sloth}/season/{season}/herostatistics', 'Rikki\LoungeStatistics\Http\Sloth@seasonHerostatistics');
});
