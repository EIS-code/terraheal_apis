<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use App\UserVoucherPrice;
use App\Massage;
use App\MassageTiming;
use App\Therapy;
use App\TherapiesTimings;
use App\Therapist;

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
            'user_voucher_price_id' => ['required', 'integer','exists:' . UserVoucherPrice::getTableName() . ',id'],
            'massage_id'            => ['integer','exists:' . Massage::getTableName() . ',id'],
            'massage_timing_id'     => ['integer','exists:' . MassageTiming::getTableName() . ',id'],
            'therapy_id'            => ['integer','exists:' . Therapy::getTableName() . ',id'],
            'therapy_timing_id'     => ['integer','exists:' . TherapiesTimings::getTableName() . ',id'],
            'therapist_id'          => ['integer','exists:' . Therapist::getTableName() . ',id'],
        ]);
    }
}
