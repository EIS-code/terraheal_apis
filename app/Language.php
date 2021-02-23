<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class Language extends BaseModel
{
    protected $fillable = [
        'name'
    ];

    public function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255']
        ]);
    }
}
