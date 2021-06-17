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
        $router->post('/all', 'TherapistController@getBookings');
        $router->post('/list/today', 'TherapistController@getTodayBooking');
        $router->post('/list/future', 'TherapistController@getFutureBooking');
        $router->post('/list/past', 'TherapistController@getPastBooking');
        $router->post('/list/pending', 'TherapistController@getPendingBooking');
        $router->post('/list/upcoming', 'TherapistController@getUpcomingBooking');
        $router->post('/list/pasts', 'TherapistController@getPastBookings');
        $router->group(['prefix' => 'massage'], function () use($router) {
            $router->post('start', 'TherapistController@startMassageTime');
            $router->post('end', 'TherapistController@endMassageTime');
        });
    });

    $router->group(['prefix' => 'profile'], function () use($router) {
        $router->post('/get', function (Request $request) {
            $controller = new \App\Http\Controllers\Therapist\TherapistController();

            return $controller->getGlobalResponse(Therapist::IS_NOT_FREELANCER, $request);
        });

        $router->post('/update', function (Request $request) {
            $controller = new \App\Http\Controllers\Therapist\TherapistController();

            return $controller->updateProfile(Therapist::IS_NOT_FREELANCER, $request);
        });

        $router->group(['prefix' => 'document'], function () use($router) {
            $router->post('remove', 'TherapistController@removeDocument');
        });
    });
    
    $router->group(['prefix' => 'shifts'], function () use($router) {
        $router->post('/get', 'TherapistController@getTherapistShifts');
    });

    $router->group(['prefix' => 'my'], function () use($router) {
        $router->group(['prefix' => 'working'], function () use($router) { 
            $router->post('/schedule', 'TherapistController@myWorkingSchedules');
        });

        $router->group(['prefix' => 'availability'], function () use($router) {
            $router->post('/get', 'TherapistController@myAvailabilities');            
            $router->post('/free/spots', 'TherapistController@myFreeSpots');
            $router->post('/add/free/spots', 'TherapistController@addFreeSlots');
            $router->post('/absent/store', 'TherapistController@absent');
        });
        
        $router->group(['prefix' => 'ratings'], function () use($router) {
            $router->get('/', 'TherapistController@myRatings');
        });

        $router->group(['prefix' => 'collaboration'], function () use($router) {
            $router->post('/quit', 'TherapistController@quitCollaboration');
            $router->post('/suspend', 'TherapistController@suspendCollaboration');
        });

        $router->group(['prefix' => 'exchange'], function () use($router) {
            $router->post('/', 'TherapistController@exchangeWithOthers');
            
            $router->group(['prefix' => 'shifts'], function () use($router) {
                $router->post('/list', 'TherapistController@getList');
                $router->post('/approve', 'TherapistController@approveShift');
                $router->post('/reject', 'TherapistController@rejectShift');
            });
        
        });

        $router->group(['prefix' => 'missing'], function () use($router) {
            $router->post('/days', 'TherapistController@myMissingDays');
        });

        $router->group(['prefix' => 'break'], function () use($router) {
            $router->post('/', 'TherapistController@takeBreaks');
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

    $router->group(['prefix' => 'verify'], function () use($router) {
        $router->post('/mobile', 'TherapistController@verifyMobile');
        $router->post('/email', 'TherapistController@verifyEmail');
    });
    
    $router->group(['prefix' => 'compare'], function () use($router) {
        $router->post('/otp/email', 'TherapistController@compareOtpEmail');
        $router->post('/otp/mobile', 'TherapistController@compareOtpSms');
    });
    
    $router->group(['prefix' => 'news'], function () use($router) {
        $router->post('/read', 'TherapistController@readNews');
    });
    
    $router->post('get', 'TherapistController@getOthers');
    $router->post('/getServices', 'TherapistController@getAllServices');
    $router->post('/getTherapists', 'TherapistController@getTherapists');
    $router->get('/getLanguages', 'TherapistController@getLanguages');
    $router->get('/getCountries', 'TherapistController@getCountries');
    $router->post('/getCities', 'TherapistController@getCities');
    $router->post('/searchClients', 'TherapistController@searchClients');
    $router->get('/complaintsSuggestion', 'TherapistController@getComplaintsSuggestion');
    $router->get('/getSessionTypes', 'TherapistController@getSessionTypes');    
});
