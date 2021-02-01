<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class TherapistCalendar extends Model
{
    protected $fillable = [
        'date',
        'time_from',
        'time_to',
        'therapist_id'
    ];

    public function validator(array $data)
    {
        return Validator::make($data, [
            'date'         => ['required', 'date'],
            'time_from'    => ['required', 'date_format:H:i'],
            'time_to'      => ['required', 'date_format:H:i'],
            'therapist_id' => ['required', 'integer']
        ]);
    }
}
