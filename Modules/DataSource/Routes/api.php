<?php

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => 'jwt:api',
    'prefix' => 'datasource',
], function (Router $router) {
    Route::get('/{id}', 'DataSourceController@show');
    Route::delete('/{id}', 'DataSourceController@destroy');
    Route::patch('/{id}', 'DataSourceController@update');
    Route::post('/', 'DataSourceController@store');
    Route::get('/', 'DataSourceController@index');
});
