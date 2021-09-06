<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class ForgotOtp extends Model
{
    protected $fillable = [
        'model',
        'model_id',
        'otp',
        'mobile_number',
        'mobile_code'
    ];

    public function validator(array $data)
    {
        return Validator::make($data, [
            'model'         => ['required', 'string'],
            'model_id'      => ['required', 'string'],
            'otp'           => ['required', 'string'],
            'mobile_number' => ['required', 'string'],
            'mobile_code'   => ['nullable', 'string'],
        ]);
    }
}