<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class UserEmailOtp extends BaseModel
{
    protected $fillable = [
        'otp',
        'email',
        'is_send',
        'is_verified',
        'user_id',
    ];

    public function validator(array $data, $id = false, $isUpdate = false)
    {
        if ($isUpdate === true && !empty($id)) {
            $emailValidator = ['unique:user_email_otps,email,' . $id];
        } else {
            $emailValidator = ['unique:user_email_otps'];
        }

        return Validator::make($data, [
            'otp'          => ['max:4'],
            // 'email'        => array_merge(['required', 'string', 'email', 'max:255'], $emailValidator),
            'email'        => ['required', 'email'],
            'is_verified'  => ['in:0,1'],
            'is_send'      => ['in:0,1'],
            'user_id' => ['required', 'integer']
        ]);
    }
}
