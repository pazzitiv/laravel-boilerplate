<?php

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => 'jwt:api',
    'prefix' => 'groups',
], function (Router $router) {
    Route::get('/{id}', 'GroupController@show');
    Route::delete('/{id}', 'GroupController@destroy');
    Route::patch('/{id}', 'GroupController@update');
    Route::post('/', 'GroupController@store');
    Route::put('/{id}', 'GroupController@setusers');
    Route::get('/', 'GroupController@index');
});
