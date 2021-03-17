<?php

namespace App\Http\Controllers\Shops\WaitingList;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use App\Booking;
use App\BookingMassage;
use App\Massage;
use App\Therapist;

class WaitingListController extends BaseController {

    public $successMsg = [
        'ongoing.massage' => 'Ongoing massages found successfully',
        'waiting.massage' => 'Waiting massages found successfully',
        'future.booking' => 'Future bookings found successfully',
        'completed.booking' => 'Completed bookings found successfully',
        'cancelled.booking' => 'Cancelled bookings found successfully',
        'add.booking' => 'New booking massage added successfully',
        'therapists' => 'All therapists found successfully',
        'delete.booking' => 'Booking deleted successfully',
        'print.booking' => 'Booking data found successfully',
        'assign.room' => 'Assign room successfully',
    ];

    public function ongoingMassage(Request $request) {

        $type = isset($request->type) ? $request->type : Booking::BOOKING_TYPE_IMC;
        $request->request->add(['type' => $type, 'bookings_filter' => Booking::BOOKING_ONGOING]);
        $bookingModel = new Booking();
        $ongoingMassages = $bookingModel->getGlobalQuery($request)->groupBy('booking_id');

        return $this->returnSuccess(__($this->successMsg['ongoing.massage']), $ongoingMassages);
    }

    public function waitingMassage(Request $request) {

        $type = isset($request->type) ? $request->type : Booking::BOOKING_TYPE_IMC;
        $request->request->add(['type' => $type, 'bookings_filter' => Booking::BOOKING_WAITING]);
        $bookingModel = new Booking();
        $waitingMassages = $bookingModel->getGlobalQuery($request)->groupBy('booking_id');

        return $this->returnSuccess(__($this->successMsg['waiting.massage']), $waitingMassages);
    }

    public function futureBooking(Request $request) {

        $type = isset($request->type) ? $request->type : Booking::BOOKING_TYPE_IMC;
        $request->request->add(['type' => $type, 'bookings_filter' => Booking::BOOKING_FUTURE]);
        $bookingModel = new Booking();
        $futureBooking = $bookingModel->getGlobalQuery($request)->groupBy('booking_id');

        return $this->returnSuccess(__($this->successMsg['future.booking']), $futureBooking);
    }

    public function completedBooking(Request $request) {

        $type = isset($request->type) ? $request->type : Booking::BOOKING_TYPE_IMC;
        $request->request->add(['type' => $type, 'bookings_filter' => Booking::BOOKING_COMPLETED]);
        $bookingModel = new Booking();
        $completedBooking = $bookingModel->getGlobalQuery($request)->groupBy('booking_id');

        return $this->returnSuccess(__($this->successMsg['completed.booking']), $completedBooking);
    }

    public function cancelBooking(Request $request) {

        $type = isset($request->type) ? $request->type : Booking::BOOKING_TYPE_IMC;
        $request->request->add(['type' => $type, 'bookings_filter' => Booking::BOOKING_CANCELLED]);
        $bookingModel = new Booking();
        $cancelBooking = $bookingModel->getGlobalQuery($request)->groupBy('booking_id');

        return $this->returnSuccess(__($this->successMsg['cancelled.booking']), $cancelBooking);
    }
    
    public function addBookingMassage(Request $request) {
        
        $bookingMassage = BookingMassage::where('id',$request->booking_massage_id)->first();
        $newBooking = [];
        $massages = Massage::with(['timing' => function ($query) use ($request) {
                    $query->where('id', $request->massage_timing_id);
                }])->with(['pricing' => function ($query) use ($request) {
                    $query->where('massage_timing_id', $request->massage_timing_id);
                }])->where(['shop_id' => $request->shop_id, 'id' => $request->massage_id])->first();
        
        $newBooking['price'] = $massages->pricing[0]->price;
        $newBooking['cost'] = $massages->pricing[0]->cost;
        $newBooking['origional_price'] = $massages->pricing[0]->price;
        $newBooking['origional_cost'] = $massages->pricing[0]->cost;
        $newBooking['massage_timing_id'] = $massages->timing[0]->id;
        $newBooking['massage_prices_id'] = $massages->pricing[0]->id;
        $newBooking['exchange_rate'] = $bookingMassage->exchange_rate;
        $newBooking['notes_of_injuries'] = $bookingMassage->notes_of_injuries;
        $newBooking['booking_info_id'] = $bookingMassage->booking_info_id;
        $newBooking['pressure_preference'] = $bookingMassage->pressure_preference;
        $newBooking['gender_preference'] = $bookingMassage->gender_preference;
        $newBooking['focus_area_preference'] = $bookingMassage->focus_area_preference;
        
        $booking = BookingMassage::create($newBooking);
        
        return $this->returnSuccess(__($this->successMsg['add.booking']), $booking);
    }
    
    public function getAllTherapists(Request $request) {
        
        $therapists = Therapist::where(['shop_id' => $request->shop_id, 'is_deleted' => Therapist::IS_NOT_DELETED])
                ->pluck('name','id');
        
        return $this->returnSuccess(__($this->successMsg['therapists']), $therapists);
    }
    
    public function deleteBooking(Request $request) {
        
        $booking = BookingMassage::find($request->booking_massage_id);
        $booking->delete();
        
        return $this->returnSuccess(__($this->successMsg['delete.booking']), $booking);
    }
    
    public function printBookingDetails(Request $request) {
        
        $bookingModel = new Booking();
        $printDetails = $bookingModel->getGlobalQuery($request);
        
        $total_cost = 0;
        foreach ($printDetails as $key => $printDetail) {

            $total_cost += $printDetail->cost;
        }
        return $this->returnSuccess(__($this->successMsg['print.booking']), ['booking_details' => $printDetails,'total_cost' => $total_cost]);
    }
    
    public function assignRoom(Request $request) {
        
        $bookingMassage = BookingMassage::find($request->booking_massage_id);
        $bookingMassage->update(['room_id' => $request->room_id]);
        
        return $this->returnSuccess(__($this->successMsg['assign.room']), $bookingMassage);
        
    }
}
