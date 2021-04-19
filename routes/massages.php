<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Application Massage related Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->group(['prefix' => 'massages', 'namespace' => 'Massages', 'guard' => 'massages'], function () use($router) {
    $router->post('get', 'MassageController@get');

    $router->group(['prefix' => 'preference', 'namespace' => 'Preference'], function () use($router) {
        $router->get('/', 'MassageController@getPreference');
        $router->post('save', 'MassageController@createPreference');
    });

    $router->group(['prefix' => 'center'], function () use($router) {
        $router->post('get', 'MassageController@getMassageCenters');
    });

    $router->group(['prefix' => 'session'], function () use($router) {
        $router->post('get', 'MassageController@getMassageSessions');
    });
});
