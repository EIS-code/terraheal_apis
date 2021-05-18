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

class ShopsController extends BaseController {

    public $errorMsg = [
        'loginEmail' => "Please provide email.",
        'loginPass' => "Please provide password.",
        'loginBoth' => "Shop email or password seems wrong.",
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
        'no.data.found' => 'No data found'
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

            $shop = Shop::where(['email' => $email])->first();
            if (!empty($shop) && Hash::check($password, $shop->shop_password)) {
                $shop = $shop->first();

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
                    'startTime' => $openAt->format('H:i:s'),
                    'endTime' => $openAt->addHour()->format('H:i:s')
                ];
                $diff = $openAt->diff($closeAt)->format("%H:%i");
            } while($diff != 0 );
            
            return $this->returnSuccess(__($this->successMsg['shop.free.slots']), $freeSlots);
        }
        return $this->returnSuccess(__($this->successMsg['shop.hours.not.found']));
        
    }

}
