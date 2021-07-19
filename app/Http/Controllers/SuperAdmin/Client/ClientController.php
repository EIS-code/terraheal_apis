<?php

namespace App\Http\Controllers\SuperAdmin\Client;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use App\User;
use App\TherapistUserRating;
use App\Booking;
use App\Therapist;
use App\Shop;
use App\UserAddress;
use App\UserMassagePreferences;
use App\Voucher;
use App\UserVoucherPrice;
use DB;
use App\Pack;
use App\UserPack;
use App\TherapyQuestionnaire;

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
        'print.booking' => "Booking details found successfully !",
        'user.address' => "User address details found successfully !",
        'user.centers' => "User centers found successfully !",
        'center.details.get' => "User center details found successfully !",
        'used.vouchers' => "User used vouchers found successfully !",
        'unused.vouchers' => "User unused vouchers found successfully !",
        'voucher.details' => "Voucher details found successfully !",
        'packs' => "Packs found successfully !",
        'pack.details' => "Pack details found successfully !",
        'massage.prefences' => "User massage prefences found successfully !",
        'therapy.questionnaries' => "Therapy questionnaries found successfully !",
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
    
    public function printBooking(Request $request) {
        
        $shopModel = new Shop();
        $bookingDetails = $shopModel->printBooking($request);
        
        if (!empty($bookingDetails['isError']) && !empty($bookingDetails['message'])) {
            return $this->returnError($bookingDetails['message'], NULL, true);
        }
        
        return $this->returnSuccess(__($this->successMsg['print.booking']), $bookingDetails);
    }
    
    public function getAddress(Request $request) {
        
        $address = UserAddress::where('user_id', $request->user_id)->get();
        
        return $this->returnSuccess(__($this->successMsg['user.address']), $address);
    }
    
    public function getCenters(Request $request) {
        
        $bookingModel = new Booking();
        $centers = $bookingModel->getGlobalQuery($request)->groupBy('shop_id');
        
        if(empty($centers)) {
            return $this->returnError(__($this->successMsg['no.data.found']));
        }
        $shops = [];
        foreach ($centers as $key => $center) {
            
            $first = $center->first();
            $shops[] = [
                'shop_id' => $first->shop_id,
                'shop_name' => $first->shop_name
            ];
        }
        
        return $this->returnSuccess(__($this->successMsg['user.centers']), $shops);
    }
    
    public function getCenterDetails(Request $request) {
        
        $details = Shop::withCount('services')->with('centerHours')->find($request->shop_id);
        
        if(empty($details)) {
            return $this->returnError(__($this->successMsg['no.data.found']));
        }
        
        return $this->returnSuccess(__($this->successMsg['center.details.get']), $details);
        
    }
    
    public function getUsedVouchers(Request $request) {
        
        $voucherModel = new Voucher();
        $voucherUsersModel = new UserVoucherPrice();

        $vouchers = $voucherModel
                ->select(DB::RAW($voucherUsersModel::getTableName() . '.*,'  . 'UNIX_TIMESTAMP(' . $voucherUsersModel::getTableName() . '.purchase_date) * 1000 as purchase_date,'. $voucherModel::getTableName() . '.*'))
                ->join($voucherUsersModel::getTableName(), $voucherUsersModel::getTableName() . '.voucher_id', '=', $voucherModel::getTableName() . '.id')
                ->where($voucherUsersModel::getTableName().'.user_id', $request->user_id)
                ->where($voucherUsersModel::getTableName().'.used_value', '>', 0.00)->get();
        
        return $this->returnSuccess(__($this->successMsg['used.vouchers']), $vouchers);
    }
    
    public function getUnUsedVouchers(Request $request) {
        
        $voucherModel = new Voucher();
        $voucherUsersModel = new UserVoucherPrice();

        $vouchers = $voucherModel
                ->select(DB::RAW($voucherUsersModel::getTableName() . '.*,'  . 'UNIX_TIMESTAMP(' . $voucherUsersModel::getTableName() . '.purchase_date) * 1000 as purchase_date,'. $voucherModel::getTableName() . '.*'))
                ->join($voucherUsersModel::getTableName(), $voucherUsersModel::getTableName() . '.voucher_id', '=', $voucherModel::getTableName() . '.id')
                ->where($voucherUsersModel::getTableName().'.user_id', $request->user_id)
                ->where($voucherUsersModel::getTableName().'.used_value', 0.00)->get();
        
        return $this->returnSuccess(__($this->successMsg['unused.vouchers']), $vouchers);
    }
    
    public function getVoucherDetails(Request $request) {
        
        $voucher = Voucher::find($request->voucher_id);
        
        if(empty($voucher)) {
            return $this->returnError(__($this->successMsg['no.data.found']));
        }
        return $this->returnSuccess(__($this->successMsg['voucher.details']), $voucher);
    }
    
    public function getPacks(Request $request) {
        
        $packModel = new Pack();
        $packUsersModel = new UserPack();
        
        $packs = $packModel
                ->select(DB::RAW($packUsersModel::getTableName() . '.*,' . $packModel::getTableName() . '.*'))
                ->join($packUsersModel::getTableName(), $packUsersModel::getTableName() . '.pack_id', '=', $packModel::getTableName() . '.id')
                ->where($packUsersModel::getTableName().'.users_id', $request->user_id)->get();
        
        return $this->returnSuccess(__($this->successMsg['packs']), $packs);
    }
    
    public function getPackDetails(Request $request) {
        
        $pack = Pack::find($request->pack_id);
        
        if(empty($pack)) {
            return $this->returnError(__($this->successMsg['no.data.found']));
        }
        return $this->returnSuccess(__($this->successMsg['pack.details']), $pack);
    }
    
    public function getMassagePreferences(Request $request) {
        
        $prefernces = UserMassagePreferences::with('massagePreference', 'massagePreferenceOption')->where('user_id', $request->user_id)->get();
        
        return $this->returnSuccess(__($this->successMsg['massage.prefences']), $prefernces);
    }
    
    public function getQuestionnaries(Request $request) {
        
        $questionnaries = TherapyQuestionnaire::with('questionnaireAnswer')->get();
        
        return $this->returnSuccess(__($this->successMsg['therapy.questionnaries']), $questionnaries);
    }
}
