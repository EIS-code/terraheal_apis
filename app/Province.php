<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class Province extends BaseModel
{
    protected $fillable = [
        'name',
        'country_id'
    ];

    public function validator(array $data, $id = false, $isUpdate = false)
    {
        return Validator::make($data, [
            'name'       => ['required', 'string', 'max:255'],
            'country_id' => ['required', 'integer']
        ]);
    }

    public function validatorMultiple(array $data, $id = false, $isUpdate = false)
    {
        return Validator::make($data, [
            'name.*'       => ['required', 'string', 'max:255'],
            'country_id.*' => ['required', 'integer']
        ]);
    }

    public function city()
    {
        return $this->hasMany('App\City', 'province_id', 'id');
    }
}
