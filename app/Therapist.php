<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Auth\Passwords\CanResetPassword;
<<<<<<< HEAD

class Therapist extends BaseModel implements CanResetPasswordContract
{
    use CanResetPassword;
=======
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;

class Therapist extends BaseModel implements CanResetPasswordContract
{
    use CanResetPassword, Notifiable;
>>>>>>> a1af10094a4c25489d0fb294eb5811e66c43dd85

    protected $fillable = [
        'name',
        'dob',
        'gender',
        'email',
        'tel_number',
        'hobbies',
        'short_description',
        'is_freelancer',
        'paid_percentage',
        'is_deleted',
        'shop_id',
        'avatar',
        'avatar_original',
        'device_token',
        'device_type',
        'app_version',
        'oauth_uid',
        'oauth_provider',
        'profile_photo',
        'password',
        'is_email_verified',
        'is_mobile_verified'
    ];

    protected $hidden = ['is_deleted', 'created_at', 'updated_at', 'password'];

    public $fileSystem = 'public';
    public $profilePhotoPath = 'therapist\profile\\';

    const GENDER_MALE   = 'm';
    const GENDER_FEMALE = 'f';

    public static $bookingTypes = [
        self::GENDER_MALE   => 'Male',
        self::GENDER_FEMALE => 'Female'
    ];

    const IS_FREELANCER     = '1';
    const IS_NOT_FREELANCER = '0';

    public static $isFreelancer = [
        self::IS_FREELANCER     => 'Yes',
        self::IS_NOT_FREELANCER => 'No'
    ];

    public function validator(array $data, $id = false, $isUpdate = false)
    {
        $user = NULL;
        if ($isUpdate === true && !empty($id)) {
            $emailValidator = ['unique:therapists,email,' . $id];
        } else {
            $emailValidator = ['unique:therapists'];
        }

        return Validator::make($data, [
            'name'                 => ['required', 'string', 'max:255'],
            'dob'                  => ['date'],
            'gender'               => ['in:m,f'],
            'email'                => array_merge(['required', 'string', 'email', 'max:255'], $emailValidator),
            'tel_number'           => ['string', 'max:50'],
            'hobbies'              => ['string', 'max:255'],
            'short_description'    => ['string', 'max:255'],
            'shop_id'              => ['integer'],
            'is_freelancer'        => ['required', 'in:' . implode(",", array_keys($this->isFreelancer))],
            'paid_percentage'      => ['integer'],
            'avatar'               => ['max:255'],
            'avatar_original'      => ['max:255'],
            'device_token'         => ['max:255'],
            'device_type'          => ['max:255'],
            'app_version'          => ['max:255'],
            'oauth_uid'            => ['max:255'],
            'oauth_provider'       => [(!empty($data['oauth_uid']) ? 'required' : ''), (!empty($data['oauth_uid']) ? 'in:1,2,3,4' : '')],
            'password'             => [(!$isUpdate ? 'required': ''), 'min:6', 'regex:/[a-z]/', 'regex:/[A-Z]/', 'regex:/[0-9]/', 'regex:/[@$!%*#?&]/'],
            'is_email_verified'    => ['in:0,1'],
            'is_mobile_verified'   => ['in:0,1'],
            'is_document_verified' => ['in:0,1'],
        ], [
            'password.regex'    => 'Password should contains at least one [a-z, A-Z, 0-9, @, $, !, %, *, #, ?, &].'
        ]);
    }

    public function validateProfilePhoto($request)
    {
        return Validator::make($request->all(), [
            'profile_photo' => 'mimes:jpeg,png,jpg',
        ], [
            'profile_photo' => 'Please select proper file. The file must be a file of type: jpeg, png, jpg.'
        ]);
    }

    public function selectedMassages()
    {
        return $this->hasMany('App\TherapistSelectedMassage', 'therapist_id', 'id');
    }

    public function selectedTherapies()
    {
        return $this->hasMany('App\TherapistSelectedTherapy', 'therapist_id', 'id');
    }

    public function getProfilePhotoAttribute($value)
    {
<<<<<<< HEAD
        $default = asset('images/therapist.png');
=======
        $default = 'images/therapist.png';
>>>>>>> a1af10094a4c25489d0fb294eb5811e66c43dd85

        if (empty($value)) {
            return $default;
        }

<<<<<<< HEAD
        $profilePhotoPath = (str_ireplace("\\", "/", $this->profilePhotoPath));
        if (Storage::disk($this->fileSystem)->exists($profilePhotoPath . $value)) {
            return Storage::disk($this->fileSystem)->url($profilePhotoPath . $value);
        }

        return $default;
    }
=======
        /*$profilePhotoPath = (str_ireplace("\\", "/", $this->profilePhotoPath));
        if (Storage::disk($this->fileSystem)->exists($profilePhotoPath . $value)) {
            return Storage::disk($this->fileSystem)->url($profilePhotoPath . $value);
        }*/

        return $default;
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $classPasswordNotification = new ResetPasswordNotification($token);

        $classPasswordNotification::$createUrlCallback = 'toMailContentsUrl';

        $this->notify($classPasswordNotification);
    }
>>>>>>> a1af10094a4c25489d0fb294eb5811e66c43dd85
}
