<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class VoucherShop extends BaseModel
{
    protected $fillable = [
        'voucher_id',
        'shop_id'
    ];

    public function validator(array $data)
    {
        return Validator::make($data, [
            'voucher_id' => ['required', 'integer'],
            'shop_id' => ['required', 'integer']
        ]);
    }
}
