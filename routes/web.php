<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

// Authentication Routes...
/*Route::get('login', [
  'as' => 'login',
  'uses' => 'Auth\LoginController@showLoginForm'
]);*/

/*Route::post('login', [
  'as' => '',
  'uses' => 'Auth\LoginController@login'
]);*/

/*Route::post('logout', [
  'as' => 'logout',
  'uses' => 'Auth\LoginController@logout'
]);*/

// Password Reset Routes...
$router->post('password/email', [
  'as' => 'password.email',
  'uses' => 'Therapist\Auth\ForgotPasswordController@sendResetLinkEmail'
]);

$router->get('password/reset', [
  'as' => 'password.request',
  'uses' => 'Therapist\Auth\ForgotPasswordController@showLinkRequestForm'
]);

/*Route::post('password/reset', [
  'as' => 'password.update',
  'uses' => 'App\Traits\ResetsPasswords@reset'
]);*/

$router->get('password/reset/{token}', [
  'as' => 'password.reset',
  'uses' => 'App\Traits\ResetsPasswords@showResetForm'
]);

// Registration Routes...
/*$router->get('register', [
  'as' => 'register',
  'uses' => 'Auth\RegisterController@showRegistrationForm'
]);*/

/*$router->post('register', [
  'as' => '',
  'uses' => 'Auth\RegisterController@register'
]);*/

$router->group(['middleware' => ['auth']], function () use($router) {
    include("users.php");
    include("therapists.php");
    include("location.php");
    include("massages.php");
    include("shops.php");
    include("news.php");
    include("superAdmin.php");
});
