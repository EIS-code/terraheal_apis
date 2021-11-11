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
    $router->post('/signin/forgot', 'ShopsController@forgotPassword');
    $router->post('/reset/password', 'ShopsController@resetPassword');
    $router->post('/verify/otp', 'ShopsController@verifyOtp');
    $router->post('/getTherapists', 'ShopsController@getAllTherapists');
    $router->post('/getServices', 'ShopsController@getAllServices');
    $router->post('/getClients', 'ShopsController@getAllClients');
    $router->post('/getPreferences', 'ShopsController@getPreferences');
    $router->get('sessions/get', 'ShopsController@getSessionTypes');
    $router->post('shifts/add', 'ShopsController@addShift');
    $router->post('shifts/get', 'ShopsController@getShifts');
    $router->post('free/slots/get', 'ShopsController@getFreeSlots');
    $router->post('booking/confirm', 'ShopsController@confirmBooking');
    $router->post('location/get', 'ShopsController@getShopRooms');
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
    $router->post('/booking/add', 'WaitingList\WaitingListController@addBooking');
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
    $router->post('/edit/booking', 'WaitingList\WaitingListController@editBooking');
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
    $router->post('/inform', 'Clients\ClientController@informClient');
});

$router->group(['prefix' => 'rooms', 'namespace' => 'Shops'], function () use($router) {
    
    $router->post('/createRoom', 'Rooms\RoomsController@createRoom');
    $router->post('/getRooms', 'Rooms\RoomsController@getRooms');
});

$router->group(['prefix' => 'staffs', 'namespace' => 'Shops'], function () use($router) {
    
    $router->post('/add', 'Staffs\StaffsController@createStaff');
    $router->post('/update', 'Staffs\StaffsController@updateStaff');
    $router->post('/list', 'Staffs\StaffsController@staffList');
    $router->post('/document/upload', 'Staffs\StaffsController@uploadDocument');
});

$router->group(['prefix' => 'manager', 'namespace' => 'Shops'], function () use($router) {
    
    $router->post('/signIn', 'Manager\ManagerController@signIn');
    $router->post('/signin/forgot', 'Manager\ManagerController@forgotPassword');
    $router->post('/reset/password', 'Manager\ManagerController@resetPassword');
    $router->post('/dashboard/info/get', 'Manager\ManagerController@getInfo');
    $router->post('/massages/get', 'Manager\ManagerController@getMassages');
    $router->post('/therapies/get', 'Manager\ManagerController@getTherapies');
    $router->post('/bookings/get', 'Manager\ManagerController@getBookings');
    $router->post('/users/get', 'Manager\ManagerController@getUsers');
    $router->post('/profile/update', 'Manager\ManagerController@updateProfile');
    $router->post('/profile/get', 'Manager\ManagerController@getProfile');
    $router->post('/side/data/get', 'Dashboard\DashboardController@getDetails');
    $router->post('/salesInfo/get', 'Dashboard\DashboardController@salesInfo');
    $router->post('/customerInfo/get', 'Dashboard\DashboardController@customerInfo');
    $router->post('/therapist/document/delete', 'Manager\ManagerController@deleteDocument');
    
    $router->group(['prefix' => 'therapist'], function () use($router) {
        
        $router->post('/availability/add', 'Manager\ManagerController@addAvailabilities');
        $router->post('/get', 'Manager\ManagerController@getTherapists');
        $router->post('/get/all', 'Manager\ManagerController@getAllTherapists');
        $router->post('/getInfo', 'Therapist\TherapistController@getInfo');
        $router->post('/timetable/get', 'WaitingList\WaitingListController@getTimeTable');
        $router->post('/ratings/get', 'Therapist\TherapistController@getTherapistRatings');
        
        $router->group(['prefix' => 'service'], function () use($router) {
            $router->post('/add', 'Manager\ManagerController@addService');
            $router->post('/delete', 'Manager\ManagerController@deleteService');
        });
    });
    
    $router->group(['prefix' => 'staff'], function () use($router) {
        
        $router->post('/add', 'Staffs\StaffsController@createStaff');
        $router->post('/update', 'Staffs\StaffsController@updateStaff');
        $router->post('/list', 'Staffs\StaffsController@staffList');        
        $router->post('/document/upload', 'Staffs\StaffsController@uploadDocument');
        $router->post('/update/status', 'Staffs\StaffsController@updateStatus');
    });
    
    $router->group(['prefix' => 'verify'], function () use($router) {
        $router->post('/mobile', 'Manager\ManagerController@verifyMobile');
        $router->post('/email', 'Manager\ManagerController@verifyEmail');
        $router->post('/otp', 'Manager\ManagerController@verifyOtp');
    });
       
    $router->group(['prefix' => 'fcm'], function () use($router) {
        $router->post('/token/save', 'Manager\ManagerController@saveToken');
    });
    
    $router->group(['prefix' => 'notification'], function () use($router) {
        $router->get('/unread', 'Manager\ManagerController@getUnreadNotification');
    });
    
    $router->group(['prefix' => 'packs'], function () use($router) {
        $router->post('/get', 'Manager\ManagerController@getPacks');
    });
    
    $router->group(['prefix' => 'vouchers'], function () use($router) {
        $router->post('/get', 'Manager\ManagerController@getVouchers');
    });
    
    $router->group(['prefix' => 'compare'], function () use($router) {
        $router->post('/otp/email', 'Manager\ManagerController@compareOtpEmail');
        $router->post('/otp/mobile', 'Manager\ManagerController@compareOtpSms');
    });
    
    $router->group(['prefix' => 'news'], function () use($router) {
    
        $router->post('/add', 'News\NewsController@addNews');
        $router->post('/update', 'News\NewsController@updateNews');
        $router->post('/delete', 'News\NewsController@deleteNews');
        $router->post('/get', 'Manager\ManagerController@getNews');
        $router->post('/details/get', 'Manager\ManagerController@newsDetails');
        $router->post('/read', 'Therapist\TherapistController@readNews');
    });
    
    $router->group(['prefix' => 'clients'], function () use($router) {

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
        $router->post('/questionnaries/update', 'Manager\ManagerController@updateQuestionnaries');
        $router->post('/document/accept', 'Manager\ManagerController@acceptDocument');
        $router->post('/document/decline', 'Manager\ManagerController@declineDocument');
        $router->post('/inform', 'Clients\ClientController@informClient');
    });
    
    $router->group(['prefix' => 'waiting'], function () use($router) {

        $router->post('/getOngoingMassage', 'WaitingList\WaitingListController@ongoingMassage');
        $router->post('/getWaitingMassage', 'WaitingList\WaitingListController@waitingMassage');
        $router->post('/getFutureBooking', 'WaitingList\WaitingListController@futureBooking');
        $router->post('/getCancelBooking', 'WaitingList\WaitingListController@cancelBooking');
        $router->post('/getCompletedBooking', 'WaitingList\WaitingListController@completedBooking');
        $router->post('/getPastBooking', 'WaitingList\WaitingListController@pastBooking');
        $router->post('/addBookingMassage', 'WaitingList\WaitingListController@addBookingMassage');
        $router->post('/deleteBooking', 'WaitingList\WaitingListController@deleteBooking');
        $router->post('/printBookingDetails', 'WaitingList\WaitingListController@printBookingDetails');
        $router->post('/assignRoom', 'WaitingList\WaitingListController@assignRoom');
        $router->post('/addNewBooking', 'WaitingList\WaitingListController@addNewBooking');
        $router->post('/booking/add', 'WaitingList\WaitingListController@addBooking');
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
        $router->post('/edit/booking', 'WaitingList\WaitingListController@editBooking');
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
    
    $router->post('/new', 'Manager\ManagerController@newTherapist');
    $router->post('/existing', 'Manager\ManagerController@existingTherapist');
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

$router->group(['namespace' => 'User'], function () use($router) {

    $router->post('manager/user/document/upload', 'UserController@updateDocument');
    
});
