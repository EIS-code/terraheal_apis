<?php

namespace App\Http\Controllers\Shops\Clients;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use App\User;
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
use Carbon\Carbon;
use App\Pack;
use App\Voucher;
use App\Service;
use App\ServicePricing;
use App\MassagePreference;

class ClientController extends BaseController {

    public $successMsg = [
        'client.data.found' => 'Client data found successfully!',
        'client.future.booking' => 'Client future booking found successfully!',
        'client.past.booking' => 'Client past booking found successfully!',
        'client.cancelled.booking' => 'Client cancelled booking found successfully!',
        'client.forgot.object' => 'Client forgotten object added successfully!',
        'client.forgot.object.return' => 'Client forgotten object returned successfully!',
        'client.email.send' => 'Email sent successfully!',
        'client.rating.update' => 'Client ratings updated successfully!',
        'client.not.found' => 'Client not found',
        'data.not.found' => 'Data not found',
        'client.recipient' => 'Client recipient found successfully!',
        'client.source' => 'Client sources found successfully!',
        'client.forgot.objects' => 'Client forgotten objectes found successfully!',
    ];        
    
    public function searchClients(Request $request) {
        
        $userModel = new User();
        $bookingModel = new Booking();
        $bookingInfoModel = new BookingInfo();
        $bookingMassageModel = new BookingMassage();
        $serviceModel = new Service();
        $servicePriceModel = new ServicePricing();
                
        $userModel->setMysqlStrictFalse();
        
        $clients = $userModel->select(DB::RAW($userModel::getTableName() .'.*', $bookingModel::getTableName().'.id',
                $bookingModel::getTableName().'.booking_type', $bookingModel::getTableName().'.user_id',
                $bookingInfoModel::getTableName().'.id', $bookingMassageModel::getTableName().'.service_pricing_id',
                $bookingMassageModel::getTableName().'.booking_info_id'))                
                ->leftJoin($bookingModel::getTableName(), $bookingModel::getTableName().'.user_id', '=', $userModel::getTableName().'.id')
                ->leftJoin($bookingInfoModel::getTableName(), $bookingInfoModel::getTableName().'.booking_id', '=', $bookingModel::getTableName().'.id')
                ->leftJoin($bookingMassageModel::getTableName(), $bookingMassageModel::getTableName().'.booking_info_id', '=', $bookingInfoModel::getTableName().'.id')
                ->leftJoin($servicePriceModel::getTableName(), $servicePriceModel::getTableName().'.id', '=', $bookingMassageModel::getTableName().'.service_pricing_id')
                ->leftJoin($serviceModel::getTableName(), $serviceModel::getTableName().'.id', '=', $servicePriceModel::getTableName().'.service_id')
                ->where(['users.shop_id' => $request->shop_id, 'users.is_removed' => User::$notRemoved]);
                        
        $pageNumber = !empty($request->page_number) ? $request->page_number : 1;
        $search_val = $request->search_val;
        
        if(!empty($request->name_filter))
        {
            $clients->where($userModel::getTableName().'.name', 'like', $request->name_filter.'%');
        }
        if(!empty($search_val))
        {
            if(is_numeric($search_val)) {
                $clients->where(function($query) use ($search_val, $userModel) {
                    $query->where($userModel::getTableName().'.id', $search_val)
                            ->orWhere($userModel::getTableName().'.tel_number', $search_val);
                });
            } else {
                $clients->where(function($query) use ($search_val, $userModel) {
                    $query->where($userModel::getTableName().'.name', 'like', $search_val.'%')
                            ->orWhere($userModel::getTableName().'.surname', 'like', $search_val.'%')
                            ->orWhere($userModel::getTableName().'.email', $search_val);
                });
            }
        }
        if(!empty($request->gender)) {
            $clients->where($userModel::getTableName().'.gender', $request->gender);
        }

        if (!empty($request->dob)) {
            $dob = Carbon::createFromTimestampMs($request->dob)->format('Y-m-d');
            $clients->whereRaw("DATE_FORMAT(FROM_UNIXTIME(`dob` / 1000), '%Y-%m-%d') = '{$dob}'");
        }

        if(!empty($request->visits)) {
            $clients->where($bookingModel::getTableName().'.booking_type', $request->visits);
        }
        if(!empty($request->booking_type)) {
            // 1 for massages , 2 for therapies
            if($request->booking_type == 1){
                $clients->where($serviceModel::getTableName() . '.service_type', Service::MASSAGE);                
            } else {
                $clients->where($serviceModel::getTableName() . '.service_type', Service::THERAPY);
            }
        }
        
        $clientData = $clients->groupBy($userModel::getTableName().'.id')->paginate(10, ['*'], 'page', $pageNumber);
        $userModel->setMysqlStrictTrue();
        
        return $this->returnSuccess(__($this->successMsg['client.data.found']), $clientData);
    }
    
    public function getRatings($ratings)
    {
        $ratingData = [];
        if(count($ratings) > 0) {
            foreach ($ratings as $key => $value) {
                $model = $value->model;
                if($model == "App\Shop") {
                    $user = Shop::where('id',$value->model_id)->select('id','name')->first();
                    $designation = 'Admin';
                } else {
                    $user = Therapist::where('id',$value->model_id)->select('id','name')->first();
                    $designation = 'Therapist';
                }
                $value['user'] = $user;
                $value['designation'] = $designation;
            }
            $ratings = $ratings->groupBy('type');

            foreach ($ratings as $key => $rating) {
                $type = $rating[0]['type'];
                $sum = 0; $cnt = 0;
                $users = [];
                foreach ($rating as $key => $value) {
                    if(!is_null($value->user)) {
                        $cnt += 1;
                        $sum += $value->rating;
                        $users[] = [
                            'id' => $value->id,
                            'user_id' => $value->user->id,
                            'user_name' => $value->user->name,
                            'rating' => $value->rating,
                            'designation' => $value->designation
                        ];
                    }
                }
                $avg_rate = $cnt == 0 ? 0 : $sum / $cnt;
                $ratingData[] = [
                    'type' => $type,
                    'users' => $users,
                    'avg_rating' => round($avg_rate, 2),
                ];
            }
        }
        return $ratingData;
    }
    
    public function clientDetails(Request $request) {
        
        $userId = $request->user_id;
        $client = User::with('shop:id,name','city:id,name','country:id,name')
                ->where('id', $userId)->where('shop_id', $request->shop_id)->first();
        
        if($client) {
            $bookingModel = new Booking();

            $totalAppointments = $bookingModel->getGlobalQuery($request)->groupBy('booking_id');
            $lastVisit = $totalAppointments->first();
            $lastVisit = !empty($lastVisit) ? $lastVisit[0]['massage_date'] : null;

            $recipient = User::where('user_id',$userId)->get()->count();
            $addresses = UserAddress::where('user_id',$userId)->get()->count();
            $therapists = $bookingModel->getGlobalQuery($request)->groupBy('therapist_id')->count();
            $is_verified = false;
            if($client->is_email_verified == 1 && $client->is_mobile_verified == 1 && $client->is_document_verified == 1) {
                $is_verified = true;
            }        
            $questionnaries = TherapyQuestionnaire::with('questionnaireAnswer')->get();

            $ratings = TherapistUserRating::where('user_id',$userId)->get();
            $avg_rating = clone $ratings;

            $ratingData = $this->getRatings($ratings);

            $request->request->add(['bookings_filter' => array(Booking::BOOKING_CANCELLED)]);
            $noShow = $bookingModel->getGlobalQuery($request)->groupBy('booking_id')->count();

            $questions = MassagePreference::with('preferenceOptions')->get();
            $answers = UserMassagePreferences::with('massagePreference:id,name','massagePreferenceOption:id,name')->where('user_id',$request->user_id)->get();
            $infoForTherapy = ['questions' => $questions, 'answers' => $answers]; 

            $packs = Pack::all();
            $usedPacks = Pack::with('users')->whereHas('users', function($q) use($userId) {
                        $q->where('user_id', $userId);
                    })->pluck('id')->toArray();
                    
            foreach ($packs as $key => $pack) {
                if (in_array($pack->id, $usedPacks)) {
                    $pack->is_used = true;
                } else {
                    $pack->is_used = false;
                }
            }
            
            $vouchers = Voucher::all();
            $usedVouchers = Voucher::with('users')->whereHas('users', function($q) use($userId) {
                        $q->where('user_id', $userId);
                    })->pluck('id')->toArray();

            foreach ($vouchers as $key => $voucher) {
                if (in_array($voucher->id, $usedVouchers)) {
                    $voucher->is_used = true;
                } else {
                    $voucher->is_used = false;
                }
            }
            $client['totalAppointments'] = $totalAppointments->count();
            $client['noShow'] = $noShow;
            $client['registeredAt'] = $client->created_at;
            $client['lastVisited'] = $lastVisit;
            $client['avg_rating'] = number_format($avg_rating->avg('rating'), 2);
            $client['is_verified'] = $is_verified;
            return $this->returnSuccess(__($this->successMsg['client.data.found']), 
                    ["client" => $client, "recipient" => $recipient, "addresses" => $addresses, "therapists" => $therapists,"questionnaries" => $questionnaries, "ratings" => $ratingData,
                        "massage_preferences" => $infoForTherapy, "packs" => $packs, "vouchers" => $vouchers]);
        }
        return $this->returnSuccess(__($this->successMsg['client.not.found']));
    }
     
    public function getFutureBookings(Request $request) {
        
        $type = !empty($request->type) ? $request->type : Booking::BOOKING_TYPE_IMC;
        $request->request->add(['type' => $type,'bookings_filter' => array(Booking::BOOKING_FUTURE),'user_id' => $request->user_id]);
        $bookingModel = new Booking();
        $futureBookings = $bookingModel->getGlobalQuery($request);
               
        return $this->returnSuccess(__($this->successMsg['client.future.booking']), $futureBookings->toArray());
    }
    
    public function getPastBookings(Request $request) {
        
        $type = !empty($request->type) ? $request->type : Booking::BOOKING_TYPE_IMC;
        $request->request->add(['type' => $type,'bookings_filter' => array(Booking::BOOKING_PAST),'user_id' => $request->user_id]);
        $bookingModel = new Booking();
        $pastBookings = $bookingModel->getGlobalQuery($request);
               
        return $this->returnSuccess(__($this->successMsg['client.past.booking']), $pastBookings->toArray());
    }
    
    public function getCancelledBookings(Request $request) {
        
        $type = !empty($request->type) ? $request->type : Booking::BOOKING_TYPE_IMC;
        $request->request->add(['type' => $type,'bookings_filter' => array(Booking::BOOKING_CANCELLED),'user_id' => $request->user_id]);
        $bookingModel = new Booking();
        $cancelledBookings = $bookingModel->getGlobalQuery($request);
               
        return $this->returnSuccess(__($this->successMsg['client.cancelled.booking']), $cancelledBookings->toArray());
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
        
        if(empty($user)) {
            return $this->returnSuccess(__($this->successMsg['client.not.found']));
        }
        
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
        
        return $send;
    }
    
    public function updateRating(Request $request) {
        
        $rating = TherapistUserRating::find($request->rating_id);
        $rating->update(['rating' => $request->rating, "edit_by" => $request->edit_by]);
        return $this->returnSuccess(__($this->successMsg['client.rating.update']), $rating);
    }
    
    public function getRecipient(Request $request) {
        
        $recipients = User::where('user_id',$request->client_id)->get();
        if ($recipients->count() == 0) {
            return $this->returnSuccess(__($this->successMsg['data.not.found']));
        }
        return $this->returnSuccess(__($this->successMsg['client.recipient']), $recipients);
    }
    
    public function getSources() {
                
        $source = User::$source;
        return $this->returnSuccess(__($this->successMsg['client.source']), $source);
    }
    
    public function getForgotObjects(Request $request) {
        
        $objects = UsersForgottenObjects::with('shops:id,name','rooms:id,name')->where('user_id',$request->client_id)->get();
        return $this->returnSuccess(__($this->successMsg['client.forgot.objects']), $objects);
    }
}
