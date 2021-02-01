<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class UserCardDetail extends Model
{
    protected $fillable = [
        'holder_name',
        'card_number',
        'exp_month',
        'exp_year',
        'cvv',
        'zip_code',
        'user_id'
    ];

    public function validator(array $data, $id = false, $isUpdate = false)
    {
        return Validator::make($data, [
            'holder_name'  => ['required', 'string', 'max:255'],
            'card_number'  => ['required', 'integer', 'unique:user_card_details'],
            'exp_month'    => ['required', 'integer'],
            'exp_year'     => ['required', 'integer'],
            'cvv'          => ['required', 'integer'],
            'zip_code'     => ['required', 'max:20'],
            'user_id'      => ['required', 'integer']
        ]);
    }
}
