<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class ShopKm extends BaseModel
{
    protected $fillable = [
        'shop_id',
        'kms',
        'price'
    ];
    
    const CENTER = '1';
    const HOME_HOTEL = '2';

    public static $allowAt = [
        self::CENTER => 'In massage center',
        self::HOME_HOTEL => 'Home / Hotel visit'
    ];
    
    public function validator(array $data)
    {
        return Validator::make($data, [
            'kms'           => ['required'],
            'price'         => ['required'],
            'shop_id'       => ['required', 'integer', 'exists:' . Shop::getTableName() . ',id'],
        ]);
    }

}
