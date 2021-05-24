<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Support\Facades\Storage;

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
        'profile_photo',
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
    
    public $fileSystem = 'public';
    public $profilePhotoPath = 'superAdmin\profile\\';

    public static function getTableName()
    {
        return with(new static)->getTable();
    }

    public function validator(array $data, $id = false, $isUpdate = false)
    {
        if ($isUpdate === true && !empty($id)) {
            $emailValidator      = ['string', 'email', 'max:255', 'unique:superadmins,email,' . $id];
            $nameValidator       = ['string', 'max:255'];
        } else {
            $emailValidator      = ['required', 'string', 'email', 'max:255', 'unique:superadmins'];
            $nameValidator       = ['required', 'string', 'max:255'];
        }
        
        return Validator::make($data, [
            'name'                    => $nameValidator,
            'email'                   => $emailValidator,
            'profile_photo'           => ['nullable', 'string'],
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
    
    public function validatePhoto($request)
    {
        return Validator::make($request, [
            'profile_photo' => 'mimes:jpeg,png,jpg',
        ], [
            'profile_photo' => 'Please select proper file. The file must be a file of type: jpeg, png, jpg.'
        ]);
    }
    
    public function getProfilePhotoAttribute($value)
    {

        // For set default image.
        if (empty($value)) {
            return $value;
        }

        $profilePhotoPath = (str_ireplace("\\", "/", $this->profilePhotoPath));

        if (Storage::disk($this->fileSystem)->exists($profilePhotoPath . $value)) {
            return Storage::disk($this->fileSystem)->url($profilePhotoPath . $value);
        }

        return $value;
    }
    
    public function sendPasswordResetNotification($token)
    {
        $classPasswordNotification = new ResetPasswordNotification($token);

        $classPasswordNotification::$createUrlCallback = 'toMailContentsUrl';

        $this->notify($classPasswordNotification);
    }
}
