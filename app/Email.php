<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class Email extends Model
{
    protected $fillable = [
        'from',
        'to',
        'cc',
        'bcc',
        'subject',
        'content',
        'is_send',
        'exception_info',
        'created_at'
    ];

    public function validator(array $data)
    {
        return Validator::make($data, [
            'to'      => ['required', 'string', 'email'],
            'subject' => ['required', 'string'],
            'body'    => ['required'],
        ]);
    }
}
