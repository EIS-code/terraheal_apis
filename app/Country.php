<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class Country extends BaseModel
{
    protected $fillable = [
        'name',
        'short_name',
        'iso3'
    ];

    public function validator(array $data, $id = false, $isUpdate = false)
    {
        return Validator::make($data, [
            'name'       => ['required', 'string', 'max:255'],
            'short_name' => ['required', 'string', 'max:255'],
            'iso3'       => ['required', 'string', 'max:255']
        ]);
    }

    public function validatorMultiple(array $data, $id = false, $isUpdate = false)
    {
        return Validator::make($data, [
            'name.*'       => ['required', 'string', 'max:255'],
            'short_name.*' => ['string', 'max:255'],
            'iso3.*'       => ['string', 'max:255']
        ]);
    }

    public function province()
    {
        return $this->hasMany('App\Province', 'country_id', 'id');
    }
}
