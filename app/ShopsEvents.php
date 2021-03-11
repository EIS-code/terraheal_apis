<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class ShopsEvents extends Model
{
    protected $fillable = [
        'id',
        'event_name',
        'event_date',
        'shop_id'
    ];
    
    public static function validator(array $data)
    {
        return Validator::make($data, [
            'event_name'       => ['required', 'string'],
            'event_date' => ['required'],
            'shop_id' => ['required', 'integer']
        ]);
    }
}
