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
}
