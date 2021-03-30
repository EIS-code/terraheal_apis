<?php

namespace App;

use App\TherapistWorkingSchedule;
use App\TherapistWorkingScheduleTime;
use Illuminate\Support\Facades\Validator;

class TherapistWorkingScheduleBreak extends BaseModel
{
    protected $fillable = [
        'from',
        'to',
        'break_for',
        'break_reason',
        'schedule_id',
        'schedule_time_id'
    ];

    const OTHER         = '0';
    const FOR_LUNCH     = '1';
    const FOR_DINNER    = '2';

    public static $breakFor = [
        self::OTHER         => 'Other',
        self::FOR_LUNCH     => 'For Lunch',
        self::FOR_DINNER    => 'For Dinner'
    ];

    public function validator(array $data, $isUpdate = false)
    {
        return Validator::make($data, [
            'from'              => ['required', 'date:Y-m-d H:i:s'],
            'to'                => ['required', 'date:Y-m-d H:i:s'],
            'break_for'         => ['in:' . implode(",", array_keys(self::$breakFor))],
            'break_reason'      => ['nullable', 'string'],
            'schedule_id'       => ['required', 'exists:' . TherapistWorkingSchedule::getTableName() . ',id'],
            'schedule_time_id'  => ['required', 'exists:' . TherapistWorkingScheduleTime::getTableName() . ',id']
        ]);
    }

    public function getFromAttribute($value)
    {
        return strtotime($value) * 1000;
    }

    public function getToAttribute($value)
    {
        return strtotime($value) * 1000;
    }
}
