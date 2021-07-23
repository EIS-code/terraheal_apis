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

$router->group(['prefix' => 'service', 'namespace' => 'Service', 'guard' => 'service'], function () use($router) {
    $router->post('get', 'ServiceController@get');
});
