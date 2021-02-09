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

$router->group(['prefix' => 'news', 'namespace' => 'News'], function () use($router) {
    $router->get('/get', 'NewsController@get');

    $router->post('/read', 'NewsController@setRead');
});
