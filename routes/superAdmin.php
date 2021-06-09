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

    $router->post('password/forgot', 'Auth\ForgotPasswordController@sendResetLinkEmail');
    $router->post('signIn', 'SuperAdminController@signIn');
    $router->post('update/profile', 'SuperAdminController@updateProfile');
    $router->post('details/get', 'SuperAdminController@getDetails');
    
    // For vouchers
    $router->post('addVoucher', 'SuperAdminController@addVoucher');
    $router->post('updateVoucher', 'SuperAdminController@updateVoucher');
    $router->get('getVouchers', 'SuperAdminController@getVouchers');
    $router->post('shareVoucher', 'SuperAdminController@shareVoucher');
    $router->post('purchaseVoucher', 'SuperAdminController@purchaseVoucher');
    $router->post('addServicesToVoucher', 'SuperAdminController@addServicesToVoucher');
    
    // For Packs
    $router->post('addPack', 'SuperAdminController@addPack');
    $router->get('getPacks', 'SuperAdminController@getPacks');
    $router->post('sharePack', 'SuperAdminController@sharePack');
    $router->post('purchasePack', 'SuperAdminController@purchasePack');
    
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

        $router->get('get', 'Therapist\TherapistController@getTherapists');
    });
    
    $router->group(['prefix' => 'sidebar'], function () use($router) {

        $router->post('details/get', 'Dashboard\DashboardController@getSidebarDetails');
    });
    
    $router->group(['prefix' => 'verify'], function () use($router) {
        $router->post('/mobile', 'SuperAdminController@verifyMobile');
        $router->post('/email', 'SuperAdminController@verifyEmail');
    });
    
    $router->group(['prefix' => 'compare'], function () use($router) {
        $router->post('/otp/email', 'SuperAdminController@compareOtpEmail');
        $router->post('/otp/mobile', 'SuperAdminController@compareOtpSms');
    });
    
});
