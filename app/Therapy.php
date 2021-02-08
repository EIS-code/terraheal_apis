<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class Therapy extends BaseModel
{
    protected $fillable = [
        'name',
        'image'
    ];

    public function validator(array $data)
    {
        return Validator::make($data, [
            'name'  => ['required', 'string', 'max:255'],
            'image' => ['string', 'max:255']
        ]);
    }
}
