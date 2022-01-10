<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\BookingInfo;
use App\MassagePreferenceOption;
use App\BookingMassageStart;
use Carbon\Carbon;

class BookingMassage extends BaseModel
{
    use SoftDeletes;
    
    protected $fillable = [
        'massage_date_time',
        'actual_date_time',
        'price',
        'cost',
        'origional_price',
        'origional_cost',
        'exchange_rate',
        'notes_of_injuries',
        'booking_info_id',
        'pressure_preference',
        'gender_preference',
        'focus_area_preference',
        'room_id',
        'therapy_id',
        'service_pricing_id',
        'is_confirm',
        'observation',
        'language_id',
        'therapist_id'
    ];
    
    const IS_CONFIRM = '1';
    const IS_NOT_CONFIRM = '0';
    
    public function validator(array $data)
    {
        $pressurePreference = ['required', 'integer', 'in:' . implode(",", MassagePreferenceOption::$massagePressures[1])];
        $genderPreference   = ['nullable', 'integer', 'in:' . implode(",", MassagePreferenceOption::$massageGenders[2])];
        $focusAreaPreference   = ['required', 'integer', 'in:' . implode(",", MassagePreferenceOption::$massageFocucAreas[8])];

        $validator = Validator::make($data, [
            'price'                                  => ['required', 'between:0,99.99'],
            'cost'                                   => ['required', 'between:0,99.99'],
            'origional_price'                        => ['required', 'between:0,99.99'],
            'origional_cost'                         => ['required', 'between:0,99.99'],
            'exchange_rate'                          => ['required', 'between:0,99.99'],
            'service_pricing_id'                     => ['nullable', 'integer', 'exists:' . ServicePricing::getTableName() . ',id'],
            'notes_of_injuries'                      => ['nullable', 'string', 'max:255'],
            'booking_info_id'                        => ['required', 'integer', 'exists:' . BookingInfo::getTableName() . ',id'],
            'massage_date_time'                      => ['required'],
            'pressure_preference'                    => $pressurePreference,
            'gender_preference'                      => $genderPreference,
            'focus_area_preference'                  => $focusAreaPreference,
            'room_id'                                => ['nullable', 'integer', 'exists:' . Room::getTableName() . ',id'],
            'language_id'                            => ['nullable', 'integer', 'exists:' . Language::getTableName() . ',id'],
            'therapist_id'                           => ['nullable', 'integer', 'exists:' . Therapist::getTableName() . ',id'],
        ]);

        return $validator;
    }

    public function servicePrices()
    {
        return $this->hasOne('App\ServicePricing', 'id', 'service_pricing_id')->with('service');
    }

    public function getMassageDateTimeAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        return strtotime($value) * 1000;
    }
    
    public function getActualDateTimeAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        return strtotime($value) * 1000;
    }
    
    public function bookingInfo()
    {
        return $this->belongsTo('App\BookingInfo');
    }

    public static function getMassageTime(int $id)
    {
        $data = self::find($id);
        $time = NULL;

        if (!empty($data) && !is_null($data->servicePrices)) {
            $time = ServiceTiming::where('id', $data->servicePrices->service_timing_id)->first();
            $time = $time->time;
        }

        return $time;
    }

    public function getServiceStatus()
    {
        $id   = $this->id;

        $find = BookingMassageStart::where('booking_massage_id', $id)->first();

        $bookingInfo = $this->bookingInfo;

        if ($bookingInfo->is_done == (string)BookingInfo::IS_DONE) {
            return 2;
        } elseif (!empty($find)) {
            return 1;
        } elseif ($bookingInfo->is_done == (string)BookingInfo::IS_NOT_DONE) {
            return 0;
        }

        return 0;
    }
    
    public function addBookingMassages($service, $bookingInfo, $request, $user) {
        
        $bookingMassageModel = new BookingMassage();     
        $servicePrice = ServicePricing::where(['service_timing_id' => $service['service_timing_id']])->first();
        if(empty($servicePrice)) {
                return ['isError' => true, 'message' => 'Service price not found'];
        }
        
        $injuries = !empty($user['notes_of_injuries']) ? $user['notes_of_injuries'] : (!empty($request->notes_of_injuries) ? $request->notes_of_injuries : NULL);

        $date = !empty($request->massage_date_time) ? Carbon::createFromTimestampMs($request->massage_date_time) : NULL;

        $bookingMassageData = [
            "massage_date_time" => $date,
            "price" => $servicePrice->price,
            "cost" => $servicePrice->cost,
            "origional_price" => $servicePrice->price,
            "origional_cost"  => $servicePrice->cost,
            "exchange_rate" => isset($service['exchange_rate']) ? $service['exchange_rate'] : 0.00,
            "notes_of_injuries" => $injuries,
            "service_pricing_id" => $servicePrice->id,
            "booking_info_id" => $bookingInfo->id,
            "pressure_preference" => $service['pressure_preference'],
            "gender_preference" => !empty($service['gender_preference']) ? $service['gender_preference'] : (!empty($request->gender_preference) ? $request->gender_preference : NULL),
            "focus_area_preference" => !empty($service['focus_area_preference']) ? $service['focus_area_preference'] : NULL,
            "language_id" => !empty($request->language_id) ? $request->language_id : NULL,
        ];

        $checks = $bookingMassageModel->validator($bookingMassageData);

        if ($checks->fails()) {
            return ['isError' => true, 'message' => $checks->errors()->first()];
        }

        // Deduct voucher price if use it.
        $voucherId = $request->get('voucher_id', null);

        if (!empty($voucherId)) {
            $voucher = UserGiftVoucher::find($voucherId);

            if (!empty($voucher)) {
                $availableAmount    = (float)$voucher->available_amount;
                $serviceRetailPrice = (float)$servicePrice->price;
                if ($availableAmount < $serviceRetailPrice) {
                    return ['isError' => true, 'message' => __('Service price more than voucher\'s available price.')];
                } elseif ($voucher->expired_date < ($date->timestamp * 1000)) {
                    return ['isError' => true, 'message' => __('Given voucher is expired.')];
                } elseif ($serviceRetailPrice > 0) {
                    $voucher->available_amount = $availableAmount - $serviceRetailPrice;

                    $voucher->save();
                }
            }
        }

        $is_done = BookingMassage::updateOrCreate(["service_pricing_id" => $servicePrice->id,"booking_info_id" => $bookingInfo->id], $bookingMassageData);
        return ['is_done' => $is_done, 'price' => $servicePrice->price];
    }
}
