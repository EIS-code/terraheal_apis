<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class Constant extends BaseModel
{
    protected $fillable = [
        'key',
        'value',
        'is_removed'
    ];

    protected $hidden = ['created_at', 'updated_at'];
    
    public function validator(array $data)
    {
        return Validator::make($data, [
            'key'        => ['required', 'string'],
            'value'      => ['required', 'string'],
            'is_removed' => ['integer', 'in:0,1']
        ]);
    }
}
