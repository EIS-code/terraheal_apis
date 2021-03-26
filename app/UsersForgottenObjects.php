<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class UsersForgottenObjects extends BaseModel
{
    protected $fillable = [
        "user_id",
        'forgotten_object',
        'shop_id',
        "room_id",
        "is_client_informed",
        "is_returned"
    ];

    public function validator(array $data)
    {
        return Validator::make($data, [
            'forgotten_object'    => ['required', 'string', 'max:255'],
            'shop_id' => ['required', 'integer'],
            'room_id' => ['required', 'integer'],
            'is_client_informed' => ['required', 'integer'],
            'is_returned' => ['required', 'integer']
        ]);
    }
}
