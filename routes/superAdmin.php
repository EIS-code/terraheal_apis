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

$router->group(['prefix' => 'superAdmin', 'namespace' => 'SuperAdmin'], function () use($router) {

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
    
    $router->post('signIn', 'SuperAdminController@signIn');
});

$router->group(['prefix' => 'dashboard', 'namespace' => 'SuperAdmin'], function () use($router) {
    
    $router->post('details/get', 'Dashboard\DashboardController@getDetails');
});