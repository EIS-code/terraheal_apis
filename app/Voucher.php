<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use DB;

class Voucher extends BaseModel
{
    protected $fillable = [
        'name',
        'number',
        'image',
        'price',
        'expired_date'
    ];

    const ACTIVE = '0';
    const USED = '1';
    
    public $fileSystem = 'public';
    public $profilePhotoPath = 'voucher\images\\';

    public function validator(array $data)
    {
        return Validator::make($data, [
            'name'       => ['required', 'string', 'max:255'],
            'number'       => ['required', 'string'],
            'price' => ['required'],
            'expired_date' => ['required']
        ]);
    }
    
    public function validateImage($request)
    {
        return Validator::make($request, [
            'image' => 'mimes:jpeg,png,jpg',
        ], [
            'image' => 'Please select proper file. The file must be a file of type: jpeg, png, jpg.'
        ]);
    }
    
    public function getImageAttribute($value)
    {
        $default = asset('storage/voucher/images/voucher.png');

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
    
    public function getVoucherQuery() {

        $voucherModel = new Voucher();
        $voucherShopModel = new VoucherShop();
        $voucherUsersModel = new UserVoucherPrice();
        $userModel = new User();

        $query = $voucherModel
                ->select(DB::RAW($voucherUsersModel::getTableName() . '.*,'  . 'UNIX_TIMESTAMP(' . $voucherUsersModel::getTableName() . '.purchase_date) * 1000 as purchase_date,'  . $voucherShopModel::getTableName() . '.*,' .
                                $voucherModel::getTableName() . '.*,' . 'CONCAT_WS(" ",' . $userModel::getTableName() . '.name,' . $userModel::getTableName() . '.surname) as client_name'))
                ->join($voucherUsersModel::getTableName(), $voucherUsersModel::getTableName() . '.voucher_id', '=', $voucherModel::getTableName() . '.id')
                ->join($voucherShopModel::getTableName(), $voucherShopModel::getTableName() . '.voucher_id', '=', $voucherModel::getTableName() . '.id')
                ->leftJoin($userModel::getTableName(), $userModel::getTableName() . '.id', '=', $voucherUsersModel::getTableName() . '.user_id');
        return $query;
    }

    public function getExpiredDateAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        return strtotime($value) * 1000;
    }
    
    public function users(){
        
        return $this->hasMany('App\UserVoucherPrice', 'voucher_id', 'id');
    }
    
    public function shopsVouchers(){
        
        return $this->hasMany('App\VoucherShop', 'voucher_id', 'id');
    }
}
