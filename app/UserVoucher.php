<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use App\UserVoucherPrice;
use App\Therapist;

class UserVoucher extends BaseModel
{
    protected $fillable = [
        "user_voucher_price_id",
        "service_id",
        "service_timing_id",
        "therapist_id"
    ];

    public function validator(array $data)
    {
        return Validator::make($data, [
            'user_voucher_price_id' => ['required', 'integer','exists:' . UserVoucherPrice::getTableName() . ',id'],
            'service_id'            => ['integer','exists:' . Service::getTableName() . ',id'],
            'service_timing_id'     => ['integer','exists:' . ServiceTiming::getTableName() . ',id'],
            'therapist_id'          => ['integer','exists:' . Therapist::getTableName() . ',id'],
        ]);
    }
}
