<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use App\Repositories\User\BookingRepository;

class BookingPayment extends Model
{
    protected $fillable = [
        'final_amounts',
        'paid_amounts',
        'remaining_amounts',
        'paid_percentage',
        'is_success',
        'api_responce',
        'currency_id',
        'booking_id',
        'shop_payment_detail_id'
    ];

    public function validator(array $data)
    {
        $validator = Validator::make($data, [
            'paid_amounts'           => ['required'],
            'final_amounts'          => ['required'],
            'paid_amounts'           => ['required'],
            'remaining_amounts'      => ['required'],
            'paid_percentage'        => ['required'],
            'is_success'             => ['required', 'integer'],
            'api_responce'           => ['max:255'],
            'currency_id'            => ['required', 'integer'],
            'booking_id'             => ['required', 'integer'],
            'shop_payment_detail_id' => ['required', 'integer']
        ]);

        return $validator;
    }
}
