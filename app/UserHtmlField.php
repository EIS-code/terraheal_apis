<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class UserHtmlField extends BaseModel
{
    protected $fillable = [
        'terms',
        'privacy',
    ];

    public function validator(array $data)
    {
        return Validator::make($data, [
            'terms'   => ['string'],
            'privacy' => ['string'],
        ]);
    }
}
