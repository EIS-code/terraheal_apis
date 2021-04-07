<?php

namespace App\Http\Controllers\Shops\Therapist;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use App\Booking;

class TherapistController extends BaseController {

    public $successMsg = [
        
        'bookings' => 'Therapist bookings found successfully!',
    ];
   
    public function myBookings(Request $request) {
        
        $booking_type = isset($request->type) ? $request->type : Booking::BOOKING_TYPE_IMC;
        $booking_time = isset($request->booking_time) ? ($request->booking_time == 0 ? Booking::BOOKING_TODAY : Booking::BOOKING_FUTURE) : Booking::BOOKING_TODAY;
        $booking_status = isset($request->booking_status) ? ($request->booking_status == 0 ? Booking::BOOKING_WAITING : Booking::BOOKING_COMPLETED) : Booking::BOOKING_WAITING;
        $request->request->add(['type' => $booking_type, 'bookings_filter' => array($booking_time,$booking_status)]);
        $bookingModel = new Booking();
        $myBookings = $bookingModel->getGlobalQuery($request);

        return $this->returnSuccess(__($this->successMsg['bookings']), $myBookings);
    }

}
