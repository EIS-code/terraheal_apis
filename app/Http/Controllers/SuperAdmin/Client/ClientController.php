<?php

namespace App\Http\Controllers\SuperAdmin\Client;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use App\User;
use App\TherapistUserRating;
use App\Booking;
use App\Therapist;

class ClientController extends BaseController
{   

    public $successMsg = [
        'no.data.found' => "Data not found !",
        'clients.get' => "Clients found successfully !",
        'client.get' => "Client found successfully !",
        'future.bookings.get' => "Future bookings found successfully !",
        'past.bookings.get' => "Past bookings found successfully !",
        'cancelled.bookings.get' => "Cancelled bookings found successfully !",
        'pending.bookings.get' => "Pending bookings found successfully !",
        'therapists.get' => "Client therapists found successfully !",
        'therapist.details' => "Client therapist details found successfully !",
    ];

    public function getAllClients() {

        $clients = User::all();
        if (!empty($clients)) {
            return $this->returnSuccess(__($this->successMsg['clients.get']), $clients);
        } else {
            return $this->returnSuccess(__($this->successMsg['no.data.found']), null);
        }
    }
    
    public function getInfo(Request $request) {
        
        $client = User::find($request->user_id);
        if(empty($client)) {
            return $this->returnSuccess(__($this->successMsg['no.data.found']));
        }
        $ratings = TherapistUserRating::where('user_id', $request->user_id)->get()->groupBy('type');
        
        $cnt = $rates = $avg = 0;
        $ratingData = [];
        if ($ratings->count() > 0) {
            foreach ($ratings as $i => $rating) {
                $first = $rating->first();
                foreach ($rating as $key => $rate) {
                    $rates += $rate->rating;
                    $cnt++;
                }
                $ratingData[] = [
                    'type' => $first->type,
                    'avg' => number_format($rates / $cnt, 2)
                ];
            }
        }
        $client['ratingData'] = $ratingData;
        return $this->returnSuccess(__($this->successMsg['client.get']), $client);
    }
    
    public function getFutureBookings(Request $request) {
        
        $request->request->add(['bookings_filter' => array(Booking::BOOKING_FUTURE)]);
        $bookingModel = new Booking();
        $futureBookings = $bookingModel->getGlobalQuery($request);
        
        return $this->returnSuccess(__($this->successMsg['future.bookings.get']), $futureBookings->toArray());
    }
    
    public function getPastBookings(Request $request) {
        
        $request->request->add(['bookings_filter' => array(Booking::BOOKING_PAST)]);
        $bookingModel = new Booking();
        $pastBookings = $bookingModel->getGlobalQuery($request);
        
        return $this->returnSuccess(__($this->successMsg['past.bookings.get']), $pastBookings->toArray());
    }
    
    public function getCancelledBookings(Request $request) {
        
        $request->request->add(['bookings_filter' => array(Booking::BOOKING_CANCELLED)]);
        $bookingModel = new Booking();
        $cancelledBookings = $bookingModel->getGlobalQuery($request);
        
        return $this->returnSuccess(__($this->successMsg['cancelled.bookings.get']), $cancelledBookings->toArray());
    }
    
    public function getPendingBookings(Request $request) {
        
        $request->request->add(['bookings_filter' => array(Booking::BOOKING_WAITING)]);
        $bookingModel = new Booking();
        $pendingBookings = $bookingModel->getGlobalQuery($request);
        
        return $this->returnSuccess(__($this->successMsg['pending.bookings.get']), $pendingBookings->toArray());
    }
    
    public function getPhoto($id) {
        
        $therapist = Therapist::find($id);
        return $therapist->profile_photo;
    }
    
    public function getTherapists(Request $request) {
        
        $bookingModel = new Booking();
        $bookings = $bookingModel->getGlobalQuery($request)->groupBy('therapist_id');
        
        $therapists = [];
        foreach ($bookings as $key => $booking) {
            $first = $booking->first();
            $therapists[] = [
                'therapist_id' => $first->therapist_id,
                'therapist_name' => $first->therapistName,
                'profile_photo' => $this->getPhoto($first->therapist_id),
            ];
        }
        return $this->returnSuccess(__($this->successMsg['therapists.get']), $therapists);
    }
    
    public function getTherapistDetails(Request $request) {
        $therapist = Therapist::find($request->therapist_id);

        if(empty($therapist)) {
            return $this->returnError(__($this->successMsg['no.data.found']));
        }
        $ratings = TherapistUserRating::where(['model_id' => $therapist->id, 'model' => 'App\Therapist'])->get();

        $cnt = $rates = $avg = 0;
        if ($ratings->count() > 0) {
            foreach ($ratings as $i => $rating) {
                $rates += $rating->rating;
                $cnt++;
            }
            $avg = $rates / $cnt;
        }
        $therapist['average'] = number_format($avg, 2);
        
        return $this->returnSuccess(__($this->successMsg['therapist.details']), $therapist);
    }
}
