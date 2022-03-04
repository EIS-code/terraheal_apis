<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use App\ServiceImage;

class ShopService extends BaseModel
{
    protected $fillable = [
        'service_id',
        'shop_id',
        'allow_at'
    ];
    
    const CENTER = '1';
    const HOME_HOTEL = '2';
    const BOTH = '3';

    public static $allowAt = [
        self::CENTER => 'In massage center',
        self::HOME_HOTEL => 'Home / Hotel visit',
        self::BOTH => 'Both'
    ];
    
    public function validator(array $data)
    {
        return Validator::make($data, [
            'service_id'       => ['required', 'integer', 'exists:' . Service::getTableName() . ',id'],
            'shop_id'          => ['required', 'integer', 'exists:' . Shop::getTableName() . ',id'],
            'allow_at'         => ['required', 'integer', 'in:' . implode(",", array_keys(self::$allowAt))],
        ]);
    }
    
    public function service()
    {
        return $this->hasOne('App\Service', 'id', 'service_id');
    }

    public function imageFeatured()
    {
        return $this->hasOne('App\ServiceImage', 'service_id', 'service_id')->where('is_featured', ServiceImage::IS_FEATURED);
    }

}
