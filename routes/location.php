<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Application Location Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->group(['prefix' => 'location', 'namespace' => 'Location', 'guard' => 'location'], function () use($router) {
    $router->group(['prefix' => 'get'], function () use($router) {
        $router->get('/country', 'LocationController@getCountry');
        $router->post('/province', 'LocationController@getProvince');
        $router->post('/city', 'LocationController@getCity');
    });
});
