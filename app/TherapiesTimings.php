<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class TherapiesTimings extends BaseModel
{
    protected $fillable = [
        'time',
        'therapy_id',
    ];
    
    protected $table = 'therapies_timings';
    
    public function validator(array $data)
    {
        return Validator::make($data, [
            'time'       => ['required', 'time'],
            'therapy_id' => ['required', 'integer']
        ]);
    }
}
