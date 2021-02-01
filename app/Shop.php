<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class Shop extends BaseModel
{
    protected $fillable = [
        'name',
        'surname',
        'address',
        'address2',
        'description',
        'longitude',
        'latitude',
        'zoom',
        'owner_name',
        'tel_number',
        'owner_mobile_number',
        'owner_mobile_number_alternative',
        'owner_email',
        'email',
        'time_zone',
        'open_time',
        'close_time',
        'open_day_from',
        'open_day_to',
        'shop_user_name',
        'manager_user_name',
        'shop_password',
        'manager_password',
        'city_id',
        'province_id',
        'country_id',
        'currency_id',
        'pin_code'
    ];

    protected $hidden = ['remember_token', 'created_at', 'updated_at'];

    public $shopDays = [
        '0' => 'Mon',
        '1' => 'Tue',
        '2' => 'Wed',
        '3' => 'Thu',
        '4' => 'Fri',
        '5' => 'Sat',
        '6' => 'Sun'
    ];

    public function validator(array $data, $id = false, $isUpdate = false)
    {
        $user = NULL;
        if ($isUpdate === true && !empty($id)) {
            $emailValidator      = ['unique:shops,email,' . $id];
            $ownerEmailValidator = ['unique:shops,owner_email,' . $id];
        } else {
            $emailValidator      = ['unique:shops'];
            $ownerEmailValidator = ['unique:shops'];
        }

        return Validator::make($data, [
            'name'                => ['nullable', 'string', 'max:255'],
            'surname'             => ['nullable', 'string', 'max:255'],
            'address'             => ['nullable', 'string', 'max:255'],
            'address2'            => ['nullable', 'string', 'max:255'],
            'description'         => ['nullable', 'string'],
            'longitude'           => ['nullable', 'between:0,99.99'],
            'latitude'            => ['nullable', 'between:0,99.99'],
            'zoom'                => ['nullable', 'integer'],
            'owner_name'          => ['nullable', 'string', 'max:255'],
            'tel_number'          => ['nullable', 'string', 'max:50'],
            'owner_mobile_number' => ['nullable', 'string', 'max:50'],
            'owner_mobile_number_alternative' => ['nullable', 'string', 'max:50'],
            'email'               => array_merge(['required', 'string', 'email', 'max:255'], $emailValidator),
            'owner_email'         => array_merge(['nullable', 'string', 'email', 'max:255'], $ownerEmailValidator),
            'time_zone'           => ['nullable', 'string', 'max:255'],
            'financial_situation' => ['nullable', 'string'],
            'open_time'           => ['nullable', 'date_format:H:i'],
            'close_time'          => ['nullable', 'date_format:H:i'],
            'open_day_from'       => ['nullable', 'in:' . implode(",", array_keys($this->shopDays))],
            'open_day_to'         => ['nullable', 'in:' . implode(",", array_keys($this->shopDays))],
            'shop_user_name'      => ['required', 'string', 'max:255'],
            'manager_user_name'   => ['required', 'string', 'max:255'],
            'shop_password'       => ['required', 'string', 'max:255'],
            'manager_password'    => ['required', 'string', 'max:255'],
            'city_id'             => ['nullable'],
            'province_id'         => ['nullable'],
            'country_id'          => ['nullable'],
            'currency_id'         => ['nullable'],
            'pin_code'            => ['nullable', 'string', 'max:255']
        ]);
    }

    public function validatorOwner(array $data, $id = false, $isUpdate = false)
    {
        $user = NULL;
        if ($isUpdate === true && !empty($id)) {
            $ownerEmailValidator = ['unique:shops,owner_email,' . $id];
        } else {
            $ownerEmailValidator = ['unique:shops'];
        }

        return Validator::make($data, [
            'name'                => ['required', 'string', 'max:255'],
            'surname'             => ['nullable', 'string', 'max:255'],
            'owner_name'          => ['nullable', 'string', 'max:255'],
            'owner_mobile_number' => ['required', 'string', 'max:50'],
            'owner_mobile_number_alternative' => ['nullable', 'string', 'max:50'],
            'owner_email'         => array_merge(['required', 'string', 'email', 'max:255'], $ownerEmailValidator),
            'financial_situation' => ['nullable', 'string']
        ]);
    }

    public function validatorLocation(array $data)
    {
        return Validator::make($data, [
            'address'     => ['required', 'string', 'max:255'],
            'address2'    => ['nullable', 'string', 'max:255'],
            'longitude'   => ['required', 'string'],
            'latitude'    => ['required', 'string'],
            'zoom'        => ['nullable', 'integer'],
            'city_id'     => ['required', 'integer'],
            'country_id'  => ['required', 'integer'],
            'pin_code'    => ['nullable', 'string', 'max:255']
        ]);
    }

    public function getOpenDayFromAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        return $this->shopDays[$value];
    }

    public function getOpenDayToAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        return $this->shopDays[$value];
    }

    public function massages()
    {
        return $this->hasMany('App\Massage', 'shop_id', 'id');
    }

    public function bookings()
    {
        return $this->hasMany('App\Booking', 'shop_id', 'id');
    }

    public function bookingsImc()
    {
        return $this->hasMany('App\Booking', 'shop_id', 'id')->where('booking_type', '1');
    }

    public function bookingsHv()
    {
        return $this->hasMany('App\Booking', 'shop_id', 'id')->where('booking_type', '0');
    }
}
