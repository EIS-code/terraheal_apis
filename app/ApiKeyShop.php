<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class ApiKeyShop extends BaseModel
{
    protected $fillable = [
        'key',
        'shop_id'
    ];

    public function validator(array $data, $id = false, $isUpdate = false)
    {
        return Validator::make($data, [
            'key'      => ['required', 'string', 'unique:api_key_shops,key', 'max:255'],
            'shop_id'  => ['required', 'integer']
        ]);
    }
}
