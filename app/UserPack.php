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
        'user_id',
        'purchase_date',
        'payment_id'
    ];
        
    public function validator(array $data)
    {
        return Validator::make($data, [
            'pack_id'         => ['required', 'integer', 'exists:' . Pack::getTableName() . ',id'],
            'user_id'         => ['required', 'integer', 'exists:' . User::getTableName() . ',id'],
            'purchase_date'   => ['required']
        ]);
    }

    public function getPurchaseDateAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        return strtotime($value) * 1000;
    }
    
    public function pack()
    {
        return $this->hasOne('App\Pack', 'id', 'pack_id');
    }
}
