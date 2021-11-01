<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class Room extends BaseModel
{
    protected $fillable = [
        'name',
        'shop_id',
        'total_beds'
    ];

    public function validator(array $data)
    {
        return Validator::make($data, [
            'name'    => ['required', 'string', 'max:255'],
            'shop_id' => ['required', 'integer']
        ]);
    }
    
    public function shop()
    {
        return $this->hasOne('App\Shop', 'id', 'shop_id');
    }
}
