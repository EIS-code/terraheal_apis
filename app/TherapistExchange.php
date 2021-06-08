<?php

namespace App;

use App\Therapist;
use Illuminate\Support\Facades\Validator;

class TherapistExchange extends BaseModel
{
    protected $fillable = [
        'date',
        'status',
        'therapist_id',
        'with_therapist_id',
        'shift_id',
        'with_shift_id',
        'shop_id',
    ];

    const NO_ACTION = '0';
    const APPROVED = '1';
    const REJECT = '2';

    public static $status = [
        self::NO_ACTION  => 'No Action',
        self::APPROVED   => 'Approved',
        self::REJECT     => 'Reject'
    ];

    public function validator(array $data, $isUpdate = false)
    {
        return Validator::make($data, [
            'date'              => ['required', 'date:Y-m-d'],
            'status'            => ['in:' . implode(",", array_keys(self::$status))],
            'therapist_id'      => ['required', 'exists:' . Therapist::getTableName() . ',id'],
            'with_therapist_id' => ['required', 'exists:' . Therapist::getTableName() . ',id'],
            'shift_id'          => ['required', 'exists:' . ShopShift::getTableName() . ',id'],
            'with_shift_id'     => ['required', 'exists:' . ShopShift::getTableName() . ',id'],
            'shop_id'           => ['required', 'exists:' . Shop::getTableName() . ',id'],
        ]);
    }
    
    public function getStatusAttribute($value)
    {
        return (isset(self::$status[$value])) ? self::$status[$value] : $value;
    }
    
    public function getDateAttribute($value)
    {
        return strtotime($value) * 1000;
    }
    
    public function therapist() {
        
        return $this->hasOne('App\Therapist', 'id', 'therapist_id');
    }
    
    public function withTherapist() {
        
        return $this->hasOne('App\Therapist', 'id', 'with_therapist_id');
    }
    
    public function shifts() {
        
        return $this->hasOne('App\ShopShift', 'id', 'shift_id');
    }
    
    public function withShifts() {
        
        return $this->hasOne('App\ShopShift', 'id', 'with_shift_id');
    }
    
    public function shop() {
        
        return $this->hasOne('App\Shop', 'id', 'shop_id');
    }
}
