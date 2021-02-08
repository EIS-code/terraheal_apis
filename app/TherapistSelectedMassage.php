<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class TherapistSelectedMassage extends BaseModel
{
    protected $fillable = [
        'massage_id',
        'therapist_id'
    ];

    public function validator(array $data)
    {
        return Validator::make($data, [
            'massage_id'   => ['required', 'integer'],
            'therapist_id' => ['required', 'integer']
        ]);
    }
}
