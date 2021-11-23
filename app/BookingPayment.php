<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use DB;
use Stripe;

class BookingPayment extends BaseModel
{
    protected $fillable = [
        'final_amounts',
        'paid_amounts',
        'remaining_amounts',
        'paid_percentage',
        'is_success',
        'booking_id',
        'payment_id'
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
            'payment_id'             => ['nullable', 'string'],
            'booking_id'             => ['required', 'integer']
        ]);

        return $validator;
    }
    
    public function bookingPayment(Request $request) {
        $card = UserCardDetail::where(['user_id' => $request->user_id, 'is_default' => UserCardDetail::CARD_DEFAULT])->first();
        $booking = Booking::with('payment')->where(['id' => $request->booking_id, 'user_id' => $request->user_id])->first();

        if (empty($card)) {
            return ['isError' => true, 'message' => 'Card not found !'];
        }
        if (empty($booking)) {
            return ['isError' => true, 'message' => 'Booking not found !'];
        }

        DB::beginTransaction();
        try {
            if ($booking->payment_type == Booking::PAYMENT_HALF) {
                $amount = $booking->total_price / 2;
            } else {
                $amount = $booking->total_price;
            }
            if ($amount > 0) {               
                try {
                    Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
                    $charge = \Stripe\Charge::create(array(
                                "amount" => $amount * 100,
                                "currency" => "usd",
                                "customer" => $card->stripe_id,
                                "description" => "Test payment from evolution.com.")
                    );

                    if ($charge->status == 'succeeded') {
                        $data = [
                            'final_amounts' => $booking->total_price,
                            'paid_amounts' => $amount,
                            'remaining_amounts' => $booking->total_price - $amount,
                            'paid_percentage' => ($booking->payment_type == Booking::PAYMENT_HALF) ? 50 : 100,
                            'is_success' => '1',
                            'booking_id' => $booking->id,
                            'payment_id' => $charge->id
                        ];
                        BookingPayment::create($data);
                    }
                    DB::commit();
                    return $data;
                } catch (\Stripe\Exception\CardException $e) {
                    return ['isError' => true, 'message' => $e->getError()->message];
                } catch (\Stripe\Exception\RateLimitException $e) {
                    return ['isError' => true, 'message' => $e->getError()->message];
                } catch (\Stripe\Exception\InvalidRequestException $e) {
                    return ['isError' => true, 'message' => $e->getError()->message];
                } catch (\Stripe\Exception\AuthenticationException $e) {
                    return ['isError' => true, 'message' => $e->getError()->message];
                } catch (\Stripe\Exception\ApiConnectionException $e) {
                    return ['isError' => true, 'message' => $e->getError()->message];
                } catch (\Stripe\Exception\ApiErrorException $e) {
                    return ['isError' => true, 'message' => $e->getError()->message];
                } catch (Exception $e) {
                    return ['isError' => true, 'message' => $e->getError()->message];
                }
            } else {
                return ['isError' => true, 'message' => 'Your payment is already done !'];
            }
        } catch (Exception $e) {
            DB::rollBack();
        }
    }
    
    public function bookingHalfPayment(Request $request) {
        $card = UserCardDetail::where(['user_id' => $request->user_id, 'is_default' => UserCardDetail::CARD_DEFAULT])->first();
        $booking = Booking::with('payment')->where(['id' => $request->booking_id, 'user_id' => $request->user_id])->first();

        if (empty($card)) {
            return ['isError' => true, 'message' => 'Card not found !'];
        }
        if (empty($booking)) {
            return ['isError' => true, 'message' => 'Booking not found !'];
        }
        if (empty($booking->payment)) {
            return ['isError' => true, 'message' => 'First payment is remaining !'];
        }

        DB::beginTransaction();
        try {
            $amount = $booking->payment->remaining_amounts;
            if ($amount > 0) {               
                try {
                    Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
                    $charge = \Stripe\Charge::create(array(
                                "amount" => $amount * 100,
                                "currency" => "usd",
                                "customer" => $card->stripe_id,
                                "description" => "Test payment from evolution.com.")
                    );

                    if ($charge->status == 'succeeded') {
                        $data = [
                            'final_amounts' => $booking->total_price,
                            'paid_amounts' => $booking->payment->remaining_amounts + $amount,
                            'remaining_amounts' => $booking->payment->remaining_amounts - $amount,
                            'paid_percentage' => 100,
                            'is_success' => '1',
                            'booking_id' => $booking->id,
                            'payment_id' => $charge->id
                        ];
                        $create = BookingPayment::create($data);
                    }
                    DB::commit();
                    return $data;
                } catch (\Stripe\Exception\CardException $e) {
                    return ['isError' => true, 'message' => $e->getError()->message];
                } catch (\Stripe\Exception\RateLimitException $e) {
                    return ['isError' => true, 'message' => $e->getError()->message];
                } catch (\Stripe\Exception\InvalidRequestException $e) {
                    return ['isError' => true, 'message' => $e->getError()->message];
                } catch (\Stripe\Exception\AuthenticationException $e) {
                    return ['isError' => true, 'message' => $e->getError()->message];
                } catch (\Stripe\Exception\ApiConnectionException $e) {
                    return ['isError' => true, 'message' => $e->getError()->message];
                } catch (\Stripe\Exception\ApiErrorException $e) {
                    return ['isError' => true, 'message' => $e->getError()->message];
                } catch (Exception $e) {
                    return ['isError' => true, 'message' => $e->getError()->message];
                }
            } else {
                return ['isError' => true, 'message' => 'Your payment is already done !'];
            }
        } catch (Exception $e) {
            DB::rollBack();
        }
    }
    
    public function getPayment($id) {
        
        $payment = self::where('booking_id', $id)->get()->last();
        return $payment;
    }
}
