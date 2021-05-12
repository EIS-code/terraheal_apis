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
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public static function getTableName()
    {
        return with(new static)->getTable();
    }

    public function validator(array $data)
    {
        return Validator::make($data, [
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'string', 'email', 'unique:superadmins', 'max:255'],
            'password'  => ['required', 'string', 'min:6', 'regex:/[a-z]/', 'regex:/[A-Z]/', 'regex:/[0-9]/', 'regex:/[@$!%*#?&]/', 'max:255']
        ]);
    }
    
    public function sendPasswordResetNotification($token)
    {
        $classPasswordNotification = new ResetPasswordNotification($token);

        $classPasswordNotification::$createUrlCallback = 'toMailContentsUrl';

        $this->notify($classPasswordNotification);
    }
}
