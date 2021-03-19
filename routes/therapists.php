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
            $router->get('/get', function (Request $request) use($router) {
                $controller = new \App\Http\Controllers\Therapist\TherapistController();

                return $controller->getGlobalResponse(Therapist::IS_FREELANCER, $request);
            });

            $router->post('/update', function (Request $request) use($router) {
                $controller = new \App\Http\Controllers\Therapist\TherapistController();

                return $controller->updateProfile(Therapist::IS_FREELANCER, $request);
            });
        });
    });

    $router->group(['prefix' => 'calender'], function () use($router) {
        $router->post('/get', 'TherapistController@getCalender');
        $router->post('/booking/details', 'TherapistController@getBookingDetails');
    });

    $router->group(['prefix' => 'booking'], function () use($router) {
        $router->post('/', 'TherapistController@getBookingDetails');
        $router->post('/list/today', 'TherapistController@getTodayBooking');
        $router->post('/list/future', 'TherapistController@getFutureBooking');
        $router->post('/list/past', 'TherapistController@getPastBooking');
        $router->group(['prefix' => 'massage'], function () use($router) {
            $router->post('start', 'TherapistController@startMassageTime');
            $router->post('end', 'TherapistController@endMassageTime');
        });
    });

    $router->group(['prefix' => 'profile'], function () use($router) {
        $router->get('/get', function (Request $request) {
            $controller = new \App\Http\Controllers\Therapist\TherapistController();

            return $controller->getGlobalResponse(Therapist::IS_NOT_FREELANCER, $request);
        });

        $router->post('/update', function (Request $request) {
            $controller = new \App\Http\Controllers\Therapist\TherapistController();

            return $controller->updateProfile(Therapist::IS_NOT_FREELANCER, $request);
        });
    });

    $router->group(['prefix' => 'my'], function () use($router) {
        $router->group(['prefix' => 'working'], function () use($router) {
            $router->post('/schedule', 'TherapistController@myWorkingSchedules');
        });

        $router->group(['prefix' => 'availability'], function () use($router) {
            $router->post('/get', 'TherapistController@myAvailabilities');
            $router->post('/free/spots', 'TherapistController@myFreeSpots');
            $router->post('/absent/store', 'TherapistController@absent');
        });

        $router->group(['prefix' => 'ratings'], function () use($router) {
            $router->get('/', 'TherapistController@myRatings');
        });

        $router->group(['prefix' => 'collaboration'], function () use($router) {
            $router->post('/quit', 'TherapistController@quitCollaboration');
            $router->post('/suspend', 'TherapistController@suspendCollaboration');
        });
    });

    $router->group(['prefix' => 'rating'], function () use($router) {
        $router->group(['prefix' => 'user'], function () use($router) {
            $router->post('/save', 'TherapistController@rateUser');
        });
    });

    $router->post('/suggestion', 'TherapistController@suggestion');
    $router->post('/complaint', 'TherapistController@complaint');

    $router->group(['prefix' => 'service'], function () use($router) {
        $router->post('/', 'TherapistController@getAllServices');
    });

    $router->post('get', 'TherapistController@getOthers');
    $router->post('/getServices', 'TherapistController@getAllServices');
    $router->post('/getTherapists', 'TherapistController@getTherapists');
});
