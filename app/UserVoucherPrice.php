<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use App\Voucher;
use App\User;

class UserVoucherPrice extends BaseModel
{
    protected $fillable = [
        "voucher_id",
        "user_id",
        "total_value",
        "used_value",
        "available_value",
        "purchase_date"
    ];

    public function validator(array $data)
    {
        return Validator::make($data, [
            'voucher_id'    => ['required', 'integer', 'exists:' . Voucher::getTableName() . ',id'],
            'user_id'       => ['required', 'integer', 'exists:' . User::getTableName() . ',id'],
            'total_value'   => ['required'],
            'purchase_date' => ['required']
        ]);
    }
}
