<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class UserFaq extends BaseModel
{
    protected $fillable = [
        'question',
        'answer',
        'is_removed'
    ];

    public function validator(array $data)
    {
        return Validator::make($data, [
            'question'   => ['required', 'string'],
            'answer'     => ['required', 'string'],
            'is_removed' => ['integer', 'in:0,1']
        ]);
    }
}
