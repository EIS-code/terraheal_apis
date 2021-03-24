<?php

namespace App;

use App\TherapistWorkingSchedule;
use Illuminate\Support\Facades\Validator;

class TherapistWorkingScheduleTime extends BaseModel
{
    protected $fillable = [
        'start_time',
        'end_time',
        'schedule_id'
    ];

    public function validator(array $data, $isUpdate = false)
    {
        return Validator::make($data, [
            'time'        => ['required', 'string'],
            'schedule_id' => ['required', 'exists:' . TherapistWorkingSchedule::getTableName() . ',id']
        ]);
    }

    public function therapistWorkingSchedule()
    {
        return $this->hasOne('App\TherapistWorkingSchedule', 'id', 'schedule_id');
    }
    
    public function getStartTimeAttribute($value)
    {
        return strtotime($value) * 1000;
    }
    
    public function getEndTimeAttribute($value)
    {
        return strtotime($value) * 1000;
    }
}
