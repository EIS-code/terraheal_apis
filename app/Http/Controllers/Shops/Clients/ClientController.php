<?php

namespace App\Http\Controllers\Shops\Clients;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use App\User;
use App\UserPeople;
use App\Booking;
use App\UserAddress;
use App\TherapyQuestionnaire;
use App\TherapistUserRating;
use App\UsersForgottenObjects;
use App\Shop;
use App\Room;

class ClientController extends BaseController {

    public $successMsg = [
        'client.data.found' => 'Client data found successfully',
        'client.future.booking' => 'Client future booking found successfully',
        'client.past.booking' => 'Client past booking found successfully',
        'client.cancelled.booking' => 'Client cancelled booking found successfully',
        'client.forgot.object' => 'Client forgotten object added successfully',
        'client.forgot.object.return' => 'Client forgotten object returned successfully',
        'client.email.send' => 'Email sent successfully',
    ];        
    
    public function searchClients(Request $request) {
        
        $pageNumber = isset($request->page_number) ? $request->page_number : 1;
        $search_val = $request->search_val;
        
        $clients = User::where(['shop_id' => $request->shop_id, 'is_removed' => User::$notRemoved]);
        
        if(isset($request->name_filter))
        {
            $clients->where('name', 'like', $request->name_filter.'%');
        }
        if(isset($search_val))
        {
            if(is_numeric($search_val)) {
                $clients->where('id', $search_val);
            } else {
                $clients->orWhere('name', 'like', $search_val);
                $clients->orWhere('email', $search_val);
            }
        }
        $clientData = $clients->paginate(10, ['*'], 'page', $pageNumber);
        return $this->returnSuccess(__($this->successMsg['client.data.found']), $clientData);
    }
    
    public function clientDetails(Request $request) {
        
        $client = User::with('shop:id,name','city:id,name','country:id,name')
                ->where(['shop_id' => $request->shop_id,'id' => $request->user_id])
                ->first();
        
        $bookingModel = new Booking();
        
        $lastvisited = $bookingModel->where(['shop_id' => $request->shop_id,'id' => $request->user_id])->get()->last();        
        $totalAppointments = $bookingModel->getGlobalQuery($request)->groupBy('booking_id')->count();
        $recipient = UserPeople::where('user_id',$request->user_id)->get()->count();
        $addresses = UserAddress::where('user_id',$request->user_id)->get()->count();
        $therapists = $bookingModel->getGlobalQuery($request)->groupBy('therapist_id')->count();
        $is_verified = false;
        if($client->is_email_verified == 1 && $client->is_mobile_verified == 1 && $client->is_document_verified == 1) {
            $is_verified = true;
        }        
        $questionnaries = TherapyQuestionnaire::with('questionnaireAnswer')->get();
        
        $ratings = TherapistUserRating::with('therapist:id,name')->where('user_id',$request->user_id);
        $avg_rating = $ratings->avg('rating');
        
        $ratings = $ratings->get()->groupBy('type');
        
        $ratingData = [];
        foreach ($ratings as $key => $rating) {
            $sum = 0; $cnt = 0;
            foreach ($rating as $key => $value) {
                $cnt += 1;
                $sum += $value->rating;
            }
            $avg_rate = $sum / $cnt;
            $rating['avg_rating'] = $avg_rate;
            array_push($ratingData, $rating);
        }
//        dd($ratingData);
        
        $request->request->add(['bookings_filter' => Booking::BOOKING_CANCELLED]);
        $noShow = $bookingModel->getGlobalQuery($request)->groupBy('booking_id')->count();
               
        return $this->returnSuccess(__($this->successMsg['client.data.found']), 
                ["client" => $client, "totalAppointments" => $totalAppointments, "noShow" => $noShow, "registeredAt" => $client->created_at,
                 "lastVisited" => $lastvisited->created_at, "recipient" => $recipient, "addresses" => $addresses, "therapists" => $therapists,
                 "is_verified" => $is_verified, "questionnaries" => $questionnaries, "ratings" => $ratingData, "avg_rating" => $avg_rating ]);
    }
     
    public function getFutureBookings(Request $request) {
        
        $type = isset($request->type) ? $request->type : Booking::BOOKING_TYPE_IMC;
        $request->request->add(['type' => $type,'bookings_filter' => Booking::BOOKING_FUTURE,'user_id' => $request->user_id]);
        $bookingModel = new Booking();
        $futureBookings = $bookingModel->getGlobalQuery($request)->groupBy('booking_id');
               
        return $this->returnSuccess(__($this->successMsg['client.future.booking']), $futureBookings);
    }
    
    public function getPastBookings(Request $request) {
        
        $type = isset($request->type) ? $request->type : Booking::BOOKING_TYPE_IMC;
        $request->request->add(['type' => $type,'bookings_filter' => Booking::BOOKING_PAST,'user_id' => $request->user_id]);
        $bookingModel = new Booking();
        $pastBookings = $bookingModel->getGlobalQuery($request)->groupBy('booking_id');
               
        return $this->returnSuccess(__($this->successMsg['client.past.booking']), $pastBookings);
    }
    
    public function getCancelledBookings(Request $request) {
        
        $type = isset($request->type) ? $request->type : Booking::BOOKING_TYPE_IMC;
        $request->request->add(['type' => $type,'bookings_filter' => Booking::BOOKING_CANCELLED,'user_id' => $request->user_id]);
        $bookingModel = new Booking();
        $cancelledBookings = $bookingModel->getGlobalQuery($request)->groupBy('booking_id');
               
        return $this->returnSuccess(__($this->successMsg['client.cancelled.booking']), $cancelledBookings);
    }
    
    public function addForgotObject(Request $request) {
        
        $object = UsersForgottenObjects::create($request->all());
        return $this->returnSuccess(__($this->successMsg['client.forgot.object']), $object);
        
    }
    public function returnForgotObject(Request $request) {
        
        $returnObject = UsersForgottenObjects::find($request->object_id);
        $returnObject->update($request->all());
        return $this->returnSuccess(__($this->successMsg['client.forgot.object.return']), $returnObject);
        
    }
    
    public function sendEmailToClient(Request $request) {
        
        $user = User::find($request->user_id);
        $shop = Shop::find($request->shop_id);
        $room = Room::find($request->room_id);
        
        $view = "forgotObject";
        $to = $user->email;
        $subject = "Forgot Object";
        $body = array(
            'shop' => $shop->name,
            'room' => $room->name,
            'object' => $request->forgotten_object
        );

        $send = $this->sendMail($view, $to, $subject, $body);
        
        return $this->returnSuccess(__($this->successMsg['client.forgot.object.return']), $send);
    }
}
