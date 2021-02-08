<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class Currency extends BaseModel
{
    public static $defaultCurrency   = 'EUR';
    public static $defaultCurrencyId = 1;

    protected $fillable = [
        'code',
        'exchange_rate',
        'country_id'
    ];

    public function validator(array $data, $id = false, $isUpdate = false)
    {
        return Validator::make($data, [
            'code'          => ['required', 'string', 'max:255'],
            'exchange_rate' => ['required', 'float'],
            'country_id'    => ['required', 'integer']
        ]);
    }
}
