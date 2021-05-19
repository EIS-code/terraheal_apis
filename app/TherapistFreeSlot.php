<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class TherapistFreeSlot extends BaseModel
{
    protected $fillable = [
        'therapist_id',
        'startTime',
        'endTime'
    ];

    public function validator(array $data)
    {
        return Validator::make($data, [
            'therapist_id'      => ['required', 'integer', 'exists:' . Therapist::getTableName() . ',id'],
            'startTime'         => ['required', 'date:H:i:s'],
            'endTime'           => ['required', 'date:H:i:s']
        ]);
    }
    
    public function getStartTimeAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        return strtotime($value) * 1000;
    }
    
    public function getEndTimeAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        return strtotime($value) * 1000;
    }
}
