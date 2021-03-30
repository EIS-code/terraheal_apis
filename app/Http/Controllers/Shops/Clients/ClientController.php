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
use App\Therapist;
use App\UserMassagePreferences;
use DB;
use App\BookingInfo;
use App\BookingMassage;

class ClientController extends BaseController {

    public $successMsg = [
        'client.data.found' => 'Client data found successfully',
        'client.future.booking' => 'Client future booking found successfully',
        'client.past.booking' => 'Client past booking found successfully',
        'client.cancelled.booking' => 'Client cancelled booking found successfully',
        'client.forgot.object' => 'Client forgotten object added successfully',
        'client.forgot.object.return' => 'Client forgotten object returned successfully',
        'client.email.send' => 'Email sent successfully',
        'client.rating.update' => 'Client ratings updated successfully',
    ];        
    
    public function searchClients(Request $request) {
        
        $userModel = new User();
        $bookingModel = new Booking();
        $bookingInfoModel = new BookingInfo();
        $bookingMassageModel = new BookingMassage();
        
        $userModel->setMysqlStrictFalse();
        
        $clients = $userModel->select(DB::RAW($userModel::getTableName() .'.*', $bookingModel::getTableName().'.id',
                $bookingModel::getTableName().'.booking_type', $bookingModel::getTableName().'.user_id',
                $bookingInfoModel::getTableName().'.id', $bookingMassageModel::getTableName().'.massage_timing_id',
                $bookingMassageModel::getTableName().'.massage_prices_id', $bookingMassageModel::getTableName().'.booking_info_id',
                $bookingMassageModel::getTableName().'.therapy_timing_id',$bookingMassageModel::getTableName().'.therapy_prices_id'))                
                ->leftJoin($bookingModel::getTableName(), $bookingModel::getTableName().'.user_id', '=', $userModel::getTableName().'.id')
                ->leftJoin($bookingInfoModel::getTableName(), $bookingInfoModel::getTableName().'.booking_id', '=', $bookingModel::getTableName().'.id')
                ->leftJoin($bookingMassageModel::getTableName(), $bookingMassageModel::getTableName().'.booking_info_id', '=', $bookingInfoModel::getTableName().'.id')
                ->where(['users.shop_id' => $request->shop_id, 'users.is_removed' => User::$notRemoved]);
                        
        
        $pageNumber = isset($request->page_number) ? $request->page_number : 1;
        $search_val = $request->search_val;
        
        if(isset($request->name_filter))
        {
            $clients->where($userModel::getTableName().'.name', 'like', $request->name_filter.'%');
        }
        if(isset($search_val))
        {
            if(is_numeric($search_val)) {
                $clients->where($userModel::getTableName().'.id', $search_val);
            } else {
                $clients->where(function($query) use ($search_val, $userModel) {
                    $query->where($userModel::getTableName().'.name', 'like', $search_val.'%')
                            ->orWhere($userModel::getTableName().'.email', $search_val);
                });
            }
        }
        if(isset($request->gender)) {
            $clients->where($userModel::getTableName().'.gender', $request->gender);
        }
        if(isset($request->dob)) {
            $clients->where($userModel::getTableName().'.dob', $request->dob);
        }
        if(isset($request->visits)) {
            $clients->where($bookingModel::getTableName().'.booking_type', $request->visits);
        }
        if(isset($request->booking_type)) {
            // 1 for massages , 2 for therapies
            if($request->booking_type == 1){
                $clients->whereNotNull($bookingMassageModel::getTableName().'.massage_prices_id')->whereNotNull($bookingMassageModel::getTableName().'.massage_timing_id')
                        ->where($bookingMassageModel::getTableName().'.therapy_timing_id',NULL)->where($bookingMassageModel::getTableName().'.therapy_prices_id',NULL);
            } else {
                $clients->whereNotNull($bookingMassageModel::getTableName().'.therapy_timing_id')->whereNotNull($bookingMassageModel::getTableName().'.therapy_prices_id')
                        ->where($bookingMassageModel::getTableName().'.massage_prices_id',NULL)->where($bookingMassageModel::getTableName().'.massage_timing_id',NULL);
            }
        }
        
        $clientData = $clients->groupBy($userModel::getTableName().'.id')->paginate(10, ['*'], 'page', $pageNumber);
        $userModel->setMysqlStrictTrue();
        
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
        
        $ratings = TherapistUserRating::where('user_id',$request->user_id)->get();
        $avg_rating = $ratings->avg('rating');
        
        foreach ($ratings as $key => $value) {
            $model = $value->model;
            if($model == "App\Shop") {
                $user = Shop::where('id',$value->model_id)->select('id','name')->get();
            } else {
                $user = Therapist::where('id',$value->model_id)->select('id','name')->get();
            }
            $value['user'] = $user;
        }
        $ratings = $ratings->groupBy('type');
        
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
        
        $request->request->add(['bookings_filter' => Booking::BOOKING_CANCELLED]);
        $noShow = $bookingModel->getGlobalQuery($request)->groupBy('booking_id')->count();
               
        $infoForTherapy = UserMassagePreferences::with('massagePreference:id,name','massagePreferenceOption:id,name')->where('user_id',$request->user_id)->get();
        
        return $this->returnSuccess(__($this->successMsg['client.data.found']), 
                ["client" => $client, "totalAppointments" => $totalAppointments, "noShow" => $noShow, "registeredAt" => $client->created_at,
                 "lastVisited" => $lastvisited->created_at, "recipient" => $recipient, "addresses" => $addresses, "therapists" => $therapists,
                 "is_verified" => $is_verified, "questionnaries" => $questionnaries, "ratings" => $ratingData, "avg_rating" => $avg_rating, "infoForTherapy" => $infoForTherapy]);
    }
     
    public function getFutureBookings(Request $request) {
        
        $type = isset($request->type) ? $request->type : Booking::BOOKING_TYPE_IMC;
        $request->request->add(['type' => $type,'bookings_filter' => Booking::BOOKING_FUTURE,'user_id' => $request->user_id]);
        $bookingModel = new Booking();
        $futureBookings = $bookingModel->getGlobalQuery($request)->groupBy('booking_id');
               
        return $this->returnSuccess(__($this->successMsg['client.future.booking']), array_values($futureBookings->toArray()));
    }
    
    public function getPastBookings(Request $request) {
        
        $type = isset($request->type) ? $request->type : Booking::BOOKING_TYPE_IMC;
        $request->request->add(['type' => $type,'bookings_filter' => Booking::BOOKING_PAST,'user_id' => $request->user_id]);
        $bookingModel = new Booking();
        $pastBookings = $bookingModel->getGlobalQuery($request)->groupBy('booking_id');
               
        return $this->returnSuccess(__($this->successMsg['client.past.booking']), array_values($pastBookings->toArray()));
    }
    
    public function getCancelledBookings(Request $request) {
        
        $type = isset($request->type) ? $request->type : Booking::BOOKING_TYPE_IMC;
        $request->request->add(['type' => $type,'bookings_filter' => Booking::BOOKING_CANCELLED,'user_id' => $request->user_id]);
        $bookingModel = new Booking();
        $cancelledBookings = $bookingModel->getGlobalQuery($request)->groupBy('booking_id');
               
        return $this->returnSuccess(__($this->successMsg['client.cancelled.booking']), array_values($cancelledBookings->toArray()));
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
            'user' => $user->name,
            'shop' => $shop->name,
            'room' => $room->name,
            'object' => $request->forgotten_object
        );

        $send = $this->sendMail($view, $to, $subject, $body);
        
        return $this->returnSuccess(__($this->successMsg['client.forgot.object.return']), $send);
    }
    
    public function updateRating(Request $request) {
        
        $rating = TherapistUserRating::find($request->rating_id);
        $rating->update(['rating' => $request->rating, "edit_by" => $request->edit_by]);
        return $this->returnSuccess(__($this->successMsg['client.rating.update']), $rating);
        
    }
}
