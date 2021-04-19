<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Application Users Routes
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
        $router->post('/update/password', 'UserController@updatePassword');
        $router->post('/get', 'UserController@getUserSettings');
        $router->post('/save', 'UserController@saveUserSettings');
    });

    $router->group(['prefix' => 'verify'], function () use($router) {
        $router->post('/mobile', 'UserController@verifyMobile');
        $router->post('/email', 'UserController@verifyEmail');
    });

    $router->group(['prefix' => 'compare'], function () use($router) {
        $router->post('/otp/email', 'UserController@compareOtpEmail');
        $router->post('/otp/mobile', 'UserController@compareOtpSms');
    });

    $router->group(['prefix' => 'booking'], function () use($router) {
        $router->post('/create', 'UserController@bookingCreate');
    });

    $router->get('/get', 'UserController@getDetails');
});
