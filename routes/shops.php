<?php

use Illuminate\Http\Request;
use App\Shop;

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

config(['auth.defaults.guard' => 'shop']);
config(['auth.defaults.passwords' => 'shop']);

$router->group(['prefix' => 'shops', 'namespace' => 'Shops'], function () use($router) {

    $router->post('/signin', 'ShopsController@signIn');
    $router->post('/getTherapists', 'ShopsController@getAllTherapists');
    $router->post('/getServices', 'ShopsController@getAllServices');
    $router->post('/getClients', 'ShopsController@getAllClients');
    $router->post('/getPreferences', 'ShopsController@getPreferences');
});

$router->group(['prefix' => 'shops', 'namespace' => 'Shops', 'guard' => 'shop'], function () use($router) {

    $router->post('/forgot', 'Auth\ForgotPasswordController@sendResetLinkEmail');
});


$router->group(['prefix' => 'waiting', 'namespace' => 'Shops'], function () use($router) {

    $router->post('/getOngoingMassage', 'WaitingList\WaitingListController@ongoingMassage');
    $router->post('/getWaitingMassage', 'WaitingList\WaitingListController@waitingMassage');
    $router->post('/getFutureBooking', 'WaitingList\WaitingListController@futureBooking');
    $router->post('/getCancelBooking', 'WaitingList\WaitingListController@cancelBooking');
    $router->post('/getCompletedBooking', 'WaitingList\WaitingListController@completedBooking');
});

$router->group(['prefix' => 'dashboard', 'namespace' => 'Shops'], function () use($router) {

    $router->post('/getInfo', 'Dashboard\DashboardController@getDetails');
    $router->post('/getSalesInfo', 'Dashboard\DashboardController@salesInfo');
    $router->post('/getCustomerInfo', 'Dashboard\DashboardController@customerInfo');
});

$router->group(['prefix' => 'events', 'namespace' => 'Shops'], function () use($router) {
    
    $router->post('/addEvent', 'Events\EventsController@createEvent');
    $router->post('/updateEvent', 'Events\EventsController@updateEvent');
    $router->post('/deleteEvent', 'Events\EventsController@deleteEvent');
    $router->post('/getAllEvents', 'Events\EventsController@getAllEvents');
});

$router->group(['prefix' => 'clients', 'namespace' => 'Shops'], function () use($router) {
    
    $router->post('/searchClients', 'Clients\ClientController@searchClients');
});
