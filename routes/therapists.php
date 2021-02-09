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

<<<<<<< HEAD
=======
// $router->get('password/reset', ['as' => 'password.reset', 'uses' => 'Therapist\Auth\ForgotPasswordController@postReset']);

>>>>>>> a1af10094a4c25489d0fb294eb5811e66c43dd85
$router->group(['prefix' => 'therapist', 'namespace' => 'Therapist', 'guard' => 'therapist'], function () use($router) {
    $router->post('/signin', function(Request $request) {
        $controller = new \App\Http\Controllers\Therapist\TherapistController();

        return $controller->signIn(Therapist::IS_NOT_FREELANCER, $request);
    });

<<<<<<< HEAD
    $router->post('/signin/forgot', 'Auth\ForgotPasswordController@generateResetToken');
=======
    $router->post('/signin/forgot', 'Auth\ForgotPasswordController@sendResetLinkEmail');
>>>>>>> a1af10094a4c25489d0fb294eb5811e66c43dd85

    $router->group(['prefix' => 'freelancer'], function () use($router) {
        $router->post('/signin', function(Request $request) {
            $controller = new \App\Http\Controllers\Therapist\TherapistController();

            return $controller->signIn(Therapist::IS_FREELANCER, $request);
        });
    });
<<<<<<< HEAD
=======

    $router->group(['prefix' => 'booking'], function () use($router) {
        $router->post('/', 'TherapistController@getGlobalResponse');
        $router->post('/list/today', 'TherapistController@getTodayBooking');
        $router->post('/list/future', 'TherapistController@getFutureBooking');
        $router->post('/list/past', 'TherapistController@getPastBooking');
    });
>>>>>>> a1af10094a4c25489d0fb294eb5811e66c43dd85
});
