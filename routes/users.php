<?php

use Illuminate\Http\Request;
use App\Therapist;

/*
|--------------------------------------------------------------------------
| Application Therapist Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->group(['prefix' => 'user', 'namespace' => 'User', 'guard' => 'user'], function () use($router) {
    $router->post('/signin', 'UserController@signIn');

    $router->group(['prefix' => 'signup'], function () use($router) {
        $router->post('/', 'UserController@signUp');
    });

    $router->group(['prefix' => 'profile'], function () use($router) {
        $router->post('update', 'UserController@updateProfile');
    });

    $router->group(['prefix' => 'setting'], function () use($router) {
        $router->post('/logout', 'UserController@logout');
    });

    $router->group(['prefix' => 'booking'], function () use($router) {
        $router->post('/create', 'UserController@bookingCreate');
    });
});
