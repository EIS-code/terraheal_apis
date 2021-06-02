<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class ServiceTiming extends BaseModel
{
    protected $fillable = [
        'time',
        'service_id'  
    ];

    protected $hidden = ['created_at', 'updated_at'];
    
    public function validator(array $data)
    {
        return Validator::make($data, [
            'time'       => ['required'],
            'service_id' => ['required', 'integer', 'exists:' . Service::getTableName() . ',id'],
        ]);
    }

    public function pricing()
    {
        return $this->hasOne('App\ServicePricing', 'service_timing_id', 'id');
    }

    public function service()
    {
        return $this->hasOne('App\Service', 'id', 'service_id');
    }
}
