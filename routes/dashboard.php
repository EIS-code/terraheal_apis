<?php


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


$router->group(['prefix' => 'dashboard', 'namespace' => 'Shops'], function () use($router) {

    $router->post('/getInfo', 'Dashboard\DashboardController@getDetails');
    $router->post('/getSalesInfo', 'Dashboard\DashboardController@salesInfo');
    $router->post('/getCustomerInfo', 'Dashboard\DashboardController@customerInfo');
});
