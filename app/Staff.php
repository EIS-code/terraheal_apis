<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class Staff extends Model
{
    protected $fillable = [
        'full_name',
        'password',
        'gender',
        'dob',
        'email',
        'mobile_number',
        'emergency_number',
        'nif',
        'role',
        'country_id',
        'city_id',
        'shop_id',
        'security_number',
        'bank_name',
        'account_number',
        'language_spoken',
        'health_condition',
        'pay_scale',
        'amount',
        'address',
        'login_access'.
        'status'
    ];
    
    protected $hidden = ['created_at', 'updated_at', 'password'];
    
    const GENDER_MALE   = 'm';
    const GENDER_FEMALE = 'f';

    public static $gender = [
        self::GENDER_MALE   => 'Male',
        self::GENDER_FEMALE => 'Female'
    ];
    
    const RECEPTIONIST = '0';
    const CLEANING_LADY = '1';
    
    public static $roles = [
        self::RECEPTIONIST   => 'Receptionist',
        self::CLEANING_LADY => 'Cleaning lady'
    ];

    const MONTHLY = '0';
    const HOURLY = '1';
    
    public static $payScle = [
        self::MONTHLY   => 'Fixed monthly',
        self::HOURLY => 'Fixed hourly'
    ];
    
    const  DEACTIVE = '0';
    const  ACTIVE = '1';
    
    public static $status = [
        self::DEACTIVE   => 'Deactive',
        self::ACTIVE => 'Active'
    ];
    
    const  DISABLE = '0';
    const  ENABLE = '1';
    
    public static $loginAccess = [
        self::DISABLE => 'Disable',
        self::ENABLE   => 'Enable'
    ];
    
    public function validator(array $data, $id = false, $isUpdate = false)
    {
        if ($isUpdate === true && !empty($id)) {
            $emailValidator = ['unique:staff,email,' . $id];
        } else {
            $emailValidator = ['unique:staff'];
        }

        return Validator::make($data, [
                'full_name'             => ['required', 'string', 'max:255'],
                'email'                 => array_merge([(!$isUpdate ? 'required': ''), 'string', 'email', 'max:255'], $emailValidator),
                'password'              => array_merge([(!$isUpdate ? 'required': ''), 'min:6', 'regex:/[a-z]/', 'regex:/[A-Z]/', 'regex:/[0-9]/', 'regex:/[@$!%*#?&]/']),
                'mobile_number'         => ['nullable', 'string', 'max:50'],
                'emergency_number'      => ['nullable', 'string', 'max:50'],
                'gender'                => array_merge(['nullable', 'in:' . implode(",", array_keys(self::$gender))]),
                'role'                  => array_merge(['nullable', 'in:' . implode(",", array_keys(self::$roles))]),
                'pay_scale'             => array_merge(['nullable', 'in:' . implode(",", array_keys(self::$payScle))]),
                'dob'                   => ['nullable', 'string', 'max:255'],
                'nif'                   => ['nullable', 'string', 'max:255'],
                'security_number'       => ['nullable', 'string', 'max:255'],
                'bank_name'             => ['nullable', 'string', 'max:255'],
                'account_number'        => ['nullable', 'string', 'max:255'],
                'language_spoken'       => ['nullable', 'string', 'max:255'],
                'health_condition'      => ['nullable', 'string', 'max:255'],
                'amount'                => ['nullable', 'between:0,99.99'],
                'address'               => ['nullable', 'string', 'max:255'],
                'shop_id'               => ['required', 'integer', 'exists:' . Shop::getTableName() . ',id'],
                'country_id'            => ['nullable', 'integer', 'exists:' . Country::getTableName() . ',id'],
                'city_id'               => ['nullable', 'integer', 'exists:' . City::getTableName() . ',id'],
                'login_access'          => array_merge(['nullable', 'in:' . implode(",", array_keys(self::$loginAccess))]),
                'status'                => array_merge(['nullable', 'in:' . implode(",", array_keys(self::$status))])
                ], [
            'password.regex' => 'Password should contains at least one [a-z, A-Z, 0-9, @, $, !, %, *, #, ?, &].'
        ]);
    }
    
    public function getDobAttribute($value)
    {
        if (is_null($value)) {
            return $value;
        }

        return strtotime($value) * 1000;
    }
    
    public function getDayNameAttribute($value)
    {
        if (is_null($value)) {
            return $value;
        }

        return $this->shopDays[$value];
    }
    
    public function getGenderAttribute($value)
    {
        return (isset(self::$gender[$value])) ? self::$gender[$value] : $value;
    }
    
    public function getRoleAttribute($value)
    {
        return (isset(self::$roles[$value])) ? self::$roles[$value] : $value;
    }
    
    public function getPayScaleAttribute($value)
    {
        return (isset(self::$payScle[$value])) ? self::$payScle[$value] : $value;
    }
    
    public function getLoginAccessAttribute($value)
    {
        return (isset(self::$loginAccess[$value])) ? self::$loginAccess[$value] : $value;
    }
    
    public function getStatusAttribute($value)
    {
        return (isset(self::$status[$value])) ? self::$status[$value] : $value;
    } 
    
    public function schedule()
    {
        return $this->hasMany('App\StaffWorkingSchedule', 'staff_id', 'id');
    }
    
    public function country()
    {
        return $this->hasOne('App\Country', 'id', 'country_id');
    }
    
    public function city()
    {
        return $this->hasOne('App\City', 'id', 'city_id');
    }
    
}
