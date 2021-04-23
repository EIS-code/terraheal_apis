<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use App\Receptionist;

class ReceptionistTimeTables extends BaseModel {

    protected $fillable = [
        'login_date',
        'login_time',
        'logout_time',
        'receptionist_id'
    ];

    
    public static function validator(array $data) {
        return Validator::make($data, [
                    'receptionist_id' => ['required', 'integer', 'exists:' . Receptionist::getTableName() . ',id']
        ]);
    }

    public function breaks() {
        return $this->hasMany('App\ReceptionistBreakTime', 'receptionist_schedule_id', 'id');
    }
    
    public function getLoginDateAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        return strtotime($value) * 1000;
    }
    
    public function getLoginTimeAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        return strtotime($value) * 1000;
    }
    
    public function getLogoutTimeAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        return strtotime($value) * 1000;
    }

}
