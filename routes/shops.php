<?php

use Illuminate\Http\Request;
use App\Shop;

/*
  |--------------------------------------------------------------------------
  | Application News Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register all of the routes for an application.
  | It is a breeze. Simply tell Lumen the URIs it should respond to
  | and give it the Closure to call when that URI is requested.
  |
 */

config(['auth.defaults.guard' => 'shop']);
config(['auth.defaults.passwords' => 'shop']);

$router->group(['prefix' => 'shops', 'namespace' => 'Shops'], function () use($router) {

    $router->post('/signin', 'ShopsController@signIn');
    $router->post('/getTherapists', 'ShopsController@getAllTherapists');
    $router->post('/getServices', 'ShopsController@getAllServices');
    $router->post('/getClients', 'ShopsController@getAllClients');
    $router->post('/getPreferences', 'ShopsController@getPreferences');
});

$router->group(['prefix' => 'shops', 'namespace' => 'Shops', 'guard' => 'shop'], function () use($router) {

    $router->post('/forgot', 'Auth\ForgotPasswordController@sendResetLinkEmail');
});