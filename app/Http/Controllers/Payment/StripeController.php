<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use Stripe;
use App\UserCardDetail;
use DB;
use App\Booking;
use App\BookingPayment;

class StripeController extends BaseController {

    public $successMsg = [
        'payment.success' => 'Payment done successfully !',
    ];
    public $errorMsg = [
        'something.went.wrong' => 'Something went wrong !',
        'card.not.found' => 'Card not found !',
        'booking.not.found' => 'Booking not found !',
        'payment.complete' => 'Your payment is already done !',
    ];

    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function bookingPayment(Request $request) {
        $card = UserCardDetail::where(['user_id' => $request->user_id, 'is_default' => UserCardDetail::CARD_DEFAULT])->first();
        $booking = Booking::with('payment')->where(['id' => $request->booking_id, 'user_id' => $request->user_id])->first();

        if (empty($card)) {
            return $this->returnError(__($this->errorMsg['card.not.found']));
        }
        if (empty($booking)) {
            return $this->returnError(__($this->errorMsg['booking.not.found']));
        }

        DB::beginTransaction();
        try {
            if (!empty($booking->payment)) {
                $amount = $booking->payment->remaining_amounts;
            } else {
                if ($booking->payment_type == Booking::PAYMENT_HALF) {
                    $amount = $booking->total_price / 2;
                } else {
                    $amount = $booking->total_price;
                }
            }
            if ($amount > 0) {
                $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
                $token_obj = $stripe->tokens->create([
                    'card' => [
                        'number' => $card->card_number,
                        'exp_month' => $card->exp_month,
                        'exp_year' => $card->exp_year,
                        'cvc' => $card->cvv,
                    ],
                ]);

                try {
                    Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
                    $charge = Stripe\Charge::create([
                                "amount" => $amount * 100,
                                "currency" => "usd",
                                "source" => $token_obj->id,
                                "description" => "Test payment from evolution.com."
                    ]);
                    if ($charge->status == 'succeeded') {
                        $data = [
                            'final_amounts' => $booking->total_price,
                            'paid_amounts' => !empty($booking->payment) ? $booking->payment->remaining_amounts + $amount : $amount,
                            'remaining_amounts' => !empty($booking->payment) ? $booking->payment->remaining_amounts - $amount : $booking->total_price - $amount,
                            'paid_percentage' => !empty($booking->payment) ? (($booking->payment->remaining_amounts - $amount) == 0 ? 100 : 50) : (($booking->total_price - $amount) == 0 ? 100 : 50),
                            'is_success' => '1',
                            'booking_id' => $booking->id,
                            'payment_id' => $charge->id
                        ];
                        BookingPayment::updateOrCreate(['booking_id' => $booking->id], $data);
                    }
                    DB::commit();
                    return $this->returnSuccess(__($this->successMsg['payment.success']), $charge);
                } catch (\Stripe\Exception\CardException $e) {
                    return $this->returnError(__($this->errorMsg['something.went.wrong']), $e->getError()->message);
                } catch (\Stripe\Exception\RateLimitException $e) {
                    return $this->returnError(__($this->errorMsg['something.went.wrong']), $e->getError()->message);
                } catch (\Stripe\Exception\InvalidRequestException $e) {
                    return $this->returnError(__($this->errorMsg['something.went.wrong']), $e->getError()->message);
                } catch (\Stripe\Exception\AuthenticationException $e) {
                    return $this->returnError(__($this->errorMsg['something.went.wrong']), $e->getError()->message);
                } catch (\Stripe\Exception\ApiConnectionException $e) {
                    return $this->returnError(__($this->errorMsg['something.went.wrong']), $e->getError()->message);
                } catch (\Stripe\Exception\ApiErrorException $e) {
                    return $this->returnError(__($this->errorMsg['something.went.wrong']), $e->getError()->message);
                } catch (Exception $e) {
                    return $this->returnError(__($this->errorMsg['something.went.wrong']), $e->getError()->message);
                }
            } else {
                return $this->returnError(__($this->errorMsg['payment.complete']));
            }
        } catch (Exception $e) {
            DB::rollBack();
        }
    }

}
