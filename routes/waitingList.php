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


$router->group(['prefix' => 'waiting', 'namespace' => 'Shops'], function () use($router) {

    $router->post('/getOngoingMassage', 'WaitingList\WaitingListController@ongoingMassage');
    $router->post('/getWaitingMassage', 'WaitingList\WaitingListController@waitingMassage');
    $router->post('/getFutureBooking', 'WaitingList\WaitingListController@futureBooking');
    $router->post('/getCancelBooking', 'WaitingList\WaitingListController@cancelBooking');
    $router->post('/getCompletedBooking', 'WaitingList\WaitingListController@completedBooking');
});
