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
    'middleware' => 'jwt:api',
    'prefix' => 'submodules'
], function (Router $router) {
    Route::get('/', 'SubModulesController@index');
    Route::get('/tree', 'SubModulesController@tree');
});
