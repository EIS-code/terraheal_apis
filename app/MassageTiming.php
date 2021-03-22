<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class MassageTiming extends BaseModel
{
    protected $fillable = [
        'time',
        'massage_id',
    ];

    public function validator(array $data)
    {
        return Validator::make($data, [
            'time'       => ['required', 'time'],
            'massage_id' => ['required', 'integer']
        ]);
    }

    public function pricing()
    {
        return $this->hasOne('App\MassagePrice', 'massage_timing_id', 'id');
    }

    public function massage()
    {
        return $this->hasOne('App\Massage', 'id', 'massage_id');
    }
}
