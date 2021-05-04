<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use App\Shop;

class ShopPaymentDetail extends Model
{
    protected $fillable = [
        'iban',
        'paypal_secret',
        'paypal_client_id',
        'google_pay_number',
        'apple_pay_number',
        'sales_percentage',
        'inital_amount',
        'fixed_amount',
        'shop_id'
    ];

    protected $hidden = ['created_at', 'updated_at'];
    
    public function validator(array $data)
    {
        return Validator::make($data, [
            'iban'                  => ['required', 'string', 'max:191'],
            'paypal_secret'         => ['required', 'string', 'max:191'],
            'paypal_client_id'      => ['required', 'string', 'max:191'],
            'google_pay_number'     => ['required', 'string', 'max:191'],
            'apple_pay_number'      => ['required', 'string', 'max:191'],
            'sales_percentage'      => ['nullable', 'string', 'max:255'],
            'inital_amount'         => ['nullable', 'string', 'max:255'],
            'fixed_amount'          => ['nullable', 'string', 'max:255'],
            'shop_id'               => ['required', 'integer', 'exists:' . Shop::getTableName() . ',id']
        ]);
    }
    public function validateAgreement(array $data)
    {
        return Validator::make($data, [
            'sales_percentage'      => ['nullable', 'string', 'max:255'],
            'inital_amount'         => ['nullable', 'string', 'max:255'],
            'fixed_amount'          => ['nullable', 'string', 'max:255'],
            'shop_id'               => ['required', 'integer', 'exists:' . Shop::getTableName() . ',id']
        ]);
    }
}
