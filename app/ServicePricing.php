<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class ServicePricing extends BaseModel
{
    protected $fillable = [
        'service_id',
        'service_timing_id',
        'price',
        'cost'
    ];

    protected $hidden = ['created_at', 'updated_at'];
    
    public function validator(array $data)
    {
        return Validator::make($data, [
            'service_id'            => ['required', 'integer', 'exists:' . Service::getTableName() . ',id'],
            'service_timing_id'     => ['required', 'integer', 'exists:' . ServiceTiming::getTableName() . ',id'],
            'price'                 => ['required'],
            'cost'                 => ['required']
        ]);
    }

    public function service()
    {
        return $this->hasOne('App\Service', 'id', 'service_id');
    }

    public function timing()
    {
        return $this->hasOne('App\ServiceTiming', 'id', 'service_timing_id');
    }
}
