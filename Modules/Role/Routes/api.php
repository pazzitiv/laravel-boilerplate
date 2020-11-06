<?php

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => 'jwt:api',
    'prefix' => 'roles',
], function (Router $router) {
    Route::get('/{id}', 'RoleController@show');
    Route::delete('/{id}', 'RoleController@destroy');
    Route::patch('/{id}', 'RoleController@update');
    Route::post('/', 'RoleController@store');
    Route::get('/', 'RoleController@index');
});
