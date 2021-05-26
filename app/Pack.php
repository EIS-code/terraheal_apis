<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\Receptionist;
use DB;

class Pack extends BaseModel
{
    protected $fillable = [
        'name',
        'number',
        'image',
        'total_price',
        'pack_price',
        'expired_date',
        'receptionist_id',
        'is_personalized'
    ];
    
    public $fileSystem = 'public';
    public $profilePhotoPath = 'pack\images\\';

    public function validator(array $data)
    {
        return Validator::make($data, [
            'name'       => ['required', 'string', 'max:255'],
            'number'       => ['required', 'string'],
            'total_price' => ['required'],
            'pack_price' => ['required'],
            'expired_date' => ['required'],
            'receptionist_id' => ['integer',  'exists:' . Receptionist::getTableName() . ',id'],
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
        $default = asset('images/pack/pack.png');

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
    
    public function getPackQuery() {

        $packModel = new Pack();
        $packShopModel = new PackShop();
        $packUsersModel = new UserPack();
        $userModel = new User();

        $query = $packUsersModel
                ->select(DB::RAW($packUsersModel::getTableName() . '.*,' . $packShopModel::getTableName() . '.*,' .
                                $packModel::getTableName() . '.*,' . 'UNIX_TIMESTAMP(' . $packModel::getTableName() . '.expired_date) * 1000 as expired_date,' .
                                'CONCAT_WS(" ",' . $userModel::getTableName() . '.name,' . $userModel::getTableName() . '.surname) as client_name'))
                ->join($packModel::getTableName(), $packModel::getTableName() . '.id', '=', $packUsersModel::getTableName() . '.pack_id')
                ->join($packShopModel::getTableName(), $packShopModel::getTableName() . '.pack_id', '=', $packModel::getTableName() . '.id')
                ->leftJoin($userModel::getTableName(), $userModel::getTableName() . '.id', '=', $packUsersModel::getTableName() . '.users_id');
        return $query;
    }

    public function getExpiredDateAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        return strtotime($value) * 1000;
    }
    
    public function services(){
        
        return $this->hasMany('App\PackService', 'pack_id', 'id');
    }
    public function users(){
        
        return $this->hasMany('App\UserPack', 'pack_id', 'id');
    }
    public function shopsPacks(){
        
        return $this->hasMany('App\PackShop', 'pack_id', 'id');
    }
}
