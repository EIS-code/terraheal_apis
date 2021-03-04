<?php

namespace App\Http\Controllers\Shops;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Shop;
use App\Therapist;
use App\User;
use App\MassagePreference;
use App\Libraries\serviceHelper;

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
        
        $services = serviceHelper::getAllService($request);
        
        if (count($services) > 0) {
            return $this->returnSuccess(__($this->successMsg['services.found.successfully']), $services);
        } else {
            return $this->returnSuccess(__($this->successMsg['no.data.found']), null);
        }
    }

    public function getAllClients(Request $request) {

        $clients = User::where('shop_id', $request->get('shop_id'))->get();
        if (count($clients) > 0) {
            return $this->returnSuccess(__($this->successMsg['clients.found.successfully']), $clients);
        } else {
            return $this->returnSuccess(__($this->successMsg['no.data.found']), null);
        }
    }

    public function getPreferences(Request $request) {

        $type = $request->get('type');
        if (isset($type)) {
            $preferences = MassagePreference::with(['preferenceOptions'])
                            ->whereHas('preferenceOptions', function($q) use($type) {
                                $q->where('massage_preference_id', '=', $type);
                            })->get();
        } else {
            $preferences = MassagePreference::with('preferenceOptions')->get();
        }
        if (count($preferences) > 0) {
            return $this->returnSuccess(__($this->successMsg['preferences.found.successfully']), $preferences);
        } else {
            return $this->returnSuccess(__($this->successMsg['no.data.found']), null);
        }
    }

}
