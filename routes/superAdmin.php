<?php

/*
|--------------------------------------------------------------------------
| Application News Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

config(['auth.defaults.guard' => 'superadmin']);
config(['auth.defaults.passwords' => 'superadmins']);


$router->group(['prefix' => 'superAdmin', 'namespace' => 'SuperAdmin', 'guard' => 'superadmin'], function () use($router) {

    $router->post('signin/forgot', 'SuperAdminController@forgotPassword');
    $router->post('reset/password', 'SuperAdminController@resetPassword');
    $router->post('signIn', 'SuperAdminController@signIn');
    $router->post('update/profile', 'SuperAdminController@updateProfile');
    $router->post('details/get', 'SuperAdminController@getDetails');
    $router->post('/fcm/token/save', 'SuperAdminController@saveToken');    
    
    // For vouchers
    $router->post('addVoucher', 'SuperAdminController@addVoucher');
    $router->post('updateVoucher', 'SuperAdminController@updateVoucher');
    $router->get('getVouchers', 'SuperAdminController@getVouchers');
    $router->post('shareVoucher', 'SuperAdminController@shareVoucher');
    $router->post('addServicesToVoucher', 'SuperAdminController@addServicesToVoucher');
    
    // For Packs
    $router->post('addPack', 'SuperAdminController@addPack');
    $router->get('getPacks', 'SuperAdminController@getPacks');
    $router->post('sharePack', 'SuperAdminController@sharePack');
    
    //service    
    $router->post('service/add', 'SuperAdminController@addService');
    $router->get('massages/get', 'SuperAdminController@getMassages');
    $router->get('therapies/get', 'SuperAdminController@getTherapies');
    
    $router->group(['prefix' => 'dashboard'], function () use($router) {

        $router->get('details/get', 'Dashboard\DashboardController@getDetails');
        $router->get('centers/get', 'Dashboard\DashboardController@getCenters');
    });
    
    $router->group(['prefix' => 'center'], function () use($router) {
        
        $router->post('details/get', 'Center\CenterController@getCenterDetails');
        $router->post('therapists/get', 'Center\CenterController@getTherapists');
        $router->post('booking/details/get', 'Center\CenterController@getCenterBookings');
        $router->post('details/add', 'Center\CenterController@addCenterDetails');
        $router->post('company/details/add', 'Center\CenterController@addOrUpdateCompanyDetails');
        $router->post('owner/details/add', 'Center\CenterController@addOwnerDetails');
        $router->post('payment/details/add', 'Center\CenterController@addPaymentDetails');
        $router->post('paymentAgreement/details/add', 'Center\CenterController@addPaymentAgreement');
        $router->post('documents/upload', 'Center\CenterController@uploadDocuments');
        $router->post('vouchers/add', 'Center\CenterController@addVouchers');
        $router->post('packs/add', 'Center\CenterController@addPacks');
        $router->get('constants/get', 'Center\CenterController@getConstants');
        $router->post('constants/add', 'Center\CenterController@addConstants');
        $router->post('featuredImages/delete', 'Center\CenterController@deleteFeaturedImages');
        $router->post('galleryImages/delete', 'Center\CenterController@deleteGalleryImages');
        $router->post('users/get', 'Center\CenterController@getUsers');
        $router->post('services/add', 'Center\CenterController@addServices');
    });
    
    $router->group(['prefix' => 'therapists'], function () use($router) {

        $router->post('get', 'Therapist\TherapistController@getTherapists');
        $router->post('profile/get', 'Therapist\TherapistController@getInfo');
        $router->post('ratings/get', 'Therapist\TherapistController@getRatings');
    });
    
    $router->group(['prefix' => 'sidebar'], function () use($router) {

        $router->post('details/get', 'Dashboard\DashboardController@getSidebarDetails');
    });
    
    $router->group(['prefix' => 'clients'], function () use($router) {

        $router->get('get', 'Client\ClientController@getAllClients');
        $router->post('details/get', 'Client\ClientController@getInfo');
        $router->post('future/bookings/get', 'Client\ClientController@getFutureBookings');
        $router->post('past/bookings/get', 'Client\ClientController@getPastBookings');
        $router->post('cancelled/bookings/get', 'Client\ClientController@getCancelledBookings');
        $router->post('pending/bookings/get', 'Client\ClientController@getPendingBookings');
        $router->post('therapists/get', 'Client\ClientController@getTherapists');
        $router->post('therapists/details/get', 'Client\ClientController@getTherapistDetails');
        $router->post('print/booking', 'Client\ClientController@printBooking');
        $router->post('address/get', 'Client\ClientController@getAddress');
        $router->post('centers/get', 'Client\ClientController@getCenters');
        $router->post('center/details/get', 'Client\ClientController@getCenterDetails');
        $router->post('used/vouchers/get', 'Client\ClientController@getUsedVouchers');
        $router->post('unused/vouchers/get', 'Client\ClientController@getUnUsedVouchers');
        $router->post('voucher/details/get', 'Client\ClientController@getVoucherDetails');
        $router->post('packs/get', 'Client\ClientController@getPacks');
        $router->post('pack/details/get', 'Client\ClientController@getPackDetails');
        $router->post('massagePreference/get', 'Client\ClientController@getMassagePreferences');
        $router->post('questionnaries/get', 'Client\ClientController@getQuestionnaries');
        $router->post('forgot/objects/get', 'Client\ClientController@getForgotObjects');
    });
    
    $router->group(['prefix' => 'verify'], function () use($router) {
        $router->post('/mobile', 'SuperAdminController@verifyMobile');
        $router->post('/email', 'SuperAdminController@verifyEmail');
        $router->post('/otp', 'SuperAdminController@verifyOtp');
    });
    
    $router->group(['prefix' => 'compare'], function () use($router) {
        $router->post('/otp/email', 'SuperAdminController@compareOtpEmail');
        $router->post('/otp/mobile', 'SuperAdminController@compareOtpSms');
    });
    
    $router->group(['prefix' => 'bookings'], function () use($router) {
        $router->post('/cancel', 'SuperAdminController@cancelBooking');
        $router->post('/past', 'SuperAdminController@pastBooking');
        $router->post('/future', 'SuperAdminController@futureBooking');
        $router->post('/pending', 'SuperAdminController@pendingBooking');
        $router->post('/details', 'SuperAdminController@printBookingDetails');
    });
    
    $router->group(['prefix' => 'notification'], function () use($router) {
        $router->post('/unread', 'SuperAdminController@getUnreadNotification');
        $router->post('/read', 'SuperAdminController@readNotification');
    });
    
});