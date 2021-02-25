<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use App\MassagePrice;
use App\BookingInfo;
use App\MassagePreferenceOption;

class BookingMassage extends BaseModel
{
    protected $fillable = [
        'price',
        'cost',
        'origional_price',
        'origional_cost',
        'exchange_rate',
        'notes_of_injuries',
        'massage_timing_id',
        'massage_prices_id',
        'booking_info_id',
        'pressure_preference',
        'gender_preference',
        'focus_area_preference'
    ];

    public function validator(array $data, $excludeBookingInfoId = false, $bookingType)
    {
        $pressurePreference = $genderPreference = ['nullable'];

        if ($bookingType == '1') {
            $pressurePreference = ['required', 'integer', 'in:' . implode(",", MassagePreferenceOption::$massagePressures[1])];
            $genderPreference   = ['required', 'integer', 'in:' . implode(",", MassagePreferenceOption::$massageGenders[2])];
        }

        $validator = Validator::make($data, [
            /*'price'                                => ['required', 'between:0,99.99'],
            'cost'                                   => ['required', 'between:0,99.99'],
            'origional_price'                        => ['required', 'between:0,99.99'],
            'origional_cost'                         => ['required', 'between:0,99.99'],
            'exchange_rate'                          => ['required', 'between:0,99.99'],
            'massage_timing_id'                      => ['required', 'integer', 'exists:' . MassageTiming::getTableName() . ',id'],*/
            '*.massage_info.*.notes_of_injuries'     => ['max:255'],
            '*.massage_info.*.massage_prices_id'     => ['required', 'integer', 'exists:' . MassagePrice::getTableName() . ',id'],
            'booking_info_id'                        => ($excludeBookingInfoId) ? [] : ['required', 'integer', 'exists:' . BookingInfo::getTableName() . ',id'],
            '*.massage_info.*.pressure_preference'   => $pressurePreference,
            '*.massage_info.*.gender_preference'     => $genderPreference,
            '*.massage_info.*.focus_area_preference' => ['required', 'integer', 'in:' . implode(",", MassagePreferenceOption::$massageFocucAreas[8])]
        ]);

        return $validator;
    }

    public function massagePrices()
    {
        return $this->hasOne('App\MassagePrice', 'id', 'massage_prices_id');
    }

    public function massageTiming()
    {
        return $this->hasOne('App\MassageTiming', 'id', 'massage_timing_id');
    }

    public static function getMassageTime(int $id)
    {
        $data = self::find($id);
        $time = NULL;

        if (!empty($data)) {
            $time = $data->massageTiming->time;
        }

        return $time;
    }
}
