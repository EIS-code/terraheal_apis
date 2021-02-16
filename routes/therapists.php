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

// $router->get('password/reset', ['as' => 'password.reset', 'uses' => 'Therapist\Auth\ForgotPasswordController@postReset']);

$router->group(['prefix' => 'therapist', 'namespace' => 'Therapist', 'guard' => 'therapist'], function () use($router) {
    $router->post('/signin', function (Request $request) {
        $controller = new \App\Http\Controllers\Therapist\TherapistController();

        return $controller->signIn(Therapist::IS_NOT_FREELANCER, $request);
    });

    $router->post('/signin/forgot', 'Auth\ForgotPasswordController@sendResetLinkEmail');

    $router->group(['prefix' => 'freelancer'], function () use($router) {
        $router->post('/signin', function (Request $request) {
            $controller = new \App\Http\Controllers\Therapist\TherapistController();

            return $controller->signIn(Therapist::IS_FREELANCER, $request);
        });

        $router->group(['prefix' => 'profile'], function () use($router) {
            $router->post('/update', function (Request $request) use($router) {
                $controller = new \App\Http\Controllers\Therapist\TherapistController();

                return $controller->updateProfile(Therapist::IS_FREELANCER, $request);
            });
        });
    });

    $router->group(['prefix' => 'calender'], function () use($router) {
        $router->post('/get', 'TherapistController@getCalender');
        $router->post('/booking/details', 'TherapistController@getGlobalResponse');
    });

    $router->group(['prefix' => 'booking'], function () use($router) {
        $router->post('/', 'TherapistController@getGlobalResponse');
        $router->post('/list/today', 'TherapistController@getTodayBooking');
        $router->post('/list/future', 'TherapistController@getFutureBooking');
        $router->post('/list/past', 'TherapistController@getPastBooking');
    });

    $router->group(['prefix' => 'profile'], function () use($router) {
        $router->post('/update', function (Request $request) {
            $controller = new \App\Http\Controllers\Therapist\TherapistController();

            return $controller->updateProfile(Therapist::IS_NOT_FREELANCER, $request);
        });
    });
});
