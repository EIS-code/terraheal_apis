<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Auth\Passwords\CanResetPassword;

class Superadmin extends BaseModel implements CanResetPasswordContract
{
    use CanResetPassword, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'dob',
        'gender',
        'nif',
        'tel_number',
        'emergency_tel_number',
        'id_passport',
        'country_id',
        'city_id'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'created_at', 'updated_at'
    ];

    public static function getTableName()
    {
        return with(new static)->getTable();
    }

    public function validator(array $data, $id = false, $isUpdate = false)
    {
        if ($isUpdate === true && !empty($id)) {
            $emailValidator      = ['unique:superadmins,email,' . $id];
        } else {
            $emailValidator      = ['unique:superadmins'];
        }
        
        return Validator::make($data, [
            'name'                    => ['required', 'string', 'max:255'],
            'email'                   => array_merge(['required', 'string', 'email', 'max:255'], $emailValidator),
            'gender'                  => ['nullable', 'string'],
            'dob'                     => ['nullable', 'string'],
            'nif'                     => ['nullable', 'string'],
            'id_passport'             => ['nullable', 'string'],
            'tel_number'              => ['nullable', 'string', 'max:50'],
            'emergency_tel_number'    => ['nullable', 'string', 'max:50'],
            'country_id'              => ['nullable', 'integer', 'exists:' . Country::getTableName() . ',id'],
            'city_id'                 => ['nullable', 'integer', 'exists:' . City::getTableName() . ',id']
        ]);
    }
    
    public function sendPasswordResetNotification($token)
    {
        $classPasswordNotification = new ResetPasswordNotification($token);

        $classPasswordNotification::$createUrlCallback = 'toMailContentsUrl';

        $this->notify($classPasswordNotification);
    }
}
