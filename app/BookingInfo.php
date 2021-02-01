<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use App\UserPeople;
use App\Therapist;
use App\Room;

class BookingInfo extends BaseModel
{
    protected $fillable = [
        'location',
        'massage_date',
        'massage_time',
        'is_cancelled',
        'cancelled_reason',
        'imc_type',
        'is_done',
        'booking_currency_id',
        'shop_currency_id',
        'therapist_id',
        'massage_prices_id',
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
            '*.user_people_id'       => ['required', 'integer', 'exists:' . UserPeople::getTableName() . ',id'],
            '*.location'             => ['required', 'max:255'],
            '*.massage_date'         => ['nullable'],
            '*.massage_time'         => ['nullable'],
            '*.is_cancelled'         => ['in:' . implode(",", array_keys(self::$isCancelled))],
            '*.cancelled_reason'     => ['mas:255'],
            '*.imc_type'             => ['required', 'in:1,2'],
            '*.therapist_id'         => ['required', 'integer', 'exists:' . Therapist::getTableName() . ',id'],
            '*.room_id'              => ['required', 'integer', 'exists:' . Room::getTableName() . ',id'],
            '*.massage_info'         => ['required', 'array']
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

    public function userPeople()
    {
        return $this->hasOne('App\UserPeople', 'id', 'user_people_id');
    }
}
