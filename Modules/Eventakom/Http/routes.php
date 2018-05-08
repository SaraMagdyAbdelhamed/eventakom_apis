<?php

Route::group(['middleware' => 'web', 'prefix' => 'eventakom', 'namespace' => 'Modules\Eventakom\Http\Controllers'], function()
{
    Route::get('/', 'EventakomController@index');
});
