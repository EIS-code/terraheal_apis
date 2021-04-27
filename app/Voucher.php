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
        $default = asset('images/voucher/voucher.png');

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

        $query = $voucherUsersModel
                ->select(DB::RAW($voucherUsersModel::getTableName() . '.*,' . $voucherShopModel::getTableName() . '.*,' .
                                $voucherModel::getTableName() . '.*,' . 'UNIX_TIMESTAMP(' . $voucherModel::getTableName() . '.expired_date) * 1000 as expired_date,' .
                                'CONCAT_WS(" ",' . $userModel::getTableName() . '.name,' . $userModel::getTableName() . '.surname) as client_name'))
                ->join($voucherModel::getTableName(), $voucherModel::getTableName() . '.id', '=', $voucherUsersModel::getTableName() . '.voucher_id')
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
