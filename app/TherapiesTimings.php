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

    public function pricing()
    {
        return $this->hasOne('App\TherapiesPrices', 'therapy_timing_id', 'id');
    }

    public function therapy()
    {
        return $this->hasOne('App\Therapy', 'id', 'therapy_id');
    }
}
