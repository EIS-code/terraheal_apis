<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class ReceptionistTimeTables extends BaseModel {

    protected $fillable = [
        'login_date',
        'login_time',
        'logout_time',
        'break_time',
        'receptionist_id'
    ];

    
    public static function validator(array $data) {
        return Validator::make($data, [
                    'receptionist_id' => ['required', 'integer'],
        ]);
    }

}
