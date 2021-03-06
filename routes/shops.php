<?php


/*
  |--------------------------------------------------------------------------
  | Application Shops Routes
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
    $router->get('sessions/get', 'ShopsController@getSessionTypes');
    $router->post('shifts/add', 'ShopsController@addShift');
    $router->post('shifts/get', 'ShopsController@getShifts');
    $router->post('free/slots/get', 'ShopsController@getFreeSlots');
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
    $router->post('/getPastBooking', 'WaitingList\WaitingListController@pastBooking');
    $router->post('/addBookingMassage', 'WaitingList\WaitingListController@addBookingMassage');
    $router->post('/getAllTherapists', 'WaitingList\WaitingListController@getAllTherapists');
    $router->post('/deleteBooking', 'WaitingList\WaitingListController@deleteBooking');
    $router->post('/printBookingDetails', 'WaitingList\WaitingListController@printBookingDetails');
    $router->post('/assignRoom', 'WaitingList\WaitingListController@assignRoom');
    $router->post('/addNewBooking', 'WaitingList\WaitingListController@addNewBooking');
    $router->post('/bookingOverview', 'WaitingList\WaitingListController@bookingOverview');
    $router->post('/roomOccupation', 'WaitingList\WaitingListController@roomOccupation');
    $router->post('/getAllMassages', 'WaitingList\WaitingListController@getAllMassages');
    $router->post('/getAllTherapies', 'WaitingList\WaitingListController@getAllTherapies');
    $router->post('/getClientList', 'WaitingList\WaitingListController@clientList');
    $router->post('/addClient', 'WaitingList\WaitingListController@addClient');
    $router->post('/searchClients', 'WaitingList\WaitingListController@searchClients');
    $router->post('/getTimeTable', 'WaitingList\WaitingListController@getTimeTable');
    $router->post('/startServiceTime', 'WaitingList\WaitingListController@startServiceTime');
    $router->post('/endServiceTime', 'WaitingList\WaitingListController@endServiceTime');
    $router->post('/assignTherapist', 'WaitingList\WaitingListController@assignTherapist');
    $router->post('/confirmBooking', 'WaitingList\WaitingListController@confirmBooking');
    $router->post('/downgradeBooking', 'WaitingList\WaitingListController@downgradeBooking');
    $router->post('/cancelAppointment', 'WaitingList\WaitingListController@cancelAppointment');
    $router->post('/recoverAppointment', 'WaitingList\WaitingListController@recoverAppointment');
    $router->post('/getActivePacks', 'WaitingList\WaitingListController@getActivePacks');
    $router->post('/getUsedPacks', 'WaitingList\WaitingListController@getUsedPacks');
    $router->post('/getActiveVouchers', 'WaitingList\WaitingListController@getActiveVouchers');
    $router->post('/getUsedVouchers', 'WaitingList\WaitingListController@getUsedVouchers');
    $router->post('/searchPacks', 'WaitingList\WaitingListController@searchPacks');
    $router->post('/searchVouchers', 'WaitingList\WaitingListController@searchVouchers');
    
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
    $router->post('/getClientDetails', 'Clients\ClientController@clientDetails');
    $router->post('/getFutureBookings', 'Clients\ClientController@getFutureBookings');
    $router->post('/getPastBookings', 'Clients\ClientController@getPastBookings');
    $router->post('/getCancelledBookings', 'Clients\ClientController@getCancelledBookings');
    $router->post('/addForgotObject', 'Clients\ClientController@addForgotObject');
    $router->post('/returnForgotObject', 'Clients\ClientController@returnForgotObject');
    $router->post('/sendEmailToClient', 'Clients\ClientController@sendEmailToClient');
    $router->post('/updateRating', 'Clients\ClientController@updateRating');
    $router->post('/getRecipient', 'Clients\ClientController@getRecipient');
    $router->get('/getSources', 'Clients\ClientController@getSources');
    $router->post('/getForgotObjects', 'Clients\ClientController@getForgotObjects');
});

$router->group(['prefix' => 'rooms', 'namespace' => 'Shops'], function () use($router) {
    
    $router->post('/createRoom', 'Rooms\RoomsController@createRoom');
    $router->post('/getRooms', 'Rooms\RoomsController@getRooms');
});

$router->group(['prefix' => 'staffs', 'namespace' => 'Shops'], function () use($router) {
    
    $router->post('/add', 'Staffs\StaffsController@createStaff');
    $router->post('/update', 'Staffs\StaffsController@updateStaff');
    $router->post('/list', 'Staffs\StaffsController@staffList');
});

$router->group(['prefix' => 'manager', 'namespace' => 'Shops'], function () use($router) {
    
    $router->post('/signIn', 'Manager\ManagerController@signIn');
    $router->post('/availability/add', 'Manager\ManagerController@addAvailabilities');
    
    $router->group(['prefix' => 'news'], function () use($router) {
    
        $router->post('/add', 'News\NewsController@addNews');
        $router->post('/update', 'News\NewsController@updateNews');
        $router->post('/delete', 'News\NewsController@deleteNews');
        $router->post('/get', 'Manager\ManagerController@getNews');
        $router->post('/details/get', 'Manager\ManagerController@newsDetails');
    });

});

$router->group(['prefix' => 'receptionist', 'namespace' => 'Shops'], function () use($router) {
    
    $router->post('/createReceptionist', 'Receptionist\ReceptionistController@createReceptionist');
    $router->post('/update', 'Receptionist\ReceptionistController@updateReceptionist');
    $router->post('/addDocument', 'Receptionist\ReceptionistController@addDocument');
    $router->post('/getReceptionist', 'Receptionist\ReceptionistController@getReceptionist');
    $router->post('/getStatistics', 'Receptionist\ReceptionistController@getStatistics');
    $router->post('/takeBreak', 'Receptionist\ReceptionistController@takeBreak');
});


$router->group(['prefix' => 'therapist', 'namespace' => 'Shops'], function () use($router) {
    
    $router->post('/myBookings', 'Therapist\TherapistController@myBookings');
    $router->post('/getTherapists', 'Therapist\TherapistController@getTherapists');
    $router->post('/getInfo', 'Therapist\TherapistController@getInfo');
    $router->post('/updateProfile', 'Therapist\TherapistController@updateProfile');
    $router->post('/myAvailabilities', 'Therapist\TherapistController@myAvailabilities');
    $router->post('/addAvailability', 'Therapist\TherapistController@addAvailability');
    $router->post('/getRatings', 'Therapist\TherapistController@getRatings');
    $router->post('/myAttendence', 'Therapist\TherapistController@myAttendence');
    $router->post('/getCalendar', 'Therapist\TherapistController@getCalendar');
});
