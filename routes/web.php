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

<<<<<<< HEAD
=======
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
Route::post('password/email', [
  'as' => 'password.email',
  'uses' => 'Therapist\Auth\ForgotPasswordController@sendResetLinkEmail'
]);

Route::get('password/reset', [
  'as' => 'password.request',
  'uses' => 'Therapist\Auth\ForgotPasswordController@showLinkRequestForm'
]);

/*Route::post('password/reset', [
  'as' => 'password.update',
  'uses' => 'App\Traits\ResetsPasswords@reset'
]);*/

Route::get('password/reset/{token}', [
  'as' => 'password.reset',
  'uses' => 'App\Traits\ResetsPasswords@showResetForm'
]);

// Registration Routes...
/*Route::get('register', [
  'as' => 'register',
  'uses' => 'Auth\RegisterController@showRegistrationForm'
]);*/

/*Route::post('register', [
  'as' => '',
  'uses' => 'Auth\RegisterController@register'
]);*/

>>>>>>> a1af10094a4c25489d0fb294eb5811e66c43dd85
include("therapists.php");
