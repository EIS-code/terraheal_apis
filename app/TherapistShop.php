<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class TherapistShop extends BaseModel
{
    protected $fillable = [
        'therapist_id',
        'shop_id'
    ];

    public function validator(array $data)
    {
        return Validator::make($data, [
            'therapist_id'     => ['required', 'integer', 'exists:' . Therapist::getTableName() . ',id'],
            'shop_id'          => ['required', 'integer', 'exists:' . Shop::getTableName() . ',id'],
        ]);
    }

}
