<?php

namespace App;

use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class Therapist extends BaseModel implements CanResetPasswordContract
{
    use CanResetPassword, Notifiable;

    protected $fillable = [
        'name',
        'surname',
        'dob',
        'gender',
        'email',
        'tel_number',
        'mobile_number',
        'emergence_contact_number',
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
        'is_mobile_verified',
        'is_document_verified',
        'account_number',
        'nif',
        'social_security_number',
        'health_conditions_allergies',
        'city_id',
        'country_id',
        'personal_description',
    ];

    protected $hidden = ['is_deleted', 'created_at', 'updated_at', 'password'];

    public $fileSystem = 'public';
    public $profilePhotoPath = 'therapists\profile\\';

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
    
    const IS_DELETED = '1';
    const IS_NOT_DELETED = '0';

    public function getFullNameAttribute()
    {
        return $this->name . ' ' . $this->surname;
    }

    public function validator(array $data, $requiredFileds = [], $extraFields = [], $id = false, $isUpdate = false)
    {
        $user = NULL;
        if ($isUpdate === true && !empty($id)) {
            $emailValidator = ['unique:therapists,email,' . $id];
        } else {
            $emailValidator = ['unique:therapists'];
        }

        return Validator::make($data, array_merge([
            'name'                 => array_merge(['string', 'max:255'], !empty($requiredFileds['name']) ? $requiredFileds['name'] : ['nullable']),
            'surname'              => array_merge(['string', 'max:255'], !empty($requiredFileds['surname']) ? $requiredFileds['surname'] : ['nullable']),
            'dob'                  => array_merge(['date:Y-m-d'], !empty($requiredFileds['dob']) ? $requiredFileds['dob'] : ['nullable']),
            'gender'               => array_merge(['in:m,f'], !empty($requiredFileds['gender']) ? $requiredFileds['gender'] : ['nullable']),
            'email'                => array_merge(array_merge(['string', 'email', 'max:255'], $emailValidator), !empty($requiredFileds['email']) ? $requiredFileds['email'] : ['nullable']),
            'tel_number'           => array_merge(['string', 'max:50'], !empty($requiredFileds['tel_number']) ? $requiredFileds['tel_number'] : ['nullable']),
            'hobbies'              => array_merge(['string', 'max:255'], !empty($requiredFileds['hobbies']) ? $requiredFileds['hobbies'] : ['nullable']),
            'short_description'    => array_merge(['string', 'max:255'], !empty($requiredFileds['short_description']) ? $requiredFileds['short_description'] : ['nullable']),
            'shop_id'              => array_merge(['integer'], !empty($requiredFileds['shop_id']) ? $requiredFileds['shop_id'] : ['required']),
            'is_freelancer'        => array_merge(['required', 'in:' . implode(",", array_keys(self::$isFreelancer))], !empty($requiredFileds['is_freelancer']) ? $requiredFileds['is_freelancer'] : ['required']),
            'paid_percentage'      => array_merge(['integer'], !empty($requiredFileds['paid_percentage']) ? $requiredFileds['paid_percentage'] : ['nullable']),
            'avatar'               => array_merge(['max:255'], !empty($requiredFileds['avatar']) ? $requiredFileds['avatar'] : ['nullable']),
            'avatar_original'      => array_merge(['max:255'], !empty($requiredFileds['avatar_original']) ? $requiredFileds['avatar_original'] : ['nullable']),
            'device_token'         => array_merge(['max:255'], !empty($requiredFileds['device_token']) ? $requiredFileds['device_token'] : ['nullable']),
            'device_type'          => array_merge(['max:255'], !empty($requiredFileds['device_type']) ? $requiredFileds['device_type'] : ['nullable']),
            'app_version'          => array_merge(['max:255'], !empty($requiredFileds['app_version']) ? $requiredFileds['app_version'] : ['nullable']),
            'oauth_uid'            => array_merge(['max:255'], !empty($requiredFileds['oauth_uid']) ? $requiredFileds['oauth_uid'] : ['nullable']),
            'oauth_provider'       => array_merge([(!empty($data['oauth_uid']) ? 'required' : ''), (!empty($data['oauth_uid']) ? 'in:1,2,3,4' : '')], !empty($requiredFileds['oauth_provider']) ? $requiredFileds['oauth_provider'] : ['nullable']),
            'password'             => array_merge([(!$isUpdate ? 'required': ''), 'min:6', 'regex:/[a-z]/', 'regex:/[A-Z]/', 'regex:/[0-9]/', 'regex:/[@$!%*#?&]/'], !empty($requiredFileds['password']) ? $requiredFileds['password'] : ['nullable']),
            'is_email_verified'    => array_merge(['in:0,1'], !empty($requiredFileds['is_email_verified']) ? $requiredFileds['is_email_verified'] : ['nullable']),
            'is_mobile_verified'   => array_merge(['in:0,1'], !empty($requiredFileds['is_mobile_verified']) ? $requiredFileds['is_mobile_verified'] : ['nullable']),
            'is_document_verified' => array_merge(['in:0,1'], !empty($requiredFileds['is_document_verified']) ? $requiredFileds['is_document_verified'] : ['nullable']),
            'account_number'            => array_merge(['string', 'max:255'], !empty($requiredFileds['account_number']) ? $requiredFileds['account_number'] : ['nullable']),
            'nif'                       => array_merge(['string', 'max:255'], !empty($requiredFileds['nif']) ? $requiredFileds['nif'] : ['nullable']),
            'social_security_number'    => array_merge(['string', 'max:255'], !empty($requiredFileds['social_security_number']) ? $requiredFileds['social_security_number'] : ['nullable']),
            'health_conditions_allergies' => array_merge(['string'], !empty($requiredFileds['health_conditions_allergies']) ? $requiredFileds['health_conditions_allergies'] : ['nullable']),
            'mobile_number'             => array_merge(['string', 'max:255'], !empty($requiredFileds['mobile_number']) ? $requiredFileds['mobile_number'] : ['nullable']),
            'emergence_contact_number'  => array_merge(['string', 'max:255'], !empty($requiredFileds['emergence_contact_number']) ? $requiredFileds['emergence_contact_number'] : ['nullable']),
        ], $extraFields), [
            'password.regex'    => __('Password should contains at least one [a-z, A-Z, 0-9, @, $, !, %, *, #, ?, &].')
        ]);
    }

    public function validateProfilePhoto($request)
    {
        return Validator::make($request, [
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

    public function bookingInfos()
    {
        return $this->hasMany('App\BookingInfo', 'therapist_id', 'id');
    }

    public function documents()
    {
        return $this->hasMany('App\TherapistDocument', 'therapist_id', 'id');
    }

    public function languageSpokens()
    {
        return $this->hasMany('App\TherapistLanguage', 'therapist_id', 'id');
    }

    public function getProfilePhotoAttribute($value)
    {
        $default = asset('images/therapists/therapist.png');

        // For set default image.
        if (empty($value)) {
            return $default;
        }

        $profilePhotoPath = (str_ireplace("\\", "/", $this->profilePhotoPath));

        if (Storage::disk($this->fileSystem)->exists($profilePhotoPath . $value)) {
            return Storage::disk($this->fileSystem)->url($profilePhotoPath . $value);
        }

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

    public static function isDocumentVerified(int $id, string $isVerified = '0')
    {
        $isVerified = (!in_array($isVerified, ['0', '1'])) ? '0' : $isVerified;

        return self::where('id', $id)->update(['is_document_verified' => $isVerified]);
    }

    public static function getGlobalQuery(int $isFreelancer = self::IS_NOT_FREELANCER, Request $request)
    {
        $model                  = new Self();
        $modelTherapistLanguage = new TherapistLanguage();
        $modelLanguage          = new Language();
        $modelCountry           = new Country();
        $modelCity              = new City();
        $data                   = $request->all();
        $id                     = !empty($data['id']) ? (int)$data['id'] : false;

        if (empty($id)) {
            return collect([]);
        }

        $model->setMysqlStrictFalse();

        $data = $model::selectRaw($model::getTableName() . '.*, CONCAT(' . $modelLanguage::getTableName() . '.name, " ", CASE WHEN ' . $modelTherapistLanguage::getTableName() . '.type = "1" THEN "- Basic" WHEN ' . $modelTherapistLanguage::getTableName() . '.type = "2" THEN "- Good" WHEN ' . $modelTherapistLanguage::getTableName() . '.type = "3" THEN "- Fluent" ELSE NULL END) AS language_spoken, ' . $modelCountry::getTableName() . '.name AS country_name, ' . $modelCity::getTableName() . '.name AS city_name')
                      ->with(['documents', 'selectedMassages'])
                      ->leftJoin($modelTherapistLanguage::getTableName(), $model::getTableName() . '.id', '=', $modelTherapistLanguage::getTableName() . '.therapist_id')
                      ->leftJoin($modelLanguage::getTableName(), $modelTherapistLanguage::getTableName() . '.language_id', '=', $modelLanguage::getTableName() . '.id')
                      ->leftJoin($modelCountry::getTableName(), $model::getTableName() . '.country_id', '=', $modelCountry::getTableName() . '.id')
                      ->leftJoin($modelCity::getTableName(), $model::getTableName() . '.city_id', '=', $modelCity::getTableName() . '.id')
                      ->where($model::getTableName() . '.id', $id)
                      ->groupBy($model::getTableName() . '.id')
                      ->get();

        $model->setMysqlStrictTrue();

        return $data;
    }

    public function getMassageCountAttribute()
    {
        return $this->selectedMassages()->count();
    }

    public function getTherapyCountAttribute()
    {
        return $this->selectedTherapies()->count();
    }
}
