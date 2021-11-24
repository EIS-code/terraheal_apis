<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class UserCardDetail extends BaseModel
{
    protected $fillable = [
        'holder_name',
        'card_number',
        'exp_month',
        'exp_year',
        'cvv',
        'user_id',
        'is_default',
        'stripe_token',
        'stripe_id'
    ];

    const CARD_DEFAULT = '1';
    const CARD_NOT_DEFAULT = '0';
    
    public function validator(array $data)
    {
        return Validator::make($data, [
            'holder_name'  => ['required', 'string', 'max:255'],
            'card_number'  => ['required', 'integer'],
            'exp_month'    => ['required', 'integer'],
            'exp_year'     => ['required', 'integer'],
            'cvv'          => ['nullable', 'integer'],
            'stripe_token' => ['nullable', 'string', 'max:255'],
            'stripe_id'    => ['nullable', 'string', 'max:255'],
            'user_id'      => ['required', 'integer', 'exists:' . User::getTableName() . ',id']
        ]);
    }
}
