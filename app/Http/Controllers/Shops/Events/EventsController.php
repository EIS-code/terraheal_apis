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
        
        $data = $request->all();
        $data['event_date'] = Carbon::createFromTimestampMs($data['event_date']);
        
        $checks = ShopsEvents::validator($data);
        if ($checks->fails()) {
            return $this->returnError($checks->errors()->first(), NULL, true);
        }
        $event = ShopsEvents::create($data);
        return $this->returnSuccess(__($this->successMsg['events.create']),$event);
    }

    public function updateEvent(Request $request) {
        
        $data = $request->all();
        $data['event_date'] = Carbon::createFromTimestampMs($data['event_date']);
        
        $checks = ShopsEvents::validator($data);
        if ($checks->fails()) {
            return $this->returnError($checks->errors()->first(), NULL, true);
        }
        unset($data['id']);
        $event = ShopsEvents::find($data['event_id']);
        if (!$event || empty($data['event_id'])) {
            return $this->returnError('notFound', NULL, true);
        }
        $event->update($data);
        return $this->returnSuccess(__($this->successMsg['events.update']),$event);
    }
    public function deleteEvent(Request $request) {
        
        $event = ShopsEvents::find($request->event_id);
        if (!$event || empty($request->event_id)) {
            return $this->returnError('notFound', NULL, true);
        }
        $event->delete();
        return $this->returnSuccess(__($this->successMsg['events.delete']));
    }
    
    public function getAllEvents(Request $request) {
        
        $month = !empty($request->month) ? $request->month : Carbon::now()->month;
        $type = !empty($request->type) ? $request->type : Booking::BOOKING_TYPE_IMC;
        
        $bookingData = DB::table('booking_massages')
                ->join('booking_infos', 'booking_infos.id', '=', 'booking_massages.booking_info_id')
                ->join('service_pricings', 'service_pricings.id', '=', 'booking_massages.service_pricing_id')
                ->join('service_timings', 'service_timings.id', '=', 'service_pricings.service_timing_id')
                ->join('services', 'services.id', '=', 'service_pricings.service_id')
                ->join('bookings', 'bookings.id', '=', 'booking_infos.booking_id')
                ->select('booking_massages.id AS bookingMassageId','services.english_name AS english_name', 'services.portugese_name AS portugese_name',
                        'service_timings.time AS massageDuration', 'booking_infos.massage_date AS massageDate','booking_infos.massage_time AS massageTime')
                ->where('bookings.booking_type' , $type)
                ->whereMonth('booking_infos.massage_date', $month)
                ->get();
        $eventsData = ShopsEvents::whereMonth('event_date',$month)->get();
        
        return $this->returnSuccess(__($this->successMsg['events.data']), ['bookingData' => $bookingData, 'eventsData' => $eventsData]);
    }

}
