<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class UserDocument extends BaseModel {

    protected $fillable = [
        'passport_front',
        'passport_back',
        'selfie',
        'user_id'
    ];

    public $fileSystem       = 'public';
    public $idPassportPath   = 'user\id_passport\\';
    public $selfiePath       = 'user\selfie\\';
    
    public function validator(array $data) {

        return Validator::make($data, [
            'passport_front'    => ['nullable', 'string', 'max:255'],
            'passport_back'     => ['nullable', 'string', 'max:255'],
            'selfie'            => ['nullable', 'string', 'max:255'],
            'user_id'           => ['required', 'exists:' . User::getTableName() . ',id']
        ]);
    }

    public function checkPassportFront($request, $mimes = 'jpeg,png,jpg')
    {
        return Validator::make($request->all(), [
            'passport_front' => 'mimes:jpeg,png,jpg',
        ], [
            'passport_front' => __('Please select proper file. The file must be a file of type: ' . $mimes . '.')
        ]);
    }
    
    public function checkPassportBack($request, $mimes = 'jpeg,png,jpg')
    {
        return Validator::make($request->all(), [
            'passport_back' => 'mimes:jpeg,png,jpg',
        ], [
            'passport_back' => __('Please select proper file. The file must be a file of type: ' . $mimes . '.')
        ]);
    }
    public function checkSelfie($request, $mimes = 'jpeg,png,jpg')
    {
        return Validator::make($request->all(), [
            'selfie' => 'mimes:jpeg,png,jpg',
        ], [
            'selfie' => __('Please select proper file. The file must be a file of type: ' . $mimes . '.')
        ]);
    }
    
    public function getPassportFrontAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        $idPassportPath = (str_ireplace("\\", "/", $this->idPassportPath));
        if (Storage::disk($this->fileSystem)->exists($idPassportPath . $value)) {
            return Storage::disk($this->fileSystem)->url($idPassportPath . $value);
        }

        return $value;
    }

    public function getPassportBackAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        $idPassportPath = (str_ireplace("\\", "/", $this->idPassportPath));
        if (Storage::disk($this->fileSystem)->exists($idPassportPath . $value)) {
            return Storage::disk($this->fileSystem)->url($idPassportPath . $value);
        }

        return $value;
    }
    
    public function getSelfieAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        $selfiePath = (str_ireplace("\\", "/", $this->selfiePath));
        if (Storage::disk($this->fileSystem)->exists($selfiePath . $value)) {
            return Storage::disk($this->fileSystem)->url($selfiePath . $value);
        }

        return $value;
    }
}
