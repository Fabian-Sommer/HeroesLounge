<?php

Route::group(['prefix' => 'api/v1'], function () {
    Route::get('seasons/{id}/casterstatistics', 'Rikki\LoungeStatistics\Http\Season@casterstatistics');
});
