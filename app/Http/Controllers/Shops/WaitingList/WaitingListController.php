<?php

namespace App\Http\Controllers\Shops\WaitingList;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use App\Massage;
use DB;
use Carbon\Carbon;
use App\Booking;
use App\BookingMassage;
use App\BookingInfo;

class WaitingListController extends BaseController {

    public $query;

    public $successMsg = [
        
        'ongoing.massage' => 'Ongoing massages found successfully',
        'waiting.massage' => 'Waiting massages found successfully',
        'future.booking' => 'Future bookings found successfully',
        'completed.booking' => 'Completed bookings found successfully',
        'cancelled.booking' => 'Cancelled bookings found successfully',
    ];
    
    public function __construct() {

        $query = DB::table('booking_massages')
                        ->join('booking_infos', 'booking_infos.id', '=', 'booking_massages.booking_info_id')
                        ->join('massage_timings', 'massage_timings.id', '=', 'booking_massages.massage_timing_id')
                        ->join('massages', 'massages.id', '=', 'massage_timings.massage_id')
                        ->leftJoin('rooms', 'rooms.id', '=', 'booking_massages.room_id')
                        ->join('bookings', 'bookings.id', '=', 'booking_infos.booking_id')
                        ->join('users', 'users.id', '=', 'bookings.user_id')
                        ->join('therapists', 'therapists.id', '=', 'booking_infos.therapist_id')
                        ->join('session_types', 'session_types.id', '=', 'bookings.session_id')
                        ->leftJoin('user_gender_preferences', 'user_gender_preferences.id', '=', 'booking_massages.gender_preference')
                        ->select('booking_massages.id AS bookingMassageId', 'bookings.session_id AS sessionId', 'session_types.type AS sessionType', 
                                DB::raw('CONCAT(COALESCE(users.name,"")," ",COALESCE(users.surname,"")) AS clientName'), 'massages.name AS massageName',
                                'massage_timings.time AS massageDuration', 'booking_infos.massage_time as massageStartTime','booking_infos.massage_date AS massageDate',
                                DB::raw('CONCAT(COALESCE(therapists.name,"")," ",COALESCE(therapists.surname,"")) AS therapistName'), 
                                'rooms.name AS roomName', 'booking_massages.notes_of_injuries AS note', 'user_gender_preferences.name AS genderPreference');
        $this->query = $query;
    }

    public function ongoingMassage(Request $request) {

        $massage = Massage::where('shop_id' , $request->shop_id)->select('id')->get()->toArray();
        $type = isset($request->type) ? $request->type : Booking::BOOKING_TYPE_IMC;
        
        $ongoingMassages = $this->query->where(['booking_massages.is_confirm' => BookingMassage::IS_CONFIRM, 'bookings.booking_type' => $type])
                        ->whereIn('massage_timings.massage_id', $massage)->get();

        return $this->returnSuccess(__($this->successMsg['ongoing.massage']), $ongoingMassages);
    }

    public function waitingMassage(Request $request) {

        $massage = Massage::where('shop_id', $request->shop_id)->select('id')->get()->toArray();
        $type = isset($request->type) ? $request->type : Booking::BOOKING_TYPE_IMC;

        $waitingMassages = $this->query->where(['booking_massages.is_confirm' => BookingMassage::IS_NOT_CONFIRM, 'bookings.booking_type' => $type])
                        ->whereIn('massage_timings.massage_id', $massage)->get();

        return $this->returnSuccess(__($this->successMsg['waiting.massage']), $waitingMassages);
    }

    public function futureBooking(Request $request) {
        
        $massage = Massage::where('shop_id', $request->shop_id)->select('id')->get()->toArray();
        $type = isset($request->type) ? $request->type : Booking::BOOKING_TYPE_IMC;
        
        $futureBooking = $this->query->where('bookings.booking_type' , $type)
                        ->where('booking_infos.massage_date', '>=', Carbon::now()->format('Y-m-d'))
                        ->whereIn('massage_timings.massage_id', $massage)->get();
        
        return $this->returnSuccess(__($this->successMsg['future.booking']), $futureBooking);
    }
    
    public function completedBooking(Request $request) {
        
        $massage = Massage::where('shop_id', $request->shop_id)->select('id')->get()->toArray();
        $type = isset($request->type) ? $request->type : Booking::BOOKING_TYPE_IMC;
        
        $completedBooking = $this->query->where(['booking_infos.is_done' => BookingInfo::IS_DONE, 'bookings.booking_type' => $type])
                        ->whereIn('massage_timings.massage_id', $massage)->get();
        
        return $this->returnSuccess(__($this->successMsg['completed.booking']), $completedBooking);
    }
    
    public function cancelBooking(Request $request) {
        
        $massage = Massage::where('shop_id', $request->shop_id)->select('id')->get()->toArray();
        $type = isset($request->type) ? $request->type : Booking::BOOKING_TYPE_IMC;
        
        $cancelBooking = $this->query->where(['booking_infos.is_cancelled' => BookingInfo::IS_CANCELLED, 'bookings.booking_type' => $type])
                        ->whereIn('massage_timings.massage_id', $massage)->get();
        
        return $this->returnSuccess(__($this->successMsg['cancelled.booking']), $cancelBooking);
    }
}
