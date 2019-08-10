<?php

Route::group(['prefix' => 'api/v1'], function () {
    Route::get('seasons/{id}/casterstatistics', 'Rikki\LoungeStatistics\Http\Season@casterstatistics');
    Route::get('divisions/{id}/herostatistics', 'Rikki\LoungeStatistics\Http\Division@herostatistics');
});
