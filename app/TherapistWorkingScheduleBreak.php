<?php

namespace App;

use App\TherapistWorkingSchedule;
use App\TherapistWorkingScheduleTime;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

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
            'from'              => ['required'],
            'to'                => ['required'],
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

    public static function takeBreaks(int $id, $date, int $minutes, int $breakFor = self::OTHER, string $breakReason = NULL)
    {
        $schedule = TherapistWorkingSchedule::whereDate('date', $date)->where('therapist_id', $id)->first();

        // Get timing
        if (!empty($schedule)) {
            $scheduleTime = $schedule->therapistWorkingScheduleTime;

            $now    = new Carbon(date('Y-m-d'));
            $toTime = $now->addMinute($minutes);

            $model  = new Self();

            if (!empty($scheduleTime)) {
                $data = [
                    'from' => '00:00:00',
                    'to' => $toTime->format('H:i:s'),
                    'break_for' => (string)$breakFor,
                    'break_reason' => $breakReason,
                    'schedule_id' => $schedule->id,
                    'schedule_time_id' => $scheduleTime->id
                ];

                $checks = $model->validator($data);
                if ($checks->fails()) {
                    return ['isError' => true, 'msg' => $checks->errors()->first()];
                }

                $create = $model::create($data);

                if ($create) {
                    return $create;
                }
            }
        }

        return false;
    }
}
