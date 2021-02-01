<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class MassagePrice extends BaseModel
{
    protected $fillable = [
        'massage_id',
        'massage_timing_id',
        'price',
        'cost'
    ];

    public function validator(array $data)
    {
        return Validator::make($data, [
            'massage_id'        => ['required', 'integer'],
            'massage_timing_id' => ['required', 'integer'],
            'price'             => ['required'],
            'cost'              => ['required']
        ]);
    }

    public function massage()
    {
        return $this->hasOne('App\Massage', 'id', 'massage_id');
    }

    public function timing()
    {
        return $this->hasOne('App\MassageTiming', 'id', 'massage_timing_id');
    }
}
