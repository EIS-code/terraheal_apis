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

config(['auth.defaults.guard' => 'therapists']);
config(['auth.defaults.passwords' => 'therapist']);

$router->group(['prefix' => 'therapist', 'namespace' => 'Therapist', 'guard' => 'therapist'], function () use($router) {
    $router->post('/signin', function(Request $request) {
        $controller = new \App\Http\Controllers\Therapist\TherapistController();

        return $controller->signIn(Therapist::IS_NOT_FREELANCER, $request);
    });

    $router->post('/signin/forgot', 'Auth\ForgotPasswordController@generateResetToken');

    $router->group(['prefix' => 'freelancer'], function () use($router) {
        $router->post('/signin', function(Request $request) {
            $controller = new \App\Http\Controllers\Therapist\TherapistController();

            return $controller->signIn(Therapist::IS_FREELANCER, $request);
        });
    });
});
