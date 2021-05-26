<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class Manager extends BaseModel
{
    protected $fillable = [
        'name',
        'surname',
        'address',
        'email',
        'password',
        'image',
        'shop_id',
        'city_id',
        'province_id',
        'country_id',
    ];

    protected $table = 'manager';
    protected $hidden = ['password', 'remember_token', 'created_at', 'updated_at'];


    public function validator(array $data, $id = false, $isUpdate = false)
    {
        $user = NULL;
        if ($isUpdate === true && !empty($id)) {
            $emailValidator      = ['unique:manager,email,' . $id];
        } else {
            $emailValidator      = ['unique:manager'];
        }

        return Validator::make($data, [
            'name'                => ['nullable', 'string', 'max:255'],
            'surname'             => ['nullable', 'string', 'max:255'],
            'address'             => ['nullable', 'string', 'max:255'],
            'email'               => array_merge(['required', 'string', 'email', 'max:255'], $emailValidator),
            'password'            => ['required', 'string', 'max:255'],
            'shop_id'            => ['required', 'integer', 'exists:' . Shop::getTableName() . ',id'],
            'city_id'             => ['nullable'],
            'province_id'         => ['nullable'],
            'country_id'          => ['nullable'],
            'currency_id'         => ['nullable'],
        ]);
    }   
}
