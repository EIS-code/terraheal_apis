<?php

namespace App\Http\Controllers\Shops;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use App\Shop;
use Illuminate\Support\Facades\Hash;

class ShopsController extends BaseController
{

     public $errorMsg = [
        'loginEmail' => "Please provide email.",
        'loginPass'  => "Please provide password.",
        'loginBoth'  => "Shop email or password seems wrong.",
    ];

    public $successMsg = [
        'login' => "Shop found successfully !",
    ];


    public function signIn(Request $request)
    {
        $data     = $request->all();
        $email    = (!empty($data['email'])) ? $data['email'] : NULL;
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
}
