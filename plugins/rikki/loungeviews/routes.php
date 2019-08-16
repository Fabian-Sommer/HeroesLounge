<?php

Route::group(['prefix' => 'api/v1'], function () {
    Route::get('divisions/{id}/standings', 'Rikki\LoungeViews\Http\Division@standings');
});