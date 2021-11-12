<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use App\Pack;
use App\Shop;

class PackShop extends BaseModel
{
    protected $fillable = [
        'pack_id',
        'shop_id'
    ];

    public function validator(array $data)
    {
        return Validator::make($data, [
            'pack_id' => ['required', 'integer', 'exists:' . Pack::getTableName() . ',id'],
            'shop_id' => ['required', 'integer',  'exists:' . Shop::getTableName() . ',id']
        ]);
    }
    
    public function pack()
    {
        return $this->hasOne('App\Pack', 'id', 'pack_id');
    }
    public function shop()
    {
        return $this->hasOne('App\Shop', 'id', 'shop_id');
    }
}
