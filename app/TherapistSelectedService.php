<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class TherapistSelectedService extends BaseModel
{
    protected $fillable = [
        'service_id',
        'therapist_id'
    ];

    public function validator(array $data)
    {
        return Validator::make($data, [
            'service_id'   => ['required', 'integer', 'exists:' . Service::getTableName() . ',id'],
            'therapist_id' => ['required', 'integer', 'exists:' . Therapist::getTableName() . ',id']
        ]);
    }

    public function validators(array $data)
    {
        return Validator::make($data, [
            '*.service_id'   => ['required', 'integer', 'exists:' . Service::getTableName() . ',id'],
            '*.therapist_id' => ['required', 'integer', 'exists:' . Therapist::getTableName() . ',id']
        ]);
    }

    public function service()
    {
        return $this->hasOne('App\Service', 'id', 'service_id');
    }
    
}
