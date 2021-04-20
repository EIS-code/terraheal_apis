<?php

namespace App\Http\Controllers\Shops\WaitingList;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use App\Booking;
use App\BookingMassage;
use App\Massage;
use App\Therapist;
use App\UserPeople;
use App\Shop;
use DB;
use Carbon\Carbon;
use App\Therapy;
use App\User;
use App\TherapistWorkingSchedule;
use App\Pack;
use App\BookingInfo;
use App\Room;
use App\MassageTiming;
use App\TherapiesTimings;

class WaitingListController extends BaseController {

    public $successMsg = [
        'ongoing.massage' => 'Ongoing massages found successfully',
        'waiting.massage' => 'Waiting massages found successfully',
        'future.booking' => 'Future bookings found successfully',
        'completed.booking' => 'Completed bookings found successfully',
        'cancelled.booking' => 'Cancelled bookings found successfully',
        'past.booking' => 'Past bookings found successfully',
        'add.booking' => 'New booking massage added successfully',
        'therapists' => 'All therapists found successfully',
        'delete.booking' => 'Booking deleted successfully',
        'print.booking' => 'Booking data found successfully',
        'assign.room' => 'Assign room successfully',
        'assign.therapist' => 'Assign therapist successfully',
        'new.booking' => 'New booking added successfully',
        'booking.overview' => 'Bookings found successfully',
        'massages' => 'Massages found successfully',
        'therapies' => 'Therapies found successfully',
        'client.list' => 'List of clients found successfully',
        'client.add' => 'Client added successfully',
        'client.data.found' => 'Client data found successfully',       
        'schedule.data.found' => 'Time Table data found successfully',
        'booking.start' => "Service started successfully !",
        'booking.end' => "Service ended successfully !",
        'not.found' => "Data not found",
        'booking.not.found' => "Booking not found",
        'therapist.not.found' => "Therapist not found",
        'room.not.found' => "Room not found",
        'booking.confirm' => "Booking confirm successfully!",
        'booking.downgrade' => "Booking downgrade successfully!",
        'room.not.available' => "Room not available!",
        'cancel.appointment' => "Appointment cancelled successfully!",
    ];

    public function ongoingMassage(Request $request) {

        $type = isset($request->type) ? $request->type : Booking::BOOKING_TYPE_IMC;
        $request->request->add(['type' => $type, 'bookings_filter' => array(Booking::BOOKING_ONGOING)]);
        $bookingModel = new Booking();
        $ongoingMassages = $bookingModel->getGlobalQuery($request);

        return $this->returnSuccess(__($this->successMsg['ongoing.massage']), $ongoingMassages);
    }

    public function waitingMassage(Request $request) {

        $type = isset($request->type) ? $request->type : Booking::BOOKING_TYPE_IMC;
        $request->request->add(['type' => $type, 'bookings_filter' => array(Booking::BOOKING_WAITING)]);
        if(isset($request->service)) {
            $request->request->add(['service' => $request->service]);
        }
        $bookingModel = new Booking();
        $waitingMassages = $bookingModel->getGlobalQuery($request);

        return $this->returnSuccess(__($this->successMsg['waiting.massage']), $waitingMassages);
    }

    public function futureBooking(Request $request) {

        $type = isset($request->type) ? $request->type : Booking::BOOKING_TYPE_IMC;
        $request->request->add(['type' => $type, 'bookings_filter' => array(Booking::BOOKING_FUTURE)]);
        if(isset($request->date)){
            $date  = Carbon::createFromTimestampMs($request->date);
            $request->request->add(['date' => (new Carbon($date))->format('Y-m-d')]);
        }
        $bookingModel = new Booking();
        $futureBooking = $bookingModel->getGlobalQuery($request);

        return $this->returnSuccess(__($this->successMsg['future.booking']), $futureBooking);
    }

    public function completedBooking(Request $request) {

        $type = isset($request->type) ? $request->type : Booking::BOOKING_TYPE_IMC;
        $request->request->add(['type' => $type, 'bookings_filter' => array(Booking::BOOKING_COMPLETED)]);
        if(isset($request->date)){
            $date  = Carbon::createFromTimestampMs($request->date);
            $request->request->add(['date' => (new Carbon($date))->format('Y-m-d')]);
        }
        $bookingModel = new Booking();
        $completedBooking = $bookingModel->getGlobalQuery($request);

        return $this->returnSuccess(__($this->successMsg['completed.booking']), $completedBooking);
    }

    public function cancelBooking(Request $request) {

        $type = isset($request->type) ? $request->type : Booking::BOOKING_TYPE_IMC;
        $request->request->add(['type' => $type, 'bookings_filter' => array(Booking::BOOKING_CANCELLED)]);
        if(isset($request->date)){
            $date  = Carbon::createFromTimestampMs($request->date);
            $request->request->add(['date' => (new Carbon($date))->format('Y-m-d')]);
        }
        $bookingModel = new Booking();
        $cancelBooking = $bookingModel->getGlobalQuery($request);

        return $this->returnSuccess(__($this->successMsg['cancelled.booking']), $cancelBooking);
    }
    
    public function pastBooking(Request $request) {

        $type = isset($request->type) ? $request->type : Booking::BOOKING_TYPE_IMC;
        $request->request->add(['type' => $type, 'bookings_filter' => array(Booking::BOOKING_PAST)]);
        $bookingModel = new Booking();
        $pastBooking = $bookingModel->getGlobalQuery($request);

        return $this->returnSuccess(__($this->successMsg['past.booking']), $pastBooking);
    }
    
    public function addBookingMassage(Request $request) {
        
        $bookingMassage = BookingMassage::where('id',$request->booking_massage_id)->first();
        if(empty($bookingMassage)) {
            return $this->returnError(__($this->successMsg['not.found']));
        }
        $newBooking = [];
        
        // 0 = for massage, 1 = for therapy
        if($request->service_type == 0)
        {
            $massages = Massage::with(['timing' => function ($query) use ($request) {
                    $query->where('id', $request->service_timing_id);
                }])->with(['pricing' => function ($query) use ($request) {
                    $query->where('massage_timing_id', $request->service_timing_id);
                }])->where(['shop_id' => $request->shop_id, 'id' => $request->service_id])->first();
            
            $newBooking['massage_timing_id'] = $massages->timing[0]->id;
            $newBooking['massage_prices_id'] = $massages->pricing[0]->id;
            $newBooking['price'] = $massages->pricing[0]->price;
            $newBooking['cost'] = $massages->pricing[0]->cost;
            $newBooking['origional_price'] = $massages->pricing[0]->price;
            $newBooking['origional_cost'] = $massages->pricing[0]->cost;        
        } else {
            $therapy = Therapy::with(['timing' => function ($query) use ($request) {
                    $query->where('id', $request->service_timing_id);
                }])->with(['pricing' => function ($query) use ($request) {
                    $query->where('therapy_timing_id', $request->service_timing_id);
                }])->where(['shop_id' => $request->shop_id, 'id' => $request->service_id])->first();
                
            $newBooking['therapy_timing_id'] = $therapy->timing[0]->id;
            $newBooking['therapy_prices_id'] = $therapy->pricing[0]->id;
            $newBooking['price'] = $therapy->pricing[0]->price;
            $newBooking['cost'] = $therapy->pricing[0]->cost;
            $newBooking['origional_price'] = $therapy->pricing[0]->price;
            $newBooking['origional_cost'] = $therapy->pricing[0]->cost;
        }
                                
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
        if(empty($booking)) {
            return $this->returnError(__($this->successMsg['not.found']));
        }
        $booking->delete();
        return $this->returnSuccess(__($this->successMsg['delete.booking']), $booking);
    }
    
    public function printBookingDetails(Request $request) {
        
        $bookingModel = new Booking();
        $printDetails = $bookingModel->getGlobalQuery($request);
        
        if(empty($printDetails)) {
            return $this->returnError(__($this->successMsg['not.found']));
        }
        $services =[];
        $total_cost = 0;
        foreach ($printDetails as $key => $printDetail) {
            
            $total_cost += $printDetail->cost;
            $services[] = [
                    "massage_name" => $printDetail['massage_name'],
                    "massage_date" => $printDetail['massage_date'],
                    "massage_start_time" => $printDetail['massage_start_time'],
                    "massage_end_time" => $printDetail['massage_end_time'],
                    "massage_duration" => $printDetail['massage_duration'],
                    "massage_day_name" => $printDetail['massage_day_name'],
                    "therapy_name" => $printDetail['therapy_name'],
                    "theropy_end_time" => $printDetail['theropy_end_time'],
                    "theropy_duration" => $printDetail['theropy_duration'],
                    "cost" => $printDetail['cost'],
                    "gender_preference" => $printDetail['gender_preference'],
                    "pressure_preference" => $printDetail['pressure_preference'],
                    "focus_area" => $printDetail['focus_area'],
                    "genderPreference" => $printDetail['genderPreference'],
                    "notes" => $printDetail['notes'],
                    "injuries" => $printDetail['injuries']
            ];
        }
        $bookingDetails = [
            "booking_id" => $printDetails[0]['booking_id'],
            "book_platform" => $printDetails[0]['book_platform'],
            "is_confirm" => $printDetails[0]['is_confirm'],
            "is_done" => $printDetails[0]['is_done'],
            "is_cancelled" => $printDetails[0]['is_cancelled'],
            "cancel_type" => $printDetails[0]['cancel_type'],
            "cancelled_reason" => $printDetails[0]['cancelled_reason'],
            "booking_type" => $printDetails[0]['booking_type_value'],
            "client_id" => $printDetails[0]['client_id'],
            "client_name" => $printDetails[0]['client_name'],
            "client_gender" => $printDetails[0]['client_gender'],
            "client_age" => $printDetails[0]['client_age'],
            "sessionId" => $printDetails[0]['sessionId'],
            "session_type" => $printDetails[0]['session_type'],
            "shop_id" => $printDetails[0]['shop_id'],
            "shop_name" => $printDetails[0]['shop_name'],
            "shop_address" => $printDetails[0]['shop_address'],
            "therapist_id" => $printDetails[0]['therapist_id'],
            "therapistName" => $printDetails[0]['therapistName'],
            "room_id" => $printDetails[0]['room_id'],
            "roomName" => $printDetails[0]['roomName'],
            "table_futon_quantity" => $printDetails[0]['table_futon_quantity'],
            "booking_services" => $services,
            "total_cost" => $total_cost

        ];
        return $this->returnSuccess(__($this->successMsg['print.booking']), $bookingDetails);
    }
    
    public function getStartEndTime($booking) {

        if (isset($booking->massage_timing_id) && is_null($booking->therapy_timing_id)) {
            $endtime = MassageTiming::find($booking->massage_timing_id);
        }
        if (isset($booking->therapy_timing_id) && is_null($booking->massage_timing_id)) {
            $endtime = TherapiesTimings::find($booking->therapy_timing_id);
        }

        $startTime = Carbon::createFromTimestampMs($booking->bookingInfo->massage_time);
        $startTime = (new Carbon($startTime))->format("H:i:s");

        $endtime = (new Carbon($startTime))->addMinutes($endtime->time);
        $endtime = (new Carbon($endtime))->format("H:i:s");

        return collect(["startTime" => $startTime, "endTime" => $endtime]);
    }

    public function assignRoom(Request $request) {
        
        $bookingMassage = BookingMassage::with('bookingInfo')->find($request->booking_massage_id);
        if(empty($bookingMassage)) {
            return $this->returnSuccess(__($this->successMsg['booking.not.found']));
        }
        $room = Room::find($request->room_id);
        if(empty($room)) {
            return $this->returnSuccess(__($this->successMsg['room.not.found']));
        }
        
        $current_times = $this->getStartEndTime($bookingMassage);
        $date  = Carbon::createFromTimestampMs($bookingMassage->bookingInfo->massage_date);
        
        $bookings = BookingMassage::with('bookingInfo')->where('room_id',$request->room_id)
                        ->whereHas('bookingInfo', function($q) use($bookingMassage, $date) {
                            $q->where('massage_date',$date)->where('id', '!=', $bookingMassage->bookingInfo->id);
                        })->get();
        if(!empty($bookings)) {
            foreach ($bookings as $key => $booking) {

                $times = $this->getStartEndTime($booking);
                if ($current_times['startTime'] >= $times['startTime'] && $current_times['startTime'] <= $times['endTime']) {

                    return $this->returnSuccess(__($this->successMsg['room.not.available']));
                }
            }
        }

        $bookingMassage->update(['room_id' => $request->room_id]);
        
        return $this->returnSuccess(__($this->successMsg['assign.room']), $bookingMassage);
    }
    
    public function assignTherapist(Request $request) {
        
        $bookingInfo = BookingInfo::find($request->booking_info_id);
        $therapist = Therapist::find($request->therapist_id);
        
        if(empty($bookingInfo)) {
            return $this->returnSuccess(__($this->successMsg['booking.not.found']));
        }
        
        if(empty($therapist)) {
            return $this->returnSuccess(__($this->successMsg['therapist.not.found']));
        }
        
        $bookingInfo->update(['therapist_id' => $request->therapist_id]);
        
        return $this->returnSuccess(__($this->successMsg['assign.therapist']), $bookingInfo);
    }
    
    public function addNewBooking(Request $request) {

        DB::beginTransaction();
        try {
            $shopModel = new Shop();
            
            $bookingData = [
                'booking_type' => isset($request->booking_type) ? $request->booking_type : Booking::BOOKING_TYPE_IMC,
                'special_notes' => $request->special_notes,
                'user_id' => $request->user_id,
                'shop_id' => $request->shop_id,
                'session_id' => $request->session_id,
                'pack_id' => isset($request->pack_id) ? $request->pack_id : NULL
            ];
            $newBooking = Booking::create($bookingData);
            $isPack = isset($request->pack_id) ? $request->pack_id : NULL;
            $bookingInfo = $shopModel->addBookingInfo($request, $newBooking, NULL, $isPack);
            
            if(isset($request->pack_id)) {
                
                $pack = Pack::with('services')->where('id', $request->pack_id)->first();
                if (empty($pack)) {
                    return ['isError' => true, 'message' => 'Pack not found'];
                }
                foreach ($pack->services as $key => $service) {

                    $isMassage = isset($service->massage_id) && empty($service->therapy_id) ? true : false;
                    $shopModel->addBookingMassages($service, $bookingInfo, $request, NULL, $isMassage);
                }
            } else {
                $isMassage = false;
                if(count($request->massages) > 0)
                {
                    $isMassage = true;
                    foreach ($request->massages as $key => $massage) {

                        $shopModel->addBookingMassages($massage, $bookingInfo, $request, NULL, $isMassage);
                    }
                }
                if(count($request->therapies) > 0)
                {
                    $isMassage = false;
                    foreach ($request->therapies as $key => $therapy) {

                        $shopModel->addBookingMassages($therapy, $bookingInfo, $request, NULL, $isMassage);
                    }
                }
                if (!empty($request->users)) {
                    foreach ($request->users as $key => $user) {
                        $user_people = [
                            'name' => $user['name'],
                            'age' => $user['age'],
                            'gender' => $user['gender'],
                            'user_gender_preference_id' => $user['gender_preference'],
                            'user_id' => $request->user_id
                        ];
                        $newUser = UserPeople::create($user_people);

                        $newUser['therapist_id'] = $user['therapist_id'];
                        $newUser['notes_of_injuries'] = $user['notes_of_injuries'];
                        
                        $bookingInfo = $shopModel->addBookingInfo($request, $newBooking, $newUser, NULL);

                        if (count($user['massages']) > 0) {

                            $isMassage = true;
                            foreach ($user['massages'] as $key => $massage) {

                                $shopModel->addBookingMassages($massage, $bookingInfo, $request, $user, $isMassage);
                            }
                        } else {

                            $isMassage = false;
                            foreach ($user['therapies'] as $key => $therapy) {

                                $shopModel->addBookingMassages($therapy, $bookingInfo, $request, $user, $isMassage);
                            }
                        }
                    }
                }
            }
            DB::commit();
            return $this->returnSuccess(__($this->successMsg['new.booking']));
            
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollback();
            throw $e;
        }
    }
    
    public function bookingOverview(Request $request) {
        
        $date  = Carbon::createFromTimestampMs($request->date);
        $type = isset($request->type) ? $request->type : Booking::BOOKING_TYPE_IMC;
        $date = isset($request->date) ? (new Carbon($date))->format('Y-m-d') : Carbon::now()->format('Y-m-d');
        $request->request->add(['type' => $type, 'date' => $date]);
        
        $bookingModel = new Booking();
        $bookingOverviews = $bookingModel->getGlobalQuery($request)->whereNotNull('therapist_id')->groupBy('therapist_id');
        
        $services = [];
        foreach ($bookingOverviews as $key => $bookings) {
            $therapist_id = $bookings[0]['therapist_id'];
            $therapist_name = $bookings[0]['therapistName'];
            foreach ($bookings as $key => $booking) {
                $services[] = [
                    "service_day_name" => $booking['massage_day_name'],
                    "massage_date" => $booking['massage_date'],
                    "massage_name" => $booking['massage_name'],
                    "massage_start_time" => $booking['massage_start_time'],
                    "massage_end_time" => $booking['massage_end_time'],
                    "massage_duration" => $booking['massage_duration'],
                    "therapy_name" => $booking['therapy_name'],
                    "theropy_end_time" => $booking['theropy_end_time'],
                    "theropy_duration" => $booking['theropy_duration']
                ];
            }
            $allBookings[] =[
                "therapist_id" => $therapist_id,
                "therapist_name" => $therapist_name,
                "services" => $services
             ];
        }
        
        return $this->returnSuccess(__($this->successMsg['booking.overview']), $allBookings);
    }
    
    public function roomOccupation(Request $request) {
        
        $date  = Carbon::createFromTimestampMs($request->date);
        $type = isset($request->type) ? $request->type : Booking::BOOKING_TYPE_IMC;
        $date = isset($request->date) ? (new Carbon($date))->format('Y-m-d') : Carbon::now()->format('Y-m-d');
        $request->request->add(['type' => $type, 'date' => $date]);
        
        $bookingModel = new Booking();
        $roomOccupied = $bookingModel->getGlobalQuery($request)->whereNotNull('room_id')->groupBy('room_id');
        
        $services = [];
        foreach ($roomOccupied as $key => $bookings) {
            $room_id = $bookings[0]['room_id'];
            $room_name = $bookings[0]['roomName'];
            foreach ($bookings as $key => $booking) {
                $services[] = [
                    "service_day_name" => $booking['massage_day_name'],
                    "massage_date" => $booking['massage_date'],
                    "massage_name" => $booking['massage_name'],
                    "massage_start_time" => $booking['massage_start_time'],
                    "massage_end_time" => $booking['massage_end_time'],
                    "massage_duration" => $booking['massage_duration'],
                    "therapy_name" => $booking['therapy_name'],
                    "theropy_end_time" => $booking['theropy_end_time'],
                    "theropy_duration" => $booking['theropy_duration']
                ];
            }
            $allBookings[] =[
                "room_id" => $room_id,
                "room_name" => $room_name,
                "services" => $services
             ];
        }
        
        return $this->returnSuccess(__($this->successMsg['booking.overview']), $allBookings);
    }
    
    public function getAllMassages(Request $request)
    {
        $massages = Massage::with('timing','pricing')->where('shop_id',$request->shop_id)->get();
        
        return $this->returnSuccess(__($this->successMsg['massages']), $massages);
    }
    
    public function getAllTherapies(Request $request)
    {
        $therapies = Therapy::with('timing','pricing')->where('shop_id',$request->shop_id)->get();
        
        return $this->returnSuccess(__($this->successMsg['therapies']), $therapies);        
    }
    
    public function clientList(Request $request) {
        
        $clients = User::with('shop','city','country','reviews')->where('shop_id',$request->shop_id)->get();
        
        return $this->returnSuccess(__($this->successMsg['client.list']), $clients);
    }
    
    public function addClient(Request $request) {
        
        $user = new User();
        $data = $request->all();
        
        $checks = $user->validator($data);
        if ($checks->fails()) {
            return $this->returnError($checks->errors()->first(), NULL, true);
        }
        
        $client = $user->create($request->all());
        
        return $this->returnSuccess(__($this->successMsg['client.add']), $client);
    }
    
    public function searchClients(Request $request) {
        
        $search_val = $request->search_val;        
        $clients = User::where(['shop_id' => $request->shop_id, 'is_removed' => User::$notRemoved]);
        
        if (isset($search_val)) {
            if (is_numeric($search_val)) {
                $clients->where(function($query) use ($search_val) {
                    $query->where('id', (int) $search_val)
                            ->orWhere('tel_number', 'like', $search_val . '%');
                });
            } else {
                $clients->where('name', 'like', $search_val . '%');
            }
        }

        return $this->returnSuccess(__($this->successMsg['client.data.found']), $clients->get());
    }
    
    public function getTimeTable(Request $request) {
        
        $filter = $request->filter;
        $schedules = TherapistWorkingSchedule::with('therapistWorkingScheduleTimes', 'therapist:id,name,shop_id')                   
                        ->where(['is_working' => TherapistWorkingSchedule::WORKING, 'is_absent' => TherapistWorkingSchedule::NOT_ABSENT])
                        ->whereHas('therapist', function($q) use($request) {
                                $q->where('shop_id',$request->shop_id);
                        });
                        
        // 1 for yesterday ,2 for current month, 3 for last 7 days, 4 for last 14 days, 5 for last 30 days
        if (isset($filter)) {
            if ($filter == 1) {
                $schedules = $schedules->where('date', Carbon::yesterday());
            } else if ($filter == 2) {
                $schedules = $schedules->whereMonth('date', Carbon::now()->month);
            } else if ($filter == 3) {
                $schedules = $schedules->whereBetween('date', [Carbon::now()->subDays(7), Carbon::now()]);
            } else if ($filter == 4) {
                $schedules = $schedules->whereBetween('date', [Carbon::now()->subDays(14), Carbon::now()]);
            } else if ($filter == 5) {
                $schedules = $schedules->whereBetween('date', [Carbon::now()->subDays(30), Carbon::now()]);
            }
        } else {
            $schedules = $schedules->whereBetween('date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        }
        
        return $this->returnSuccess(__($this->successMsg['schedule.data.found']), $schedules->get());
    }
    
    public function startServiceTime(Request $request)
    {
        $model = new Therapist();
        $start = $model->serviceStart($request);

        return $this->returnSuccess(__($this->successMsg['booking.start']), $start);
    }
    
    public function endServiceTime(Request $request)
    {
        $model = new Therapist();
        $end = $model->serviceEnd($request);
        
        return $this->returnSuccess(__($this->successMsg['booking.end']), $end);
    }
    
    public function confirmBooking(Request $request) {
        
        $bookingMassage = BookingMassage::find($request->booking_massage_id);
        
        if(empty($bookingMassage)) {
            return $this->returnSuccess(__($this->successMsg['booking.not.found']));
        }
        $bookingMassage->update(['is_confirm' => BookingMassage::IS_CONFIRM]);
        
        return $this->returnSuccess(__($this->successMsg['booking.confirm']), $bookingMassage);
    }
    
    public function downgradeBooking(Request $request) {
        
        $bookingMassage = BookingMassage::find($request->booking_massage_id);
        
        if(empty($bookingMassage)) {
            return $this->returnSuccess(__($this->successMsg['booking.not.found']));
        }
        $bookingMassage->update(['is_confirm' => BookingMassage::IS_NOT_CONFIRM]);
        
        return $this->returnSuccess(__($this->successMsg['booking.downgrade']), $bookingMassage);
    }
    
    public function cancelAppointment(Request $request) {
        
        $booking = Booking::with('bookingInfo')->find($request->booking_id);        
        if(empty($booking)) {
            return $this->returnSuccess(__($this->successMsg['booking.not.found']));
        }
        foreach ($booking->bookingInfo as $key => $bookingInfo) {
            
            $bookingInfo->update(['is_cancelled' => BookingInfo::IS_CANCELLED, 'cancel_type' => $request->cancel_type, 'cancelled_reason' => $request->cancelled_reason]);
        }
        return $this->returnSuccess(__($this->successMsg['cancel.appointment']), $booking);
    }
}
