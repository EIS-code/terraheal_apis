<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use DB;
use Stripe;

class BookingPayment extends BaseModel
{
    protected $fillable = [
        'paid_amounts',
        'is_success',
        'booking_id',
        'payment_id'
    ];

    public function validator(array $data)
    {
        $validator = Validator::make($data, [
            'paid_amounts'           => ['required'],
            'is_success'             => ['required', 'integer'],
            'payment_id'             => ['nullable', 'string'],
            'booking_id'             => ['required', 'integer']
        ]);

        return $validator;
    }
    
    public function getAmount($booking, $voucher) {
        
        $available_amount = 0;
        if (!empty($voucher)) {
            $total_amount = $booking->total_price;
            $voucher_amount = $voucher->amount;
            if ($booking->payment_type == Booking::PAYMENT_HALF) {
                if($voucher_amount >= $total_amount) {
                    $deduct = $available_amount = $voucher_amount - $total_amount;
                    $amount = $remaining_amount = $deduct / 2;
                } else {
                    $deduct = $total_amount - $voucher_amount;
                    $amount = $remaining_amount = $deduct / 2;
                    $available_amount = 0;
                }
            } else {
                if($voucher_amount >= $total_amount) {
                    $amount = $available_amount = $voucher_amount - $total_amount;
                    $remaining_amount = 0;
                } else {
                    $amount = $total_amount - $voucher_amount;
                    $remaining_amount = 0;
                    $available_amount = 0;
                }
            }
        } else {
            if ($booking->payment_type == Booking::PAYMENT_HALF) {
                $amount = $remaining_amount = $booking->total_price / 2;
            } else {
                $amount = $booking->total_price;
                $remaining_amount = 0;
            }
        }
        
        return ['payable' => $amount, 'remaining_amount' => $remaining_amount, 'available_amount' => $available_amount];
    }
    
    public function bookingPayment(Request $request) {
        $card = UserCardDetail::where(['user_id' => $request->user_id, 'is_default' => UserCardDetail::CARD_DEFAULT])->first();
        $booking = Booking::with('payment')->where(['id' => $request->booking_id, 'user_id' => $request->user_id])->first();
        $voucher_model = new UserGiftVoucher(); 
        $voucher = $voucher_model->where(['id' => $request->voucher_id, 'is_used' => UserGiftVoucher::IS_NOT_USED])->first();
        
        if (empty($card)) {
            return ['isError' => true, 'message' => 'Card not found !'];
        }
        if (empty($booking)) {
            return ['isError' => true, 'message' => 'Booking not found !'];
        }

        DB::beginTransaction();
        try {
            $all_amount = $this->getAmount($booking, $voucher);
            if ($all_amount['payable'] > 0) {               
                try {
                    Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
                    $charge = \Stripe\Charge::create(array(
                                "amount" => $all_amount['payable'] * 100,
                                "currency" => "usd",
                                "customer" => $card->stripe_id,
                                "description" => "Test payment from evolution.com.")
                    );

                    if ($charge->status == 'succeeded') {
                        $data = [
                            'paid_amounts' => $all_amount['payable'],
                            'is_success' => '1',
                            'booking_id' => $booking->id,
                            'payment_id' => $charge->id
                        ];
                        BookingPayment::create($data);
                    }
                    $booking->update(['remaining_price' => $all_amount['remaining_amount']]);
                    $is_used = $all_amount['available_amount'] > 0 ? UserGiftVoucher::IS_NOT_USED : UserGiftVoucher::IS_USED;
                    $voucher->update(['available_amount' => $all_amount['available_amount'], 'is_used' => $is_used]);
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
            $amount = $booking->remaining_price;
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
                            'paid_amounts' => $amount,
                            'is_success' => '1',
                            'booking_id' => $booking->id,
                            'payment_id' => $charge->id
                        ];
                        BookingPayment::create($data);
                    }
                    $remaining  = $booking->remaining_price - $amount;
                    $booking->update(['remaining_price' => $remaining]);
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
