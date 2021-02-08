<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class City extends BaseModel
{
    protected $fillable = [
        'name',
        'country_id'
    ];

    public function validator(array $data, $id = false, $isUpdate = false)
    {
        return Validator::make($data, [
            'name'       => ['required', 'string', 'max:255'],
            'province_id' => ['required', 'integer'],
            'country_id' => ['required', 'integer']
        ]);
    }

    public function validatorMultiple(array $data, $id = false, $isUpdate = false)
    {
        return Validator::make($data, [
            'name.*'        => ['required', 'string', 'max:255'],
            'province_id.*' => ['required', 'integer'],
            'country_id.*'  => ['integer']
        ]);
    }

    public function country()
    {
        return $this->belongsTo('App\Country', 'country_id', 'id');
    }

    public function province()
    {
        return $this->belongsTo('App\Province', 'province_id', 'id');
    }
}
