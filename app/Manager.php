<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class Manager extends BaseModel
{
    protected $fillable = [
        'name',
        'surname',
        'address',
        'email',
        'password',
        'image',
        'shop_id',
        'city_id',
        'province_id',
        'country_id',
        'dob',
        'gender',
        'nif',
        'tel_number',
        'tel_number_code',
        'emergency_tel_number',
        'emergency_tel_number_code',
        'id_passport',
        'is_email_verified',
        'is_mobile_verified',
        'news',
        'fcm_token'
    ];

    protected $table = 'manager';
    protected $hidden = ['password', 'remember_token', 'created_at', 'updated_at'];

    const IS_NOT_VERIFIED = '0';
    const IS_VERIFIED = '1';
    
    public $fileSystem = 'public';
    public $profilePhotoPath = 'manager\profile\\';
    
    const MANAGER = 'Manager';
    
    public function validator(array $data, $id = false, $isUpdate = false)
    {
        if ($isUpdate === true && !empty($id)) {
            $emailValidator      = ['string', 'email', 'max:255', 'unique:manager,email,' . $id];
            $numberValidator     = ['unique:manager,tel_number,' . $id];
        } else {
            $emailValidator      = ['required', 'string', 'email', 'max:255', 'unique:superadmins'];
            $numberValidator     = ['nullable', 'unique:manager'];
        }
        
        return Validator::make($data, [
            'name'                    => ['nullable', 'string', 'max:255'],
            'surname'                 => ['nullable', 'string', 'max:255'],
            'email'                   => $emailValidator,
            'address'                 => ['nullable', 'string', 'max:255'],
            'image'                   => ['nullable', 'string'],
            'gender'                  => ['nullable', 'string'],
            'dob'                     => ['nullable', 'string'],
            'nif'                     => ['nullable', 'string'],
            'id_passport'             => ['nullable', 'string'],
            'tel_number'              => array_merge(['nullable', 'string', 'max:50'], $numberValidator),
            'tel_number_code'         => ['nullable', 'string', 'max:20'],
            'emergency_tel_number'    => ['nullable', 'string', 'max:50'],
            'emergency_tel_number_code'    => ['nullable', 'string', 'max:20'],
            'shop_id'                 => ['required', 'integer', 'exists:' . Shop::getTableName() . ',id'],
            'province_id'             => ['nullable', 'integer', 'exists:' . Province::getTableName() . ',id'],
            'country_id'              => ['nullable', 'integer', 'exists:' . Country::getTableName() . ',id'],
            'city_id'                 => ['nullable', 'integer', 'exists:' . City::getTableName() . ',id']
        ]);
    }
    
    public function validatePhoto($request)
    {
        return Validator::make($request, [
            'image' => 'mimes:jpeg,png,jpg',
        ], [
            'image' => 'Please select proper file. The file must be a file of type: jpeg, png, jpg.'
        ]);
    }
    
    public function getImageAttribute($value)
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
    
    public function country()
    {
        return $this->hasOne('App\Country', 'id', 'country_id');
    }

    public function province()
    {
        return $this->hasOne('App\Province', 'id', 'province_id');
    }
    
    public function city()
    {
        return $this->hasOne('App\City', 'id', 'city_id');
    }
}
