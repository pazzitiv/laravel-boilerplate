<?php

use Illuminate\Http\Request;
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
    'middleware' => 'guest:api',
    'prefix' => 'auth'
], function ($router) {

    Route::get('login', 'AuthController@login');
    Route::get('refresh', 'AuthController@refresh');
});

Route::group([
    'middleware' => 'jwt:api',
    'prefix' => 'auth'
], function ($router) {
    Route::get('logout', 'AuthController@logout');
    Route::get('me', 'AuthController@me');

});
