<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class StaffWorkingSchedule extends BaseModel
{
    protected $fillable = [
        'day_name',
        'start_time',
        'end_time',
        'staff_id'
    ];

    protected $table = 'staff_work_schedules';
    
    protected $hidden = ['created_at', 'updated_at'];


    public $days = [
        '0' => 'Sunday',
        '1' => 'Monday',
        '2' => 'Tuesday',
        '3' => 'Wednesday',
        '4' => 'Thursday',
        '5' => 'Friday',
        '6' => 'Saturday'
    ];
     
    public function validator(array $data, $isUpdate = false)
    {
        return Validator::make($data, [
            'day_name'   => ['required', 'in:' . implode(",", array_keys($this->days))],
            'start_time'  => ['required', 'date:Y-m-d H:i:s'],
            'end_time'    => ['required', 'date:Y-m-d H:i:s'],
            'staff_id'   => ['required', 'exists:staff,id']
        ]);
    }
    
    public function getDayNameAttribute($value)
    {
        if (is_null($value)) {
            return $value;
        }

        return $this->days[$value];
    }
    
    public function getStartTimeAttribute($value)
    {
        if (is_null($value)) {
            return $value;
        }

        return strtotime($value) * 1000;
    }
    
    public function getEndTimeAttribute($value)
    {
        if (is_null($value)) {
            return $value;
        }

        return strtotime($value) * 1000;
    }
    
    public function staff()
    {
        return $this->hasOne('App\Staff', 'id', 'staff_id');
    }
}
