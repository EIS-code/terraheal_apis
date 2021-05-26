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
        'pay_sacle',
        'amount',
        'address',
    ];
    
    protected $hidden = ['created_at', 'updated_at', 'password'];

    public function validator(array $data, $id = false, $isUpdate = false)
    {
        if ($isUpdate === true && !empty($id)) {
            $emailValidator = ['unique:staff,email,' . $id];
        } else {
            $emailValidator = ['unique:staff'];
        }

        return Validator::make($data, [
                'full_name'             => ['required', 'string', 'max:255'],
                'email'                 => array_merge(['required', 'string', 'email', 'max:255'], $emailValidator),
                'password'              => array_merge([(!$isUpdate ? 'required': ''), 'min:6', 'regex:/[a-z]/', 'regex:/[A-Z]/', 'regex:/[0-9]/', 'regex:/[@$!%*#?&]/']),
                'mobile_number'         => ['nullable', 'string', 'max:50'],
                'emergency_number'      => ['nullable', 'string', 'max:50'],
                'gender'                => ['nullable', ['in:0,1']],
                'role'                  => ['nullable', ['in:0,1']],
                'pay_sacle'             => ['nullable', ['in:0,1']],
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
                'city_id'               => ['nullable', 'integer', 'exists:' . City::getTableName() . ',id']
                ], [
            'password.regex' => 'Password should contains at least one [a-z, A-Z, 0-9, @, $, !, %, *, #, ?, &].'
        ]);
    }
    
    public function schedule()
    {
        return $this->hasMany('App\StaffWorkingSchedule', 'staff_id', 'id');
    }
    
}
