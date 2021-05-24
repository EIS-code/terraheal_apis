<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class News extends BaseModel
{
    protected $fillable = [
        'title',
        'sub_title',
        'description',
        'manager_id'
    ];

    public function validator(array $data)
    {
        return Validator::make($data, [
            'title'         => ['required', 'string', 'max:255'],
            'sub_title'     => ['string', 'max:255'],
            'description'   => ['string'],
            'manager_id'    => ['integer',  'exists:' . Manager::getTableName() . ',id'],
        ]);
    }
}
