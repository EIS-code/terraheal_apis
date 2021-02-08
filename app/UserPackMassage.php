<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use App\UserPack;

class UserPackMassage extends BaseModel
{
    protected $fillable = [
        'cost',
        'retail',
        'is_removed',
        'user_pack_id'
    ];

    public function validator(array $data, $isUpdate = false)
    {
        return Validator::make($data, [
            'cost'         => ['required', 'between:0,99.99'],
            'retail'       => ['required', 'between:0,99.99'],
            'is_removed'   => ['integer', 'in:0,1'],
            'user_pack_id' => ['required', 'exists:' . UserPack::getTableName() . ',id']
        ]);
    }

    public function massagePrice()
    {
        return $this->hasOne('App\MassagePrice', 'id', 'massage_prices_id');
    }
}
