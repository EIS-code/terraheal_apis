<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class TherapistSelectedTherapy extends BaseModel
{
    protected $fillable = [
        'therapy_id',
        'therapist_id'
    ];

    public function validator(array $data)
    {
        return Validator::make($data, [
            'therapy_id'   => ['required', 'integer'],
            'therapist_id' => ['required', 'integer']
        ]);
    }
}
