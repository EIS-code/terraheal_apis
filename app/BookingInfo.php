<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use App\UserPeople;
use App\Therapist;
use App\Room;
use App\BookingMassage;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class BookingInfo extends BaseModel
{
    protected $fillable = [
        'location',
        'massage_date',
        'massage_time',
        'is_cancelled',
        'cancel_type',
        'cancelled_reason',
        'imc_type',
        'is_done',
        'booking_currency_id',
        'shop_currency_id',
        'therapist_id',
        'user_people_id',
        'room_id',
        'booking_id'
    ];

    const PREFERENCE_MALE   = 'm';
    const PREFERENCE_FEMALE = 'F';
    public static $preferenceTypes = [
        self::PREFERENCE_MALE   => 'Male',
        self::PREFERENCE_FEMALE => 'Female'
    ];

    const IS_CANCELLED_YES  = 1;
    const IS_CANCELLED_NOPE = 0;
    public static $isCancelledTypes = [
        self::IS_CANCELLED_YES  => 'Yes',
        self::IS_CANCELLED_NOPE => 'Nope'
    ];

    const IMC_TYPE_ASAP      = 1;
    const IMC_TYPE_SCHEDULED = 2;
    public static $imcTypes = [
        self::IMC_TYPE_ASAP      => 'ASAP',
        self::IMC_TYPE_SCHEDULED => 'Scheduled'
    ];

    const IS_DONE     = 1;
    const IS_NOT_DONE = 0;
    public static $isDone = [
        self::IS_DONE     => 'Done',
        self::IS_NOT_DONE => 'Not done yet'
    ];

    const DEFAULT_BRING_TABLE_FUTON    = '0';
    const DEFAULT_TABLE_FUTON_QUANTITY = '0';

    const IS_CANCELLED     = '1';
    const IS_NOT_CANCELLED = '0';

    public static $isCancelled = [
        self::IS_CANCELLED     => 'Canceled',
        self::IS_NOT_CANCELLED => 'Not Canceled'
    ];

    public function validator(array $data)
    {
        return Validator::make($data, [
            'user_people_id'       => ['nullable', 'integer', 'exists:' . UserPeople::getTableName() . ',id'],
            'location'             => ['nullable', 'max:255'],
            'massage_date'         => ['nullable'],
            'massage_time'         => ['nullable'],
            'is_cancelled'         => ['in:' . implode(",", array_keys(self::$isCancelled))],
            'cancelled_reason'     => ['mas:255'],
            'imc_type'             => ['nullable', 'in:1,2'],
            'therapist_id'         => ['nullable', 'integer', 'exists:' . Therapist::getTableName() . ',id'],
            'booking_currency_id'  => ['required', 'integer', 'exists:' . Currency::getTableName() . ',id'],
            'shop_currency_id'     => ['required', 'integer', 'exists:' . Currency::getTableName() . ',id'],
            'booking_id'           => ['required', 'integer', 'exists:' . Booking::getTableName() . ',id'],
        ]);
    }

    public function getMassageTimeAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        return strtotime($value) * 1000;
    }

    public function getMassageDateAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        return strtotime($value) * 1000;
    }

    public function booking()
    {
        return $this->belongsTo('App\Booking', 'booking_id', 'id');
    }

    public function bookingMassages()
    {
        return $this->hasMany('App\BookingMassage', 'booking_info_id', 'id');
    }

    public function massagePrice()
    {
        return $this->belongsTo('App\MassagePrice', 'massage_prices_id', 'id');
    }

    public function therapist()
    {
        return $this->hasOne('App\Therapist', 'id', 'therapist_id');
    }

    public function therapistWhereShop()
    {
        $shopId = request()->get('shop_id', false);

        return $this->hasOne('App\Therapist', 'id', 'therapist_id')->where('shop_id', (int)$shopId);
    }

    public function therapistWhereId()
    {
        $id = request()->get('therapist_id', false);

        return $this->hasOne('App\Therapist', 'id', 'therapist_id')->where('therapist_id', (int)$id);
    }

    public function therapistWhereShopAndId()
    {
        $shopId = request()->get('shop_id', false);
        $id     = request()->get('therapist_id', false);

        return $this->hasOne('App\Therapist', 'id', 'therapist_id')->where('shop_id', (int)$shopId)->where('id', (int)$id);
    }

    public function userPeople()
    {
        return $this->hasOne('App\UserPeople', 'id', 'user_people_id');
    }

    public function filterDatas(Builder $query, $type = 'today')
    {
        $request        = request();
        $now            = Carbon::now();
        $therapistId    = (int)$request->get('id', false);
        $type           = (strpos($request->path(), 'today') !== false) ? 'today' : ((strpos($request->path(), 'future') !== false) ? 'future' : 'past');
        $massageDate    = $type == 'today' ? NULL : $request->get('massage_date');
        $massageDate    = (!empty($massageDate)) ? Carbon::createFromTimestampMs($massageDate)->format('Y-m-d') : NULL;

        switch ($type) {
            case 'future':
                $query->whereDate('massage_date', '>=', $now);
                break;
            case 'past':
                $query->whereDate('massage_date', '<', $now);
                break;
            case 'today':
            default:
                $query->whereDate('massage_date', '=', $now);
                break;
        }

        $query->where('therapist_id', $therapistId);

        if (!empty($massageDate)) {
            switch ($type) {
                case 'future':
                    $query->whereDate('massage_date', '>=', $massageDate);
                    break;
                case 'past':
                    $query->whereDate('massage_date', '<=', $massageDate);
                    break;
                case 'today':
                default:
                    $query->whereDate('massage_date', '=', $massageDate);
                    break;
            }
        }

        return $query;
    }

    public static function getCalender(int $therapistId, $month = NULL)
    {
        $currentMonth   = Carbon::now();
        $month          = empty($month) ? $currentMonth : new Carbon($month / 1000);
        $startDate      = $month->format('Y') . '-' . $month->format('m') . '-01';
        $endDate        = $month->format('Y') . '-' . $month->format('m') . '-' . $month->endOfMonth()->format('d');
        $return = [];

        $data   = self::select('massage_date', 'massage_time', 'id as booking_info_id', 'id')
                      ->has('therapistWhereShop')
                      ->has('bookingMassages')
                      ->with(['bookingMassages' => function($query) {
                          $query->select('booking_info_id', 'service_pricing_id')
                                ->with('servicePrices');
                      }])
                      ->where('therapist_id', $therapistId)
                      ->whereBetween('massage_date', [$startDate, $endDate])
                      ->get();
        if (!empty($data) && !$data->isEmpty()) {
            foreach ($data as $record) {
                if (!empty($record->bookingMassages) && !$record->bookingMassages->isEmpty()) {
                    foreach ($record->bookingMassages as $bookingMassage) {
                        if (empty($bookingMassage->servicePrices)) {
                            continue;
                        }
                        $timing = ServiceTiming::where('id', $bookingMassage->servicePrices->service_timing_id)->first();
                        $return[] = [
                            'massage_date'      => $record->massage_date,
                            'massage_time'      => $record->massage_time,
                            'booking_info_id'   => $record->booking_info_id,
                            'time'              => (int)$timing->time
                        ];
                    }
                }
            }
        }

        return collect($return);
    }

    public function getMassageCountByTherapist(int $therapistId)
    {
        $model = new BookingMassage();

        $model->setMysqlStrictFalse();

        $data = $model::join(self::getTableName(), BookingMassage::getTableName() . '.booking_info_id', '=', self::getTableName() . '.id')
                              ->join(ServicePricing::getTableName(), ServicePricing::getTableName().'.id', BookingMassage::getTableName().'.service_pricing_id')
                              ->join(Service::getTableName(), Service::getTableName().'.id', ServicePricing::getTableName().'.service_id')
                              ->where('therapist_id', $therapistId)->where('is_cancelled', (string)self::IS_CANCELLED_NOPE)
                              ->whereNotNull(BookingMassage::getTableName() . '.service_pricing_id')
                              ->where(Service::getTableName().'.service_type', Service::MASSAGE)
                              ->groupBy(BookingMassage::getTableName() . '.service_pricing_id')
                              ->get()
                              ->count();

        $model->setMysqlStrictTrue();

        return $data;
    }

    public function getTherapyCountByTherapist(int $therapistId)
    {
        $model = new BookingMassage();

        $model->setMysqlStrictFalse();

        $data = $model::join(self::getTableName(), BookingMassage::getTableName() . '.booking_info_id', '=', self::getTableName() . '.id')
                              ->join(ServicePricing::getTableName(), ServicePricing::getTableName().'.id', BookingMassage::getTableName().'.service_pricing_id')
                              ->join(Service::getTableName(), Service::getTableName().'.id', ServicePricing::getTableName().'.service_id')
                              ->where('therapist_id', $therapistId)->where('is_cancelled', (string)self::IS_CANCELLED_NOPE)
                              ->whereNotNull(BookingMassage::getTableName() . '.service_pricing_id')
                              ->where(Service::getTableName().'.service_type', Service::THERAPY)
                              ->groupBy(BookingMassage::getTableName() . '.service_pricing_id')
                              ->get()
                              ->count();

        $model->setMysqlStrictTrue();

        return $data;
    }

    public static function massageDone(int $id)
    {
        $find = self::find($id);

        if (!empty($find)) {
            $find->is_done = (string)self::IS_DONE;

            return $find->update();
        }

        return false;
    }
}
