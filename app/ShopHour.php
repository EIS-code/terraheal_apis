<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use App\Shop;

class ShopHour extends Model
{
    protected $fillable = [
        'day_name',
        'is_open',
        'open_at',
        'close_at',
        'shop_id'
    ];
    
    protected $hidden = ['created_at', 'updated_at'];
    
    public $shopDays = [
        '0' => 'Sunday',
        '1' => 'Monday',
        '2' => 'Tuesday',
        '3' => 'Wednesday',
        '4' => 'Thursday',
        '5' => 'Friday',
        '6' => 'Saturday'
    ];
    const IS_OPEN = 1;

    public function validator(array $data)
    {
        return Validator::make($data, [
            'day_name'            => ['nullable', 'in:' . implode(",", array_keys($this->shopDays))],
            'open_time'           => ['nullable', 'date_format:H:i'],
            'close_time'          => ['nullable', 'date_format:H:i'],
            'shop_id'             => ['required', 'integer', 'exists:' . Shop::getTableName() . ',id']
        ]);
    }
    
    public function getDayNameAttribute($value)
    {
        if (is_null($value)) {
            return $value;
        }

        return $this->shopDays[$value];
    }
}
