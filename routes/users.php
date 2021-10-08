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

    $router->post('signin/forgot', 'UserController@forgotPassword');
    $router->post('reset/password', 'UserController@resetPassword');
    $router->post('verify/otp', 'UserController@verifyOtp');
    
    $router->group(['prefix' => 'signup'], function () use($router) {
        $router->post('/', 'UserController@signUp');
    });

    $router->group(['prefix' => 'profile'], function () use($router) {
        $router->post('update', 'UserController@updateProfile');
        $router->group(['prefix' => 'document'], function () use($router) {
            $router->post('/upload', 'UserController@updateDocument');
            $router->post('/remove', 'UserController@removeDocument');
        });
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
        $router->post('/therapists', 'UserController@getBookingTherapists');
        $router->post('/places', 'UserController@getBookingPlaces');
        $router->post('/list/past', 'UserController@getPastBooking');
        $router->post('/list/future', 'UserController@getFutureBooking');
        $router->post('/list/pending', 'UserController@getPendingBooking');
        $router->post('/events/corporate/request/add', 'UserController@addEventsCorporateRequest');
        $router->post('/pending/update', 'UserController@updatePendingBooking');
        $router->post('/pending/delete', 'UserController@deletePendingBooking');
    });

    $router->post('/get', 'UserController@getDetails');

    $router->group(['prefix' => 'address'], function () use($router) {
        $router->post('/get', 'UserController@getAddress');
        $router->post('/save', 'UserController@createAddress');
        $router->post('/update', 'UserController@updateAddress');
        $router->post('/remove', 'UserController@removeAddress');
    });

    $router->group(['prefix' => 'people'], function () use($router) {
        $router->post('/get', 'UserController@getPeople');
        $router->post('/save', 'UserController@createPeople');
        $router->post('/update', 'UserController@updatePeople');
        $router->post('/remove', 'UserController@removePeople');
    });

    $router->group(['prefix' => 'therapist'], function () use($router) {
        $router->group(['prefix' => 'review'], function () use($router) {
            $router->post('/save', 'UserController@setTherapistReviews');
        });
    });

    $router->group(['prefix' => 'menu'], function () use($router) {
        $router->get('/get', 'UserController@getMenus');

        $router->group(['prefix' => 'item'], function () use($router) {
            $router->post('/get', 'UserController@getMenuItem');
        });
    });

    $router->group(['prefix' => 'gift'], function () use($router) {
        $router->group(['prefix' => 'voucher'], function () use($router) {
            $router->post('/get', 'UserController@getGiftVouchers');

            $router->get('/info', 'UserController@getGiftVoucherInfos');

            $router->group(['prefix' => 'design'], function () use($router) {
                $router->get('/get', 'UserController@getGiftVoucherDesigns');
            });

            $router->post('/save', 'UserController@saveGiftVouchers');
        });
    });

    $router->group(['prefix' => 'faq'], function () use($router) {
        $router->get('/get', 'UserController@getFaqs');
    });

    $router->group(['prefix' => 'pack'], function () use($router) {
        $router->post('/get', 'UserController@getPacks');
        $router->post('/list', 'UserController@getUserPacks');

        $router->post('/services/get', 'UserController@getPackServices');

        $router->group(['prefix' => 'order'], function () use($router) {
            $router->post('/save', 'UserController@savePackOrders');
        });

        $router->group(['prefix' => 'gift'], function () use($router) {
            $router->post('/save', 'UserController@savePackGifts');
        });
    });

    $router->group(['prefix' => 'match'], function () use($router) {
        $router->post('/qr', 'UserController@checkQRCode');
    });

    $router->group(['prefix' => 'favorite'], function () use($router) {
        $router->post('/get', 'UserController@getFavorite');
        $router->post('/save', 'UserController@saveFavorite');
        $router->post('/remove', 'UserController@removeFavorite');
    });

    $router->get('qr/temp/get', 'UserController@getQRTemp');
    $router->post('service/timing/get', 'UserController@getServiceTiming');
    $router->post('booking/card/details/save', 'UserController@saveCardDetails');
    $router->post('booking/ids/save', 'UserController@updateDocument');
    $router->post('card/details/get', 'UserController@getCardDetails');
    $router->post('default/card/save', 'UserController@saveDefaultCard');
    $router->post('card/delete', 'UserController@deleteCard');
});
