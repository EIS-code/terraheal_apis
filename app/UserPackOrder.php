<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use App\User;
use App\UserPack;

class UserPackOrder extends BaseModel
{
    protected $fillable = [
        'is_removed',
        'user_pack_id',
        'user_id'
    ];

    public function validator(array $data, $isUpdate = false)
    {
        return Validator::make($data, [
            'is_removed'   => ['integer', 'in:0,1'],
            'user_id'      => ['required', 'integer', 'exists:' . User::getTableName() . ',id'],
            'user_pack_id' => ['required', 'integer', 'exists:' . UserPack::getTableName() . ',id']
        ]);
    }

    public function userPack()
    {
        return $this->hasOne('App\UserPack', 'id', 'user_pack_id');
    }
}
