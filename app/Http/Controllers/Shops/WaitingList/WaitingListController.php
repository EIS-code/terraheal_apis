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
        'client.add' => 'Client added successfully'
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
    public function pastBooking(Request $request) {

        $type = isset($request->type) ? $request->type : Booking::BOOKING_TYPE_IMC;
        $request->request->add(['type' => $type, 'bookings_filter' => Booking::BOOKING_PAST]);
        $bookingModel = new Booking();
        $pastBooking = $bookingModel->getGlobalQuery($request)->groupBy('booking_id');

        return $this->returnSuccess(__($this->successMsg['past.booking']), $pastBooking);
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
    
    public function addNewBooking(Request $request) {

        DB::beginTransaction();
        try {
            $shopModel = new Shop();

            $bookingData = [
                'booking_type' => $request->booking_type,
                'special_notes' => $request->special_notes,
                'user_id' => $request->user_id,
                'shop_id' => $request->shop_id,
                'session_id' => $request->session_id
            ];
            $newBooking = Booking::create($bookingData);

            $bookingInfo = $shopModel->addBookingInfo($request, $newBooking, NULL);

            foreach ($request->massages as $key => $massage) {

                $shopModel->addBookingMassages($massage, $bookingInfo, $request, NULL);
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

                    $bookingInfo = $shopModel->addBookingInfo($request, $newBooking, $newUser);

                    foreach ($user['massages'] as $key => $massage) {

                        $shopModel->addBookingMassages($massage, $bookingInfo, $request, $user);
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
        $bookingOverviews = $bookingModel->getGlobalQuery($request)->groupBy('therapist_id');
        
        return $this->returnSuccess(__($this->successMsg['booking.overview']), $bookingOverviews);
    }
    public function roomOccupation(Request $request) {
        
        $filterDate = Carbon::parse($request->date);
        
        $type = isset($request->type) ? $request->type : Booking::BOOKING_TYPE_IMC;
        $date = isset($request->date) ? $filterDate->format('Y-m-d') : Carbon::now()->format('Y-m-d');
        $request->request->add(['type' => $type, 'date' => $date]);
        
        $bookingModel = new Booking();
        $roomOccupied = $bookingModel->getGlobalQuery($request)->whereNotNull('room_id')->groupBy('room_id');
        
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
}
