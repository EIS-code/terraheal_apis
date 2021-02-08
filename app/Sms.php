<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class Sms extends Model
{
    protected $fillable = [
        'to',
        'body',
        'created_at'
    ];

    public function validator(array $data)
    {
        return Validator::make($data, [
            'number' => ['required', 'string', 'max:15'],
            'body'   => ['required', 'string', 'max:160'],
        ]);
    }
}
