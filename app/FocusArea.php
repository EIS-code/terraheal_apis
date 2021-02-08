<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class FocusArea extends BaseModel
{
    protected $fillable = [
        'name',
        'is_removed'
    ];

    public function validator(array $data, $id = false, $isUpdate = false)
    {
        return Validator::make($data, [
            'name'       => ['required', 'string', 'max:255'],
            'is_removed' => ['required', 'in:0,1']
        ]);
    }
}
