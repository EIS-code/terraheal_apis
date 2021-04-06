<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use App\Voucher;
use App\Shop;

class VoucherShop extends BaseModel
{
    protected $fillable = [
        'voucher_id',
        'shop_id'
    ];

    public function validator(array $data)
    {
        return Validator::make($data, [
            'voucher_id'    => ['required', 'integer', 'exists:' . Voucher::getTableName() . ',id'],
            'shop_id'       => ['required', 'integer', 'exists:' . Shop::getTableName() . ',id']
        ]);
    }
}
