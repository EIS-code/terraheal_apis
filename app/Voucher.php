<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class Voucher extends BaseModel
{
    protected $fillable = [
        'name',
        'number',
        'price',
        'expired_date'
    ];

    public function validator(array $data)
    {
        return Validator::make($data, [
            'name'       => ['required', 'string', 'max:255'],
            'number'       => ['required', 'string'],
            'price' => ['required'],
            'expired_date' => ['required']
        ]);
    }
    
    public function users(){
        
        return $this->hasMany('App\UserVoucherPrice', 'voucher_id', 'id');
    }
}
