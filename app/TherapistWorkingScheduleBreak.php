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
        'schedule_id',
    ];


    public function validator(array $data, $isUpdate = false)
    {
        return Validator::make($data, [
            'from'              => ['required'],
            'to'                => ['required'],
            'schedule_id'       => ['required', 'exists:' . TherapistWorkingSchedule::getTableName() . ',id'],
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

    public static function takeBreaks($from, $to, $schedule) {
        
        if (!empty($schedule)) {
            $model = new Self();

            $data = [
                'from' => $from,
                'to' => $to,
                'schedule_id' => $schedule->id,
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

        return false;
    }

}
