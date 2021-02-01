<?php

namespace App\Http\Controllers\Therapist;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use App\Therapist;

class TherapistController extends BaseController
{

    public $errorMsg = [
        'loginEmail' => "Please provide email properly.",
        'loginPass'  => "Please provide password properly.",
        'loginBoth'  => "Therapist email or password seems wrong."
    ];

    public $successMsg = [
        'login' => "Therapist found successfully !"
    ];

    public function signIn(int $isFreelancer = Therapist::IS_NOT_FREELANCER, Request $request)
    {
        $model    = new Therapist();
        $data     = $request->all();
        $email    = (!empty($data['email'])) ? $data['email'] : NULL;
        $password = (!empty($data['password'])) ? $data['password'] : NULL;

        if (empty($email)) {
            return $this->returnError($this->errorMsg['loginEmail']);
        } elseif (empty($password)) {
            return $this->returnError($this->errorMsg['loginPass']);
        }

        if (!empty($email) && !empty($password)) {
            $getTherapist = $model->where(['email' => $email, 'is_freelancer' => $isFreelancer])->first();

            if (!empty($getTherapist) && Hash::check($password, $getTherapist->password)) {
                $getTherapist = $getTherapist->first();
                return $this->returnSuccess($this->successMsg['login'], $this->getGlobalResponse($getTherapist->id));
            } else {
                return $this->returnError($this->errorMsg['loginBoth']);
            }
        }

        return $this->returnNull();
    }

    public function forgot(int $isFreelancer = Therapist::IS_NOT_FREELANCER, Request $request)
    {
        $model = new Therapist();
    }
}
