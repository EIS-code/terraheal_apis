<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class ApiKey extends Model
{
    protected $fillable = [
        'key',
        'is_valid',
        'shop_id'
    ];

    public function validator(array $data, $id = false, $isUpdate = false)
    {
        if ($isUpdate === true && !empty($id)) {
            $keyValidator = ['unique:api_keys,key,' . $id];
        } else {
            $keyValidator = ['unique:api_keys'];
        }

        return Validator::make($data, [
            'key'      => array_merge(['required', 'string', 'max:255'], $keyValidator),
            'is_valid' => ['in:0,1'],
            'shop_id'  => ['required', 'integer']
        ]);
    }
}
