<?php

namespace App\Http\Controllers\Shops\Events;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use App\ShopsEvents;
use App\Booking;
use DB;
use Carbon\Carbon;

class EventsController extends BaseController {

    public $successMsg = [
        
        'events.create' => 'Shop event created successfully',
        'events.update' => 'Shop event updated successfully',
        'events.delete' => 'Shop event deleted successfully',
        'events.data' => 'Events data found successfully',
    ];
    
    public function createEvent(Request $request) {
        
        $checks = ShopsEvents::validator($request->all());
        if ($checks->fails()) {
            return $this->returnError($checks->errors()->first(), NULL, true);
        }
        
        $event = ShopsEvents::create($request->all());
        return $this->returnSuccess(__($this->successMsg['events.create']),$event);
    }

    public function updateEvent(Request $request) {
        
        $checks = ShopsEvents::validator($request->all());
        if ($checks->fails()) {
            return $this->returnError($checks->errors()->first(), NULL, true);
        }
        
        $event = ShopsEvents::find($request->id);
        if (!$event || empty($request->id)) {
            return $this->returnError('notFound', NULL, true);
        }
        $event->update($request->all());
        return $this->returnSuccess(__($this->successMsg['events.update']),$event);
    }
    public function deleteEvent(Request $request) {
        
        $event = ShopsEvents::find($request->id);
        if (!$event || empty($request->id)) {
            return $this->returnError('notFound', NULL, true);
        }
        $event->delete();
        return $this->returnSuccess(__($this->successMsg['events.delete']));
    }
    
    public function getAllEvents(Request $request) {
        
        $month = isset($request->month) ? $request->month : Carbon::now()->month;
        $type = isset($request->type) ? $request->type : Booking::BOOKING_TYPE_IMC;
        
        $bookingData = DB::table('booking_massages')
                ->join('booking_infos', 'booking_infos.id', '=', 'booking_massages.booking_info_id')
                ->join('massage_timings', 'massage_timings.id', '=', 'booking_massages.massage_timing_id')
                ->join('massages', 'massages.id', '=', 'massage_timings.massage_id')
                ->join('bookings', 'bookings.id', '=', 'booking_infos.booking_id')
                ->select('booking_massages.id AS bookingMassageId','massages.name AS massageName', 'massage_timings.time AS massageDuration',
                        'booking_infos.massage_date AS massageDate','booking_infos.massage_time AS massageTime')
                ->where('bookings.booking_type' , $type)
                ->whereMonth('booking_infos.massage_date', $month)
                ->get();
        $eventsData = ShopsEvents::whereMonth('event_date',$month)->get();
        
        return $this->returnSuccess(__($this->successMsg['events.data']), ['bookingData' => $bookingData, 'eventsData' => $eventsData]);
    }

}
