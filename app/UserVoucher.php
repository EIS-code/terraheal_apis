<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class UserVoucher extends BaseModel
{
    protected $fillable = [
        "user_voucher_price_id",
        "massage_id",
        "massage_timing_id",
        "therapy_id",
        "therapy_timing_id",
        "therapist_id"
    ];

    public function validator(array $data)
    {
        return Validator::make($data, [
            'user_voucher_price_id' => ['required', 'integer'],
        ]);
    }
}
