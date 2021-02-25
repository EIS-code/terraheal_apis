<?php

namespace App;

use App\Shop;
use App\BookingMassage;
use Illuminate\Support\Facades\Validator;

class BookingMassageStart extends BaseModel
{
    protected $fillable = [
        'start_time',
        'end_time',
        'taken_total_time',
        'actual_total_time',
        'booking_massage_id',
        'shop_id'
    ];

    public function validator(array $data, $id = false, $isUpdate = false)
    {
        return Validator::make($data, [
            'start_time'            => ['required', 'string'],
            'end_time'              => ['required', 'string'],
            'taken_total_time'      => ['nullable', 'string'],
            'actual_total_time'     => ['required', 'string'],
            'shop_id'               => ['required', 'integer', 'exists:' . Shop::getTableName() . ',id'],
            'booking_massage_id'    => ['required', 'integer', 'exists:' . BookingMassage::getTableName() . ',id']
        ]);
    }
}
