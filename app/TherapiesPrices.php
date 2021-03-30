<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class TherapiesPrices extends BaseModel
{
    protected $fillable = [
        'therapy_id',
        'therapy_timing_id',
        'price',
        'cost'
    ];
    protected $table = 'therapies_prices';
    
    public function validator(array $data)
    {
        return Validator::make($data, [
            'therapy_id'        => ['required', 'integer'],
            'therapy_timing_id' => ['required', 'integer'],
            'price'             => ['required'],
            'cost'              => ['required']
        ]);
    }
}
