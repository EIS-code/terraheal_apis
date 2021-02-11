<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class ApiKey extends BaseModel
{
    protected $fillable = [
        'key',
        'type',
        'model_id',
        'api_key_id'
    ];

    const TYPE_NONE = '0';
    const TYPE_USERS = '1';
    const TYPE_THERAPISTS = '2';
    const TYPE_FREELANCER_THERAPISTS = '3';
    const TYPE_SHOPS = '4';
    public $types = [
        self::TYPE_NONE => 'None',
        self::TYPE_USERS => 'Users',
        self::TYPE_THERAPISTS => 'Therapists',
        self::TYPE_FREELANCER_THERAPISTS => 'Freelancer Therapists',
        self::TYPE_SHOPS => 'Shops'
    ];

    public function validator(array $data, $id = false, $isUpdate = false)
    {
        return Validator::make($data, [
            'key'           => ['required', 'string', 'unique:api_keys,key', 'max:255'],
            'type'          => ['in:' . implode(",", $this->types)],
            'model_id'      => ['required', 'integer'],
            'api_key_id'    => ['required', 'integer']
        ]);
    }
}
