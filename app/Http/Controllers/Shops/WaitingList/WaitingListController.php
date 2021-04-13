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
        'not.found' => "Data not found"
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
        $bookingModel = new Booking();
        $waitingMassages = $bookingModel->getGlobalQuery($request);

        return $this->returnSuccess(__($this->successMsg['waiting.massage']), $waitingMassages);
    }

    public function futureBooking(Request $request) {

        $type = isset($request->type) ? $request->type : Booking::BOOKING_TYPE_IMC;
        $request->request->add(['type' => $type, 'bookings_filter' => array(Booking::BOOKING_FUTURE)]);
        $bookingModel = new Booking();
        $futureBooking = $bookingModel->getGlobalQuery($request);

        return $this->returnSuccess(__($this->successMsg['future.booking']), $futureBooking);
    }

    public function completedBooking(Request $request) {

        $type = isset($request->type) ? $request->type : Booking::BOOKING_TYPE_IMC;
        $request->request->add(['type' => $type, 'bookings_filter' => array(Booking::BOOKING_COMPLETED)]);
        $bookingModel = new Booking();
        $completedBooking = $bookingModel->getGlobalQuery($request);

        return $this->returnSuccess(__($this->successMsg['completed.booking']), $completedBooking);
    }

    public function cancelBooking(Request $request) {

        $type = isset($request->type) ? $request->type : Booking::BOOKING_TYPE_IMC;
        $request->request->add(['type' => $type, 'bookings_filter' => array(Booking::BOOKING_CANCELLED)]);
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
    
    public function addNewBooking(Request $request) {

        DB::beginTransaction();
        try {
            $shopModel = new Shop();
            
            $bookingData = [
                'booking_type' => $request->booking_type,
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
        
        $filterDate = Carbon::parse($request->date);
        
        $type = isset($request->type) ? $request->type : Booking::BOOKING_TYPE_IMC;
        $date = isset($request->date) ? $filterDate->format('Y-m-d') : Carbon::now()->format('Y-m-d');
        $request->request->add(['type' => $type, 'date' => $date]);
        
        $bookingModel = new Booking();
        $bookingOverviews = $bookingModel->getGlobalQuery($request);
        
        return $this->returnSuccess(__($this->successMsg['booking.overview']), $bookingOverviews);
    }
    
    public function roomOccupation(Request $request) {
        
        $filterDate = Carbon::parse($request->date);
        
        $type = isset($request->type) ? $request->type : Booking::BOOKING_TYPE_IMC;
        $date = isset($request->date) ? $filterDate->format('Y-m-d') : Carbon::now()->format('Y-m-d');
        $request->request->add(['type' => $type, 'date' => $date]);
        
        $bookingModel = new Booking();
        $roomOccupied = $bookingModel->getGlobalQuery($request)->whereNotNull('room_id');
        
        return $this->returnSuccess(__($this->successMsg['booking.overview']), $roomOccupied);
    }
    
    public function getAllMassages(Request $request)
    {
        $massages = Massage::with('timing','pricing')->where('shop_id',$request->shop_id)->get();
        
        return $this->returnSuccess(__($this->successMsg['massages']), $massages);
    }
    
    public function getAllTherapies(Request $request)
    {
        $therapies = Therapy::where('shop_id',$request->shop_id)->get();
        
        return $this->returnSuccess(__($this->successMsg['therapies']), $therapies);        
    }
    
    public function clientList(Request $request) {
        
        $clients = User::with('shop','city','country','reviews')->where('shop_id',$request->shop_id)->get();
        
        return $this->returnSuccess(__($this->successMsg['client.list']), $clients);
    }
    
    public function addClient(Request $request) {
        
        $client = User::create($request->all());
        
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
}
