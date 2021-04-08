<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class Receptionist extends BaseModel
{
    protected $fillable = [
        'name',
        'email',
        'tel_number',
        'photo',
        'dob',
        'gender',
        'emergency_tel_number',
        'nif',
        'security_number',
        'address',
        'country_id',
        'city_id',
        'shop_id'
    ];
    
    protected $hidden = ['created_at', 'updated_at'];
    
    public $fileSystem = 'public';
    public $profilePhotoPath = 'receptionist\profile\\';

    public function validator(array $data, $id = false, $isUpdate = false)
    {
        $user = NULL;
        if ($isUpdate === true && !empty($id)) {
            $emailValidator = ['unique:receptionists,email,' . $id];
        } else {
            $emailValidator = ['unique:receptionists'];
        }

        return Validator::make($data, [
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => array_merge(['required', 'string', 'email', 'max:255'], $emailValidator),
            'tel_number'            => ['max:50'],
            'emergency_tel_number'  => ['max:50'],
            'gender'                => ['required', 'string'],
            'shop_id'               => ['required', 'integer'],
            'city_id'               => ['required', 'integer'],
            'country_id'            => ['required', 'integer'],
        ]);
    }
    
    public function validatePhoto($request)
    {
        return Validator::make($request, [
            'photo' => 'mimes:jpeg,png,jpg',
        ], [
            'photo' => 'Please select proper file. The file must be a file of type: jpeg, png, jpg.'
        ]);
    }
    
    public function documents()
    {
        return $this->hasMany('App\ReceptionistDocuments', 'receptionist_id', 'id');
    }
    
    public function country()
    {
        return $this->hasOne('App\Country', 'id', 'country_id');
    }
    
    public function city()
    {
        return $this->hasOne('App\City', 'id', 'city_id');
    }
    
    public function shop()
    {
        return $this->hasOne('App\Shop', 'id', 'shop_id');
    }

    public function getProfilePhotoAttribute($value)
    {
        $default = asset('images/receptionists/receptionist.png');

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
}
