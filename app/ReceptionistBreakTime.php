<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use App\ReceptionistTimeTables;

class ReceptionistBreakTime extends BaseModel {

    protected $fillable = [
        'start_time',
        'end_time',
        'receptionist_schedule_id'
    ];

    
    public static function validator(array $data) {
        return Validator::make($data, [
                'receptionist_schedule_id' => ['required', 'integer', 'exists:' . ReceptionistTimeTables::getTableName() . ',id'],
        ]);
    }

}
