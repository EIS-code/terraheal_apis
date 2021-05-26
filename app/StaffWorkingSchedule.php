<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class StaffWorkingSchedule extends BaseModel
{
    protected $fillable = [
        'day_name',
        'startTime',
        'endTime',
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
            'startTime'  => ['required', 'date:Y-m-d H:i:s'],
            'endTime'    => ['required', 'date:Y-m-d H:i:s'],
            'staff_id'   => ['required', 'exists:staff,id']
        ]);
    }
    
    public function staff()
    {
        return $this->hasOne('App\Staff', 'id', 'staff_id');
    }
}
