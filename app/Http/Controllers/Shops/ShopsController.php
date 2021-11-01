<?php

namespace App\Http\Controllers\Shops;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Shop;
use App\Therapist;
use App\User;
use App\MassagePreference;
use App\Libraries\CommonHelper;
use App\SessionType;
use Carbon\Carbon;
use App\ShopShift;
use App\ShopHour;
use App\Booking;
use App\BookingInfo;
use App\BookingMassage;
use App\ForgotOtp;
use App\Room;

class ShopsController extends BaseController {

    public $errorMsg = [
        'loginEmail' => "Please provide email.",
        'loginPass' => "Please provide password.",
        'loginBoth' => "Shop email or password seems wrong.",
        'error.booking' => 'Booking not found.',
        'otp.not.found' => 'Otp not found !',
        'shop.not.found' => 'Center not found !'
    ];
    public $successMsg = [
        'login' => "Shop found successfully !",
        'therapists.found.successfully' => 'therapists found successfully',
        'services.found.successfully' => 'services found successfully',
        'clients.found.successfully' => 'clients found successfully',
        'preferences.found.successfully' => 'preferences found successfully',
        'sessions.found.successfully' => 'Sessions types found successfully',
        'shifts.add' => 'Shift added successfully',
        'shifts.get' => 'Shifts data found successfully',
        'shop.free.slots' => 'Shop freeslots are found successfully',
        'shop.hours.not.found' => 'Shop hours not found',
        'no.data.found' => 'No data found',
        'confirm.booking' => 'Booking confirm successfully',
        'success.otp' => 'Otp sent successfully !',
        'success.reset.password' => 'Password reset successfully !',
        'success.otp.verified' => 'Otp verified successfully !',
        'success.location' => 'Location get successfully !',
    ];

    public function signIn(Request $request) {
        $data = $request->all();
        $email = (!empty($data['email'])) ? $data['email'] : NULL;
        $password = (!empty($data['shop_password'])) ? $data['shop_password'] : NULL;

        if (empty($email)) {
            return $this->returnError($this->errorMsg['loginEmail']);
        } elseif (empty($password)) {
            return $this->returnError($this->errorMsg['loginPass']);
        }

        if (!empty($email) && !empty($password)) {

            $shop = Shop::with('apiKey')->where(['email' => $email])->first();
            
            if (!empty($shop) && Hash::check($password, $shop->shop_password)) {
                return $this->returnSuccess(__($this->successMsg['login']), $shop);
            } else {
                return $this->returnError($this->errorMsg['loginBoth']);
            }
        }
        return $this->returnNull();
    }

    public function getAllTherapists(Request $request) {

        $therapist = Therapist::where('shop_id', $request->get('shop_id'))->get();
        if (!empty($therapist)) {
            return $this->returnSuccess(__($this->successMsg['therapists.found.successfully']), $therapist);
        } else {
            return $this->returnSuccess(__($this->successMsg['no.data.found']), null);
        }
    }

    public function getAllServices(Request $request) {
        
        $request->request->add(['isGetAll' => true]);
        $services = CommonHelper::getAllService($request);
        
        if (!empty($services)) {
            return $this->returnSuccess(__($this->successMsg['services.found.successfully']), $services);
        } else {
            return $this->returnSuccess(__($this->successMsg['no.data.found']), null);
        }
    }

    public function getAllClients(Request $request) {

        $clients = User::where('shop_id', $request->get('shop_id'))->get();
        if (!empty($clients)) {
            return $this->returnSuccess(__($this->successMsg['clients.found.successfully']), $clients);
        } else {
            return $this->returnSuccess(__($this->successMsg['no.data.found']), null);
        }
    }
    
    public function getSessionTypes() {

        $sessions = SessionType::all();
        if (!empty($sessions)) {
            return $this->returnSuccess(__($this->successMsg['sessions.found.successfully']), $sessions);
        } else {
            return $this->returnSuccess(__($this->successMsg['no.data.found']), null);
        }
    }

    public function getPreferences(Request $request) {
        
        $type = $request->get('type');
        if (!empty($type)) {
            $preferences = MassagePreference::with(['preferenceOptions'])
                            ->whereHas('preferenceOptions', function($q) use($type) {
                                $q->where('massage_preference_id', '=', $type);
                            })->first();
        } else {
            $preferences = MassagePreference::with('preferenceOptions')->get();
        }
        if (!empty($preferences)) {
            return $this->returnSuccess(__($this->successMsg['preferences.found.successfully']), $preferences);
        } else {
            return $this->returnSuccess(__($this->successMsg['no.data.found']), null);
        }
    }
    
    public function addShift(Request $request) {
        
        $data = $request->all();
        $from = Carbon::createFromTimestampMs($data['from']);
        $to = Carbon::createFromTimestampMs($data['to']);
        
        $data['from'] = $from;
        $data['to'] = $to;
        
        $model = new ShopShift();
        $checks = $model->validator($data);
        if ($checks->fails()) {
            return $this->returnError($checks->errors()->first(), NULL, true);
        }
        $shift = ShopShift::create($data);
        return $this->returnSuccess(__($this->successMsg['shifts.add']), $shift);
    }
    
    public function getShifts(Request $request) {
        
        $shifts = ShopShift::where('shop_id',$request->shop_id)->get();
        return $this->returnSuccess(__($this->successMsg['shifts.get']), $shifts);
    }
    
    public function getFreeSlots(Request $request) {
        
        $date = Carbon::createFromTimestampMs($request->date);
        $hourModel = new ShopHour();
        $day = array_search($date->format('l'), $hourModel->shopDays);
        $hours = ShopHour::where(['shop_id' => $request->shop_id, 'day_name' => (string) $day])->first();
        
        if(!empty($hours)) {
            $openAt = new Carbon($hours->open_at);
            $closeAt = new Carbon($hours->close_at);

            $freeSlots = [];
            do {
                $freeSlots[] = [
                    'startTime' => strtotime($openAt->format('H:i:s')) * 1000,
                    'endTime' => strtotime($openAt->addHour()->format('H:i:s')) * 1000
                ];
                $diff = $openAt->diff($closeAt)->format("%H:%i");
            } while($diff != 0 );
            
            return $this->returnSuccess(__($this->successMsg['shop.free.slots']), $freeSlots);
        }
        return $this->returnSuccess(__($this->successMsg['shop.hours.not.found']));
        
    }

    public function confirmBooking(Request $request) {
        
        $date = Carbon::createFromTimestampMs($request->actual_date_time);
        $booking = Booking::find($request->booking_id);
        if(empty($booking)) {
            return $this->returnError($this->errorMsg['error.booking']);
        }
        $bookingInfos = BookingInfo::where('booking_id', $request->booking_id)->get();
        
        foreach ($bookingInfos as $key => $bookinginfo) {
            
            $bookingMassages = BookingMassage::where('booking_info_id', $bookinginfo->id)->get();
            foreach ($bookingMassages as $key => $massage) {
                $massage->update(['is_confirm' => BookingMassage::IS_CONFIRM, 'actual_date_time' => $date]);
            }
        }
        return $this->returnSuccess(__($this->successMsg['confirm.booking']), $booking);
    }
    
    public function deleteOtp(Request $request) {
        
        $otps = ForgotOtp::where(['model_id' => $request->user_id, 'model' => Shop::SHOP])->get();
        foreach ($otps as $key => $otp) {
            $otp->delete();
        }
        return true;
    }
    
    public function forgotPassword(Request $request) {

        $shop = Shop::where('tel_number', $request->mobile_number)->first();

        if (empty($shop)) {
            return $this->returnError($this->errorMsg['shop.not.found']);
        }
        $request->request->add(['user_id' => $shop->id]);
        $this->deleteOtp($request);
        
        $data = [
            'model_id' => $shop->id,
            'model' => Shop::SHOP,
            'otp' => 1234,
            'mobile_number' => $request->mobile_number,
            'mobile_code' => $request->mobile_code,
        ];

        ForgotOtp::create($data);
        return $this->returnSuccess(__($this->successMsg['success.otp']), ['user_id' => $shop->id, 'otp' => 1234]);
    }
    
    public function resetPassword(Request $request) {
        
        $shop = Shop::find($request->user_id);

        if (empty($shop)) {
            return $this->returnError($this->errorMsg['shop.not.found']);
        }
        
        $shop->update(['password' => Hash::make($request->password)]);                
        $this->deleteOtp($request);
        
        return $this->returnSuccess(__($this->successMsg['success.reset.password']), $shop);
    }

    public function verifyOtp(Request $request) {
        
        $is_exist = ForgotOtp::where(['model_id' => $request->user_id, 'model' => Shop::SHOP, 'otp' => $request->otp])->first();
        
        if(empty($is_exist)) {
            return $this->returnError($this->errorMsg['otp.not.found']);
        }
        return $this->returnSuccess(__($this->successMsg['success.otp.verified']), $is_exist);
    }
    
    public function getShopRooms(Request $request) {
        
        $rooms = Room::with('shop')->where('shop_id', $request->shop_id)->get();
        
        $location = [];
        foreach ($rooms as $key => $room) {
            
            $location[] = [
                'room_id' => $room->id,
                'room' => $room->shop->name . '-' . $room->name
            ];
        }
        
        return $this->returnSuccess(__($this->successMsg['success.location']), $location);
    }
}
