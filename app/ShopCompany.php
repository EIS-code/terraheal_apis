<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use App\Country;
use App\Province;
use App\City;
use App\Shop;

class ShopCompany extends Model
{
    protected $fillable = [
        'name',
        'nif',
        'address',
        'city_id',
        'province_id',
        'country_id',
        'longitude',
        'latitude',
        'zoom',
        'shop_id'
    ];

    protected $hidden = ['created_at', 'updated_at'];
    
    public function validator(array $data)
    {
        return Validator::make($data, [
            'name'        => ['required', 'string', 'max:255'],
            'nif'         => ['nullable', 'string', 'max:255'],
            'address'     => ['nullable', 'string'],
            'city_id'     => ['nullable', 'integer', 'exists:' . City::getTableName() . ',id'],
            'province_id' => ['nullable', 'integer', 'exists:' . Province::getTableName() . ',id'],
            'country_id'  => ['nullable', 'integer', 'exists:' . Country::getTableName() . ',id'],
            'longitude'   => ['nullable'],
            'latitude'    => ['nullable'],
            'zoom'        => ['nullable'],
            'shop_id'     => ['required', 'integer', 'exists:' . Shop::getTableName() . ',id']
        ]);
    }
}
