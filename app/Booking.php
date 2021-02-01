<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use App\User;
use App\Shop;
use App\SessionType;

class Booking extends BaseModel
{
    protected $fillable = [
        'booking_type',
        'special_notes',
        'total_persons',
        'bring_table_futon',
        'table_futon_quantity',
        'session_id',
        'copy_with_id',
        'user_id'
    ];

    const BOOKING_TYPE_IMC = '1';
    const BOOKING_TYPE_HHV = '0';

    public static $defaultTableFutons = ['0', '1', '2'];
    public static $tableFutons = ['0', '1', '2'];

    public static $bookingTypes = [
        self::BOOKING_TYPE_IMC => 'In massage center',
        self::BOOKING_TYPE_HHV => 'Home / Hotel visit'
    ];

    public function validator(array $data, $isUpdate = false)
    {
        $validatorExtended = [];
        if ($isUpdate === false) {
            $totalBookingInfos = (!empty($data['booking_info']) && is_array($data['booking_info']) ? count($data['booking_info']) : 0);
            $validatorExtended = ['total_persons' => [new Rules\CheckValidBookingCount($totalBookingInfos)]];
        }

        $validator = Validator::make($data, array_merge([
            'booking_type'         => ['required', 'in:' . implode(",", array_keys(self::$bookingTypes))],
            'special_notes'        => ['max:255'],
            'copy_with_id'         => ['max:255'],
            'user_id'              => ['required', 'integer', 'exists:' . User::getTableName() . ',id'],
            'shop_id'              => ['required', 'integer', 'exists:' . Shop::getTableName() . ',id'],
            'session_id'           => ['required', 'integer', 'exists:' . SessionType::getTableName() . ',id'],
            'total_persons'        => ['required', 'integer'],
            'bring_table_futon'    => ['in:' . implode(",", self::$tableFutons)],
            'table_futon_quantity' => ['integer'],
            'booking_date_time'    => ['required'],
            'booking_info'         => ['required', 'array']
        ], $validatorExtended));

        return $validator;
    }

    public function getBookingDateTimeAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        return strtotime($value) * 1000;
    }

    public function getBookingTypeAttribute($value)
    {
        return (isset(self::$bookingTypes[$value])) ? self::$bookingTypes[$value] : $value;
    }

    public function bookingInfo()
    {
        return $this->hasMany('App\BookingInfo', 'booking_id', 'id');
    }

    public function bookingInfoWithBookingMassages()
    {
        return $this->hasMany('App\BookingInfo', 'booking_id', 'id')->with('bookingMassages');
    }

    public function shop()
    {
        return $this->hasOne('App\Shop', 'id', 'shop_id');
    }

    public function user()
    {
        return $this->hasOne('App\User', 'id', 'user_id');
    }
}
