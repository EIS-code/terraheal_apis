<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use App\Therapist;
use App\Pack;
use App\User;

class UserPack extends BaseModel
{
    protected $fillable = [
        'pack_id',
        'user_id',
        'purchase_date',
        'payment_id'
    ];
    
    const APP = '0';
    const WEB = '1';
    const CENTER = '2';
    
    const TODAY = '0';
    const YESTERDAY = '1';
    const THIS_WEEK = '2';
    const CURRENT_MONTH = '3';
    const LAST_7_DAYS = '4';
    const LAST_14_DAYS = '5';
    const LAST_30_DAYS = '6';
    const CUSTOM = '7';
        
    public function validator(array $data)
    {
        return Validator::make($data, [
            'pack_id'         => ['required', 'integer', 'exists:' . Pack::getTableName() . ',id'],
            'user_id'         => ['required', 'integer', 'exists:' . User::getTableName() . ',id'],
            'purchase_date'   => ['required']
        ]);
    }

    public function getPurchaseDateAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        return strtotime($value) * 1000;
    }
    
    public function pack()
    {
        return $this->hasOne('App\Pack', 'id', 'pack_id');
    }
    
    public function user()
    {
        return $this->hasOne('App\User', 'id', 'user_id');
    }
}
