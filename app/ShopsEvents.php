<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class ShopsEvents extends BaseModel
{
    protected $fillable = [
        'event_name',
        'event_date',
        'shop_id'
    ];
    
    public static function validator(array $data)
    {
        return Validator::make($data, [
                    'event_name'    => ['required', 'string'],
                    'event_date'    => ['required'],
                    'shop_id'       => ['required', 'integer']
        ]);
    }
    
    public function getEventDateAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        return strtotime($value) * 1000;
    }
}
