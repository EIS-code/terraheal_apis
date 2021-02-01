<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use App\Shop;

class ShopHour extends Model
{
    protected $fillable = [
        'sunday',
        'monday',
        'tuesday',
        'wednesday',
        'thursday',
        'friday',
        'saturday',
        'shop_id'
    ];

    public function validator(array $data)
    {
        return Validator::make($data, [
            'sunday'    => ['in:0,1'],
            'monday'    => ['in:0,1'],
            'tuesday'   => ['in:0,1'],
            'wednesday' => ['in:0,1'],
            'thursday'  => ['in:0,1'],
            'friday'    => ['in:0,1'],
            'saturday'  => ['in:0,1'],
            'shop_id'   => ['required', 'integer', 'exists:' . Shop::getTableName() . ',id']
        ]);
    }
}
