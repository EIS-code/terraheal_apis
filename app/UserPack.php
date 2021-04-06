<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use App\Therapist;
use App\Pack;
use App\User;

class UserPack extends BaseModel
{
    protected $fillable = [
        'pack_id',
        'therapist_id',
        'users_id',
        'purchase_date'
    ];

    public function validator(array $data)
    {
        return Validator::make($data, [
            'pack_id'         => ['required', 'integer', 'exists:' . Pack::getTableName() . ',id'],
            'therapist_id'    => ['required', 'integer', 'exists:' . Therapist::getTableName() . ',id'],
            'users_id'        => ['required', 'integer', 'exists:' . User::getTableName() . ',id'],
            'purchase_date'   => ['required']
        ]);
    }

    public function massages()
    {
        return $this->hasMany('App\UserPackMassage', 'user_pack_id', 'id');
    }

    public function getPurchasedPacks($userId)
    {
        return $this->hasMany('App\UserPackOrder', 'user_pack_id', 'id')->where('user_id', (int)$userId);
    }
}
