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
        'is_default'
    ];

    const CARD_DEFAULT = '1';
    const CARD_NOT_DEFAULT = '0';
    
    public function validator(array $data)
    {
        return Validator::make($data, [
            'holder_name'  => ['required', 'string', 'max:255'],
            'card_number'  => ['required', 'integer', 'unique:user_card_details'],
            'exp_month'    => ['required', 'integer'],
            'exp_year'     => ['required', 'integer'],
            'cvv'          => ['required', 'integer'],
            'user_id'      => ['required', 'integer', 'exists:' . User::getTableName() . ',id']
        ]);
    }
}
