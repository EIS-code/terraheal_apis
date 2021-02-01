<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use App\Shop;

class UserPack extends BaseModel
{
    protected $fillable = [
        'title',
        'description',
        'expire_date',
        'actual_amount',
        'save_amount',
        'retail_amount',
        'unique_id',
        'is_removed',
        'shop_id'
    ];

    public function validator(array $data, $isUpdate = false)
    {
        return Validator::make($data, [
            'title'         => ['required', 'string', 'max:255'],
            'description'   => ['required', 'string'],
            'expire_date'   => ['required', 'date_format:Y-m-d'],
            'actual_amount' => ['required', 'between:0,99.99'],
            'save_amount'   => ['required', 'between:0,99.99'],
            'retail_amount' => ['required', 'between:0,99.99'],
            'unique_id'     => ['required', 'unique:' . self::getTableName() . ',unique_id'],
            'is_removed'    => ['integer', 'in:0,1'],
            'shop_id'       => ['required', 'exists:' . Shop::getTableName() . ',id']
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

    public function getExpireDateAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        return strtotime($value) * 1000;
    }
}
