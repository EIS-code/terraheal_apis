<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class ShopService extends BaseModel
{
    protected $fillable = [
        'service_id',
        'shop_id',
        'allow_at'
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
            'service_id'       => ['required', 'integer', 'exists:' . Service::getTableName() . ',id'],
            'shop_id'          => ['required', 'integer', 'exists:' . Shop::getTableName() . ',id'],
            'allow_at'         => ['required', 'integer', 'in:' . implode(",", array_keys(self::$allowAt))],
        ]);
    }

}
