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
            'start_time'    => ['required', 'date:Y-m-d H:i:s'],
            'end_time'      => ['required', 'date:Y-m-d H:i:s'],
            'schedule_id'   => ['required', 'exists:' . TherapistWorkingSchedule::getTableName() . ',id']
        ]);
    }

    public function therapistWorkingSchedule()
    {
        return $this->hasOne('App\TherapistWorkingSchedule', 'id', 'schedule_id');
    }
}
