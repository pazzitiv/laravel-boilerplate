<?php

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group([
    'middleware' => 'api',
    'prefix' => 'users',
], function (Router $router) {
    Route::get('/{id}', 'UserController@show');
    Route::delete('/{id}', 'UserController@destroy');
    Route::patch('/{id}', 'UserController@update');
    Route::post('/', 'UserController@store');
    Route::get('/', 'UserController@index');
});
