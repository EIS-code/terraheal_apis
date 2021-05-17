<?php

namespace App;

use Illuminate\Support\Facades\Validator;


class ShopShift extends BaseModel
{
    protected $fillable = [
        'from',
        'to',
        'shop_id'
    ];

    public function validator(array $data)
    {
        return Validator::make($data, [
            'from'       => ['required', 'date:H:i:s'],
            'to'         => ['required', 'date:H:i:s'],
            'shop_id'    => ['required', 'integer', 'exists:' . Shop::getTableName() . ',id']
        ]);
    }
    
    public function getFromAttribute($value)
    {
        return strtotime($value) * 1000;
    }
    
    public function getToAttribute($value)
    {
        return strtotime($value) * 1000;
    }
}
