<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\MassagePrice;
use App\BookingInfo;
use App\MassagePreferenceOption;
use App\BookingMassageStart;

class BookingMassage extends BaseModel
{
    use SoftDeletes;
    
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
        'focus_area_preference',
        'room_id',
        'therapy_id',
        'therapy_timing_id',
        'therapy_prices_id',
        'is_confirm'
    ];
    
    const IS_CONFIRM = '1';
    const IS_NOT_CONFIRM = '0';
    
    public function validator(array $data)
    {
        $pressurePreference = ['required', 'integer', 'in:' . implode(",", MassagePreferenceOption::$massagePressures[1])];
        $genderPreference   = ['required', 'integer', 'in:' . implode(",", MassagePreferenceOption::$massageGenders[2])];
        $focusAreaPreference   = ['required', 'integer', 'in:' . implode(",", MassagePreferenceOption::$massageFocucAreas[8])];
echo "<pre>";
print_r($pressurePreference);
print_r($genderPreference);
exit;
        $validator = Validator::make($data, [
            'price'                                  => ['required', 'between:0,99.99'],
            'cost'                                   => ['required', 'between:0,99.99'],
            'origional_price'                        => ['required', 'between:0,99.99'],
            'origional_cost'                         => ['required', 'between:0,99.99'],
            'exchange_rate'                          => ['required', 'between:0,99.99'],
            'massage_timing_id'                      => ['nullable', 'integer', 'exists:' . MassageTiming::getTableName() . ',id'],
            'therapy_timing_id '                     => ['nullable', 'integer', 'exists:' . TherapiesTimings::getTableName() . ',id'],
            'massage_prices_id'                      => ['nullable', 'integer', 'exists:' . MassagePrice::getTableName() . ',id'],
            'therapy_prices_id'                      => ['nullable', 'integer', 'exists:' . TherapiesPrices::getTableName() . ',id'],
            'notes_of_injuries'                      => ['max:255'],
            'booking_info_id'                        => ['required', 'integer', 'exists:' . BookingInfo::getTableName() . ',id'],
            'pressure_preference'                    => $pressurePreference,
            'gender_preference'                      => $genderPreference,
            'focus_area_preference'                  => $focusAreaPreference,
            'room_id'                                => ['nullable', 'integer', 'exists:' . Room::getTableName() . ',id'],
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

    public function therapyTiming()
    {
        return $this->hasOne('App\TherapiesTimings', 'id', 'therapy_timing_id');
    }

    public function bookingInfo()
    {
        return $this->belongsTo('App\BookingInfo');
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
}
