<?php

namespace App\Http\Controllers\Shops\WaitingList;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use App\Booking;
use App\BookingMassage;
use App\UserPeople;
use App\Shop;
use DB;
use Carbon\Carbon;
use App\Therapist;
use App\User;
use App\TherapistWorkingSchedule;
use App\Pack;
use App\BookingInfo;
use App\Room;
use App\TherapistUserRating;
use App\Voucher;
use App\PackShop;
use App\VoucherShop;
use App\Libraries\CommonHelper;
use App\Service;
use App\ServicePricing;
use App\ServiceTiming;
use App\SessionType;

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
        'edit.booking' => 'Booking updated successfully',
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
        'recover.appointment' => "Appointment recovered successfully!",
        'packs.active' => 'Active packs found successfully',
        'packs.use' => 'Used packs found successfully',
        'vouchers.active' => 'Active vouchers found successfully',
        'vouchers.use' => 'Used vouchers found successfully',
        'data.found' => 'Data found successfully',
        'couple.users' => 'Please select more than one users!',
        'group.users' => 'Please select more than one users!',
        'single.users' => 'Please select only one user!',
        'couple.therapist.users' => 'Please select therapist!',
    ];

    public function ongoingMassage(Request $request) {

        $type = !empty($request->type) ? $request->type : Booking::BOOKING_TYPE_IMC;
        $request->request->add(['type' => $type, 'bookings_filter' => array(Booking::BOOKING_ONGOING)]);
        $bookingModel = new Booking();
        $ongoingMassages = $bookingModel->getGlobalQuery($request);

        return $this->returnSuccess(__($this->successMsg['ongoing.massage']), $ongoingMassages);
    }

    public function waitingMassage(Request $request) {

        $type = !empty($request->type) ? $request->type : Booking::BOOKING_TYPE_IMC;
        $request->request->add(['type' => $type, 'bookings_filter' => array(Booking::BOOKING_WAITING)]);
        $bookingModel = new Booking();
        $waitingMassages = $bookingModel->getGlobalQuery($request);

        return $this->returnSuccess(__($this->successMsg['waiting.massage']), $waitingMassages);
    }

    public function futureBooking(Request $request) {

        $type = !empty($request->type) ? $request->type : Booking::BOOKING_TYPE_IMC;
        $request->request->add(['type' => $type, 'bookings_filter' => array(Booking::BOOKING_FUTURE)]);
        if(!empty($request->date)){
            $date  = Carbon::createFromTimestampMs($request->date);
            $request->request->add(['date' => (new Carbon($date))->format('Y-m-d')]);
        }
        $bookingModel = new Booking();
        $futureBooking = $bookingModel->getGlobalQuery($request);

        return $this->returnSuccess(__($this->successMsg['future.booking']), $futureBooking);
    }

    public function completedBooking(Request $request) {

        $type = !empty($request->type) ? $request->type : Booking::BOOKING_TYPE_IMC;
        $request->request->add(['type' => $type, 'bookings_filter' => array(Booking::BOOKING_COMPLETED)]);
        if(!empty($request->date)){
            $date  = Carbon::createFromTimestampMs($request->date);
            $request->request->add(['date' => (new Carbon($date))->format('Y-m-d')]);
        }
        $bookingModel = new Booking();
        $completedBooking = $bookingModel->getGlobalQuery($request);
        
        foreach ($completedBooking as $key => $value) {
            
            $review = TherapistUserRating::where(['model_id' => $value['therapist_id'], 'model' => 'App\Therapist'])->count();
            if($review > 0) {
                $value['has_review'] = true;
            } else {
                $value['has_review'] = false;
            }
        }
        return $this->returnSuccess(__($this->successMsg['completed.booking']), $completedBooking);
    }

    public function cancelBooking(Request $request) {

        $type = !empty($request->type) ? $request->type : Booking::BOOKING_TYPE_IMC;
        $request->request->add(['type' => $type, 'bookings_filter' => array(Booking::BOOKING_CANCELLED)]);
        if(!empty($request->date)){
            $date  = Carbon::createFromTimestampMs($request->date);
            $request->request->add(['date' => (new Carbon($date))->format('Y-m-d')]);
        }
        $bookingModel = new Booking();
        $cancelBooking = $bookingModel->getGlobalQuery($request);

        return $this->returnSuccess(__($this->successMsg['cancelled.booking']), $cancelBooking);
    }
    
    public function pastBooking(Request $request) {

        $type = !empty($request->type) ? $request->type : Booking::BOOKING_TYPE_IMC;
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
        $pricing = ServicePricing::where(['service_timing_id' => $request->service_timing_id, 'service_id' => $request->service_id])->first();
            
        $newBooking['massage_date_time'] = $bookingMassage->massage_date_time;
        $newBooking['service_pricing_id'] = $pricing->id;
        $newBooking['price'] = $pricing->price;
        $newBooking['cost'] = $pricing->cost;
        $newBooking['origional_price'] = $pricing->price;
        $newBooking['origional_cost'] = $pricing->cost;
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
        
        $shopModel = new Shop();
        $bookingDetails = $shopModel->printBooking($request);
        
        if (!empty($bookingDetails['isError']) && !empty($bookingDetails['message'])) {
            return $this->returnError($bookingDetails['message'], NULL, true);
        }
        
        return $this->returnSuccess(__($this->successMsg['print.booking']), $bookingDetails);
    }
    
    public function getStartEndTime($booking) {

        if (!empty($booking->service_pricing_id)) {
            $pricing = ServicePricing::where('id', $booking->service_pricing_id)->first();
            $endtime = ServiceTiming::where('id', $pricing->service_timing_id)->first();
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
        $date  = Carbon::createFromTimestampMs($bookingMassage->massage_date_time);
        
        $bookings = BookingMassage::with('bookingInfo')->where('room_id',$request->room_id)
                        ->whereHas('bookingInfo', function($q) use($bookingMassage, $date) {
                            $q->where('massage_date_time',$date)->where('id', '!=', $bookingMassage->bookingInfo->id);
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
            $bookingModel = new Booking();

            $date = !empty($request->booking_date_time) ? Carbon::createFromTimestampMs($request->booking_date_time) : null;
            $bookingData = [
                'booking_type' => !empty($request->booking_type) ? $request->booking_type : Booking::BOOKING_TYPE_IMC,
                'special_notes' => $request->special_notes,
                'user_id' => $request->user_id,
                'shop_id' => $request->shop_id,
                'session_id' => $request->session_id,
                'pack_id' => !empty($request->pack_id) ? $request->pack_id : NULL,
                'booking_date_time' => $date,
                'book_platform' => !empty($request->book_platform) ? $request->book_platform : NULL
            ];
            $checks = $bookingModel->validator($bookingData);
            if ($checks->fails()) {
                return $this->returnError($checks->errors()->first(), NULL, true);
            }
            $newBooking = Booking::create($bookingData);
            $isPack = !empty($request->pack_id) ? $request->pack_id : NULL;
            $bookingInfo = $shopModel->addBookingInfo($request, $newBooking, NULL, $isPack);
            if (!empty($bookingInfo['isError']) && !empty($bookingInfo['message'])) {
                return $this->returnError($bookingInfo['message'], NULL, true);
            }

            if (!empty($request->pack_id)) {

                $pack = Pack::with('services')->where('id', $request->pack_id)->first();
                if (empty($pack)) {
                    return ['isError' => true, 'message' => 'Pack not found'];
                }
                foreach ($pack->services as $key => $service) {
                    $service = $shopModel->addBookingMassages($service, $bookingInfo, $request, NULL);
                    if (!empty($service['isError']) && !empty($service['message'])) {
                        return $this->returnError($service['message'], NULL, true);
                    }
                }
            } else {
                if (count($request->services) > 0) {
                    foreach ($request->services as $key => $value) {
                        $service = $shopModel->addBookingMassages($value, $bookingInfo, $request, NULL);
                        if (!empty($service['isError']) && !empty($service['message'])) {
                            return $this->returnError($service['message'], NULL, true);
                        }
                    }
                }
                if (!empty($request->users)) {
                    $userModel = new UserPeople();
                    foreach ($request->users as $key => $user) {
                        $user_people = [
                            'name' => $user['name'],
                            'age' => $user['age'],
                            'gender' => $user['gender'],
                            'user_gender_preference_id' => $user['gender_preference'],
                            'user_id' => $request->user_id
                        ];
                        $checks = $userModel->validator($user_people);
                        if ($checks->fails()) {
                            return $this->returnError($checks->errors()->first(), NULL, true);
                        }
                        $newUser = UserPeople::create($user_people);

                        $newUser['therapist_id'] = $user['therapist_id'];
                        $newUser['notes_of_injuries'] = $user['notes_of_injuries'];

                        $bookingInfo = $shopModel->addBookingInfo($request, $newBooking, $newUser, NULL);
                        if (!empty($bookingInfo['isError']) && !empty($bookingInfo['message'])) {
                            return $this->returnError($bookingInfo['message'], NULL, true);
                        }

                        if (count($user['services']) > 0) {
                            foreach ($user['services'] as $key => $value) {
                                $service = $shopModel->addBookingMassages($value, $bookingInfo, $request, $user);
                                if (!empty($service['isError']) && !empty($service['message'])) {
                                    return $this->returnError($service['message'], NULL, true);
                                }
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
    
    public function addBooking(Request $request) {

        DB::beginTransaction();
        try {
            if (!empty($request->users)) {
                $shopModel = new Shop();
                $bookingModel = new Booking();
                if($request->session_id == SessionType::SINGLE) {
                    if(count($request->users) > 1) {
                        return $this->returnError(__($this->successMsg['single.users']));
                    }
                }
                if($request->session_id == (SessionType::COUPLE || SessionType::COUPLE_WITH_THERAPIST || SessionType::COUPLE_BACK_TO_BACK)) {
                    if(count($request->users) < 1) {
                        return $this->returnError(__($this->successMsg['couple.users']));
                    }
                }
                if($request->session_id == SessionType::GROUP) {
                    if(count($request->users) < 1) {
                        return $this->returnError(__($this->successMsg['group.users']));
                    }
                }

                foreach ($request->users as $key => $user) {
                    if ($request->session_id == SessionType::COUPLE_WITH_THERAPIST) {
                        if (!isset($user['therapist_id'])) {
                            return $this->returnError(__($this->successMsg['couple.therapist.users']));
                        }
                    }

                    $date = !empty($user['booking_date_time']) ? Carbon::createFromTimestampMs($user['booking_date_time']) : Carbon::now();
                    $bookingData = [
                        'booking_type' => !empty($request->booking_type) ? $request->booking_type : Booking::BOOKING_TYPE_IMC,
                        'special_notes' => $request->special_notes,
                        'user_id' => $user['user_id'],
                        'shop_id' => $request->shop_id,
                        'session_id' => $request->session_id,
                        'booking_date_time' => $date,
                        'book_platform' => !empty($request->book_platform) ? $request->book_platform : NULL
                    ];
                    $checks = $bookingModel->validator($bookingData);
                    if ($checks->fails()) {
                        return $this->returnError($checks->errors()->first(), NULL, true);
                    }
                    $newBooking = Booking::create($bookingData);
                    $user['id'] = $user['user_id'];
                    $bookingInfo = $shopModel->addBookingInfo($request, $newBooking, collect($user), NULL);
                    if (!empty($bookingInfo['isError']) && !empty($bookingInfo['message'])) {
                        return $this->returnError($bookingInfo['message'], NULL, true);
                    }
                    if (count($user['services']) > 0) {
                        foreach ($user['services'] as $key => $value) {
                            $service = $shopModel->addBookingMassages($value, $bookingInfo, $request, $user);
                            if (!empty($service['isError']) && !empty($service['message'])) {
                                return $this->returnError($service['message'], NULL, true);
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
        $type = !empty($request->type) ? $request->type : Booking::BOOKING_TYPE_IMC;
        $date = !empty($request->date) ? (new Carbon($date))->format('Y-m-d') : Carbon::now()->format('Y-m-d');
        $request->request->add(['type' => $type, 'date' => $date]);
        
        $bookingModel = new Booking();
        $bookingOverviews = $bookingModel->getGlobalQuery($request)->whereNotNull('therapist_id')->groupBy('therapist_id');
        
        $allBookings = [];
        foreach ($bookingOverviews as $key => $bookings) {
            $therapist_id = $bookings[0]['therapist_id'];
            $therapist_name = $bookings[0]['therapistName'];
            $services = [];
            foreach ($bookings as $key => $booking) {
                $services[] = [
                    "service_day_name" => $booking['massage_day_name'],
                    "massage_date_time" => $booking['massage_date_time'],
                    "service_english_name" => $booking['service_english_name'],
                    "service_portugese_name" => $booking['service_portugese_name'],
                    "massage_start_time" => $booking['massage_start_time'],
                    "massage_end_time" => $booking['massage_end_time'],
                    "massage_duration" => $booking['massage_duration'],
                    "session_Id" => $booking['sessionId'],
                    "session_type" => $booking['session_type']
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
        $date = !empty($request->date) ? (new Carbon($date))->format('Y-m-d') : Carbon::now()->format('Y-m-d');
        $request->request->add(['date' => $date]);
        
        $bookingModel = new Booking();
        $roomOccupied = $bookingModel->getGlobalQuery($request)->whereNotNull('room_id')->groupBy('room_id');
        
        $allBookings = [];
        if(!empty($roomOccupied)) {
            foreach ($roomOccupied as $key => $bookings) {
                $room_id = $bookings[0]['room_id'];
                $room_name = $bookings[0]['roomName'];
                $total_beds = $bookings[0]['totalBeds'];
                $services = [];
                foreach ($bookings as $key => $booking) {
                    $services[] = [
                        "service_day_name" => $booking['massage_day_name'],
                        "massage_date_time" => $booking['massage_date_time'],
                        "service_english_name" => $booking['service_english_name'],
                        "service_portugese_name" => $booking['service_portugese_name'],
                        "massage_start_time" => $booking['massage_start_time'],
                        "massage_end_time" => $booking['massage_end_time'],
                        "massage_duration" => $booking['massage_duration'],
                        "session_id" => $booking['sessionId'],
                        "session_type" => $booking['session_type']
                    ];
                }
                $allBookings[] =[
                    "room_id" => $room_id,
                    "room_name" => $room_name,
                    "total_beds" => $total_beds,
                    "services" => $services
                 ];
            }
        }
        
        return $this->returnSuccess(__($this->successMsg['booking.overview']), $allBookings);
    }
    
    public function getAllMassages(Request $request)
    {
        $request->request->add(['type' => Service::MASSAGE, 'isGetAll' => true]);
        $massages = CommonHelper::getAllService($request);
        return $this->returnSuccess(__($this->successMsg['massages']), $massages);
    }
    
    public function getAllTherapies(Request $request)
    {
        $request->request->add(['type' => Service::THERAPY, 'isGetAll' => true]);
        $therapies = CommonHelper::getAllService($request);
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
        
        if (!empty($search_val)) {
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
        
        $date  = Carbon::createFromTimestampMs($request->date);
        $date = !empty($request->date) ? $date : Carbon::now();
        
        $schedules = TherapistWorkingSchedule::with('therapistWorkingScheduleTime', 'therapist:id,name,shop_id')                   
                        ->whereMonth('date', $date->month)
                        ->whereYear('date', $date->year)
                        ->whereHas('therapist', function($q) use($request) {
                                $q->where('shop_id',$request->shop_id);
                        });
                        
//        // 1 for yesterday ,2 for current month, 3 for last 7 days, 4 for last 14 days, 5 for last 30 days
//        if (!empty($filter)) {
//            if ($filter == 1) {
//                $schedules = $schedules->where('date', Carbon::yesterday());
//            } else if ($filter == 2) {
//                $schedules = $schedules->whereMonth('date', Carbon::now()->month);
//            } else if ($filter == 3) {
//                $schedules = $schedules->whereBetween('date', [Carbon::now()->subDays(7), Carbon::now()]);
//            } else if ($filter == 4) {
//                $schedules = $schedules->whereBetween('date', [Carbon::now()->subDays(14), Carbon::now()]);
//            } else if ($filter == 5) {
//                $schedules = $schedules->whereBetween('date', [Carbon::now()->subDays(30), Carbon::now()]);
//            }
//        } else {
//            $schedules = $schedules->whereBetween('date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
//        }                     
        return $this->returnSuccess(__($this->successMsg['schedule.data.found']), $schedules->get());
    }
    
    public function startServiceTime(Request $request)
    {
        $model = new Therapist();
        $start = $model->serviceStart($request);

        if (!empty($start['isError']) && !empty($start['message'])) {
            return $this->returnError($start['message'], NULL, true);
        }
        
        return $this->returnSuccess(__($this->successMsg['booking.start']), $start);
    }
    
    public function endServiceTime(Request $request)
    {
        $model = new Therapist();
        $end = $model->serviceEnd($request);
        
        if (!empty($end['isError']) && !empty($end['message'])) {
            return $this->returnError($end['message'], NULL, true);
        }
        
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
    
    public function recoverAppointment(Request $request) {
        
        $booking = Booking::with('bookingInfo')->find($request->booking_id);        
        if(empty($booking)) {
            return $this->returnSuccess(__($this->successMsg['booking.not.found']));
        }
        foreach ($booking->bookingInfo as $key => $bookingInfo) {
            
            $bookingInfo->update(['is_cancelled' => BookingInfo::IS_NOT_CANCELLED]);
        }
        return $this->returnSuccess(__($this->successMsg['recover.appointment']), $booking);
    }
    
    public function getActivePacks(Request $request) {
        
        $pageNumber = !empty($request->page_number) ? $request->page_number : 1;
        $packs = Pack::with('shopsPacks')->whereDate('expired_date', '>=', Carbon::now()->format('Y-m-d'))
                        ->whereHas('shopsPacks', function($q) use($request) {
                            $q->where('shop_id', $request->shop_id);
                        })->paginate(10, ['*'], 'page', $pageNumber);
        
        if (count($packs) > 0) {
            return $this->returnSuccess(__($this->successMsg['packs.active']), $packs);
        } else {
            return $this->returnSuccess(__($this->successMsg['not.found']), null);
        }
    }
    
    public function getUsedPacks(Request $request) {
        
        $pageNumber = !empty($request->page_number) ? $request->page_number : 1;
        $packModel              = new Pack();
        $packShopModel          = new PackShop();
        
        $packs = $packModel->getPackQuery()
                ->where($packShopModel::getTableName() . '.shop_id', $request->shop_id)
                ->whereDate($packModel::getTableName() . '.expired_date', '>=', Carbon::now()->format('Y-m-d'))
                ->paginate(10, ['*'], 'page', $pageNumber);

        if (count($packs) > 0) {
            return $this->returnSuccess(__($this->successMsg['packs.use']), $packs);
        } else {
            return $this->returnSuccess(__($this->successMsg['not.found']), null);
        }
    }
    
    public function getUsedVouchers(Request $request) {
        
        $pageNumber = !empty($request->page_number) ? $request->page_number : 1;
        $voucherModel       = new Voucher();
        $voucherShopModel   = new VoucherShop();
        
        $vouchers = $voucherModel->getVoucherQuery()
                ->where($voucherShopModel::getTableName() . '.shop_id', $request->shop_id)
                ->whereDate($voucherModel::getTableName() . '.expired_date', '>=', Carbon::now()->format('Y-m-d'))
                ->paginate(10, ['*'], 'page', $pageNumber);

        if (count($vouchers) > 0) {
            return $this->returnSuccess(__($this->successMsg['vouchers.use']), $vouchers);
        } else {
            return $this->returnSuccess(__($this->successMsg['not.found']), null);
        }
    }
    
    public function getActiveVouchers(Request $request) {
        
        $pageNumber = !empty($request->page_number) ? $request->page_number : 1;
        $vouchers = Voucher::with('shopsVouchers')->whereDate('expired_date', '>=', Carbon::now()->format('Y-m-d'))
                        ->whereHas('shopsVouchers', function($q) use($request) {
                            $q->where('shop_id', $request->shop_id);
                        })->paginate(10, ['*'], 'page', $pageNumber);
        
        if (count($vouchers) > 0) {
            return $this->returnSuccess(__($this->successMsg['vouchers.active']), $vouchers);
        } else {
            return $this->returnSuccess(__($this->successMsg['not.found']), null);
        }
    }
    
    public function searchUsedPacks(Request $request) {
        
        $pageNumber = !empty($request->page_number) ? $request->page_number : 1;
        $packModel              = new Pack();
        $packShopModel          = new PackShop();
        $userModel              = new User();
        
        $packs = $packModel->getPackQuery()
                ->where($packShopModel::getTableName() . '.shop_id', $request->shop_id)
                ->whereDate($packModel::getTableName() . '.expired_date', '>=', Carbon::now()->format('Y-m-d'));
             
        $search_val = $request->search_val;
        if(!empty($search_val)) {
            $packs->where(function($query) use ($search_val, $packModel, $userModel) {
                    $query->where($packModel::getTableName().'.name', 'like', $search_val.'%')
                            ->orWhere($userModel::getTableName().'.name', 'like', $search_val.'%');
                });
        }
        return $packs->paginate(10, ['*'], 'page', $pageNumber);
    }
    
    public function searchActivePacks(Request $request) {
        
        $pageNumber = !empty($request->page_number) ? $request->page_number : 1;
        $packModel = new Pack();
        $packShopModel = new PackShop();
        
        $packs = $packModel
                ->select(DB::RAW($packShopModel::getTableName() . '.*,' .$packModel::getTableName() . '.*' ))
                ->join($packShopModel::getTableName(), $packShopModel::getTableName() . '.pack_id', '=', $packModel::getTableName() . '.id')
                ->where($packShopModel::getTableName() . '.shop_id', $request->shop_id)
                ->whereDate($packModel::getTableName() . '.expired_date', '>=', Carbon::now()->format('Y-m-d'));
             
        $search_val = $request->search_val;
        if(!empty($search_val)) {
            $packs->where($packModel::getTableName().'.name', 'like', $search_val.'%');
        }
        return $packs->paginate(10, ['*'], 'page', $pageNumber);
    }
    public function searchPacks(Request $request) {
        
        $filter = !empty($request->filter) ? $request->filter : Pack::ACTIVE;
        
        $packs = collect();
        
        if ($filter == Pack::ACTIVE) {
            $packs = $this->searchActivePacks($request);
        } 
        if($filter == Pack::USED) {
            $packs = $this->searchUsedPacks($request);
        }
        if (count($packs) > 0) {
            return $this->returnSuccess(__($this->successMsg['data.found']), $packs);
        } else {
            return $this->returnSuccess(__($this->successMsg['not.found']), null);
        }
    }
    
    public function searchUsedVoucher(Request $request) {
        
        $pageNumber = !empty($request->page_number) ? $request->page_number : 1;
        $voucherModel       = new Voucher();
        $voucherShopModel   = new VoucherShop();
        $userModel          = new User();
        
        $vouchers = $voucherModel->getVoucherQuery()
                ->where($voucherShopModel::getTableName() . '.shop_id', $request->shop_id)
                ->whereDate($voucherModel::getTableName() . '.expired_date', '>=', Carbon::now()->format('Y-m-d'));

        $search_val = $request->search_val;
        if(!empty($search_val)) {
            $vouchers->where(function($query) use ($search_val, $voucherModel, $userModel) {
                    $query->where($voucherModel::getTableName().'.name', 'like', $search_val.'%')
                            ->orWhere($userModel::getTableName().'.name', 'like', $search_val.'%');
                });
        }
        return $vouchers->paginate(10, ['*'], 'page', $pageNumber);
    }
    
    public function searchActiveVoucher(Request $request) {
        
        $pageNumber = !empty($request->page_number) ? $request->page_number : 1;
        $voucherModel       = new Voucher();
        $voucherShopModel   = new VoucherShop();
        
        $vouchers = $voucherModel
                ->select(DB::RAW($voucherShopModel::getTableName() . '.*,' .$voucherModel::getTableName() . '.*'))
                ->join($voucherShopModel::getTableName(), $voucherShopModel::getTableName() . '.voucher_id', '=', $voucherModel::getTableName() . '.id')
                ->where($voucherShopModel::getTableName() . '.shop_id', $request->shop_id)
                ->whereDate($voucherModel::getTableName() . '.expired_date', '>=', Carbon::now()->format('Y-m-d'));

        $search_val = $request->search_val;
        if (!empty($search_val)) {
            $vouchers->where($voucherModel::getTableName().'.name', 'like', $search_val.'%');
        }
        return $vouchers->paginate(10, ['*'], 'page', $pageNumber);
    }
    
    public function searchVouchers(Request $request) {

        $filter = !empty($request->filter) ? $request->filter : Voucher::ACTIVE;
        
        $vouchers = collect();
        
        if ($filter == Voucher::ACTIVE) {
            $vouchers = $this->searchActiveVoucher($request);
        } 
        if($filter == Voucher::USED) {
            $vouchers = $this->searchUsedVoucher($request);
        }
        if (count($vouchers) > 0) {
            return $this->returnSuccess(__($this->successMsg['data.found']), $vouchers);
        } else {
            return $this->returnSuccess(__($this->successMsg['not.found']), null);
        }
    }
    
    public function editBooking(Request $request) {

        DB::beginTransaction();
        try {
            $shopModel = new Shop();
            $booking = Booking::find($request->booking_id);
            $booking->update(['special_notes' => $request->note]);
            $bookingInfoModel = new BookingInfo();
            $bookingInfo = BookingInfo::where('booking_id', $booking->id)->whereNull('user_people_id')->first();
            $data = [
                'booking_currency_id' => $bookingInfo->booking_currency_id,
                'shop_currency_id' => $bookingInfo->shop_currency_id,
                'booking_id' => $booking->id,
                'therapist_id' => $request->therapist_id
            ];
            $checks = $bookingInfoModel->validator($data);
            if ($checks->fails()) {
                return $this->returnError($checks->errors()->first(), NULL, true);
            }
            $bookingInfo->update(['therapist_id' => $request->therapist_id]);
            
            if (count($request->services) > 0) {
                foreach ($request->services as $key => $value) {
                    $service = $shopModel->addBookingMassages($value, $bookingInfo, $request, NULL);
                    if (!empty($service['isError']) && !empty($service['message'])) {
                        return $this->returnError($service['message'], NULL, true);
                    }
                }
            }
            if (!empty($request->users)) {
                foreach ($request->users as $key => $user) {
                    $bookingInfo = BookingInfo::where(['booking_id' => $booking->id ,'user_people_id' => $user['id']])->first();
                    $data = [
                        'booking_currency_id' => $bookingInfo->booking_currency_id,
                        'shop_currency_id' => $bookingInfo->shop_currency_id,
                        'booking_id' => $booking->id,
                        'therapist_id' => $user['therapist_id']
                    ];
                    $checks = $bookingInfoModel->validator($data);
                    if ($checks->fails()) {
                        return $this->returnError($checks->errors()->first(), NULL, true);
                    }
                    $bookingInfo->update(['therapist_id' => $user['therapist_id']]);
                    if (count($user['services']) > 0) {
                        foreach ($user['services'] as $key => $value) {
                            $service = $shopModel->addBookingMassages($value, $bookingInfo, $request, $user);
                            if (!empty($service['isError']) && !empty($service['message'])) {
                                return $this->returnError($service['message'], NULL, true);
                            }
                        }
                    }
                }
            }
            DB::commit();
            return $this->returnSuccess(__($this->successMsg['edit.booking']));
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollback();
            throw $e;
        }
    }
}