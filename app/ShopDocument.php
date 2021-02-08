<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use App\Shop;

class ShopDocument extends Model
{
    protected $fillable = [
        'franchise_contact',
        'id_passport',
        'registration',
        'shop_id'
    ];

    public $fileSystem  = 'public';
    public $storageFolderNameFranchise    = 'shop\\document\\franchise';
    public $storageFolderNameIdPassport   = 'shop\\document\\id_passport';
    public $storageFolderNameRegistration = 'shop\\document\\registrations';

    public function validator(array $data, $id = false, $isUpdate = false)
    {
        return Validator::make($data, [
            'franchise_contact' => ['nullable', 'string', 'max:255'],
            'id_passport' => ['nullable', 'string', 'max:255'],
            'registration' => ['nullable', 'string', 'max:255'],
            'shop_id' => ['required', 'integer', 'exists:' . Shop::getTableName() . ',id']
        ]);
    }

    public function getFranchiseContactAttribute($value)
    {
        $default = '';

        if (empty($value)) {
            return $default;
        }

        $storageFolderNameFranchise = (str_ireplace("\\", "/", $this->storageFolderNameFranchise));
        if (Storage::disk($this->fileSystem)->exists($storageFolderNameFranchise . $value)) {
            return Storage::disk($this->fileSystem)->url($storageFolderNameFranchise . $value);
        }

        return $default;
    }

    public function getIdPassportAttribute($value)
    {
        $default = '';

        if (empty($value)) {
            return $default;
        }

        $storageFolderNameIdPassport = (str_ireplace("\\", "/", $this->storageFolderNameIdPassport));
        if (Storage::disk($this->fileSystem)->exists($storageFolderNameIdPassport . $value)) {
            return Storage::disk($this->fileSystem)->url($storageFolderNameIdPassport . $value);
        }

        return $default;
    }

    public function getRegistrationAttribute($value)
    {
        $default = '';

        if (empty($value)) {
            return $default;
        }

        $storageFolderNameRegistration = (str_ireplace("\\", "/", $this->storageFolderNameRegistration));
        if (Storage::disk($this->fileSystem)->exists($storageFolderNameRegistration . $value)) {
            return Storage::disk($this->fileSystem)->url($storageFolderNameRegistration . $value);
        }

        return $default;
    }
}
