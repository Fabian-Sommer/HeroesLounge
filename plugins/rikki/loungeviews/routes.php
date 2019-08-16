<?php

Route::group(['prefix' => 'api/v1'], function () {
    Route::get('divisions/{division}/standings', 'Rikki\LoungeViews\Http\Division@standings');
    Route::get('divisions/{division}/standings/{team}', 'Rikki\LoungeViews\Http\Division@standing');
});