<?php

namespace App;

use App\Therapist;
use App\TherapistWorkingScheduleTime;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use DB;

class TherapistWorkingSchedule extends BaseModel
{
    protected $fillable = [
        'date',
        'is_working',
        'is_absent',
        'therapist_id'
    ];

    const WORKING       = '1';
    const NOT_WORKING   = '0';

    public static $isWorking = [
        self::WORKING       => 'Working',
        self::NOT_WORKING   => 'Nope'
    ];

    const ABSENT        = '1';
    const NOT_ABSENT    = '0';

    public static $isAbsent = [
        self::ABSENT        => 'Yes',
        self::NOT_ABSENT    => 'Nope'
    ];

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);

    }

    public function validator(array $data, $isUpdate = false)
    {
        return Validator::make($data, [
            'date'          => ['required', 'date:Y-m-d'],
            'is_working'    => ['in:' . implode(",", array_keys(self::$isWorking))],
            'is_absent'     => ['nullable', 'in:' . implode(",", array_keys(self::$isWorking))],
            'therapist_id'  => ['required', 'exists:' . Therapist::getTableName() . ',id']
        ]);
    }

    public function therapistWorkingScheduleTimes()
    {
        return $this->hasMany('App\TherapistWorkingScheduleTime', 'schedule_id', 'id');
    }

    public function therapistWorkingScheduleTime()
    {
        return $this->hasOne('App\TherapistWorkingScheduleTime', 'schedule_id', 'id');
    }
    public function therapistBreakTime()
    {
        return $this->hasMany('App\TherapistWorkingScheduleBreak', 'schedule_id', 'id');
    }

    public function therapistWorkingScheduleTimeWithBreaks()
    {
        return $this->hasOne('App\TherapistWorkingScheduleTime', 'schedule_id', 'id')->with('therapistWorkingScheduleBreaks');
    }

    public function getDateAttribute($value)
    {
        return strtotime($value) * 1000;
    }
    
    public function therapist() {
        
        return $this->hasOne('App\Therapist', 'id', 'therapist_id');
    }
    
    public function therapistShifts()
    {
        return $this->hasMany('App\TherapistShift', 'schedule_id', 'id');
    }

//    public function getDateAttribute($value)
//    {
//        return strtotime($value) * 1000;
//    }

    /**
     * Get schedule by date.  If not pass then it will take current month.
     *
     * @$month date
     */
    public static function getScheduleByDate(int $id, $date)
    {
        $currentDate   = Carbon::now();
        $date          = empty($date) ? $currentDate : new Carbon($date);

        $data = self::whereDate('date', $date)->where('therapist_id', $id)->get();

        return $data;
    }

    /**
     * Get schedule by month.  If not pass then it will take current month.
     *
     * @$month date
     */
    public static function getScheduleByMonth(int $id, $month)
    {
        $currentMonth   = Carbon::now();
        $month          = empty($month) ? $currentMonth : new Carbon($month);
        $startDate      = $month->format('Y') . '-' . $month->format('m') . '-01';
        $endDate        = $month->format('Y') . '-' . $month->format('m') . '-' . $month->endOfMonth()->format('d');

        $data = self::whereBetween('date', [$startDate, $endDate])->where('therapist_id', $id)->get();

        return $data;
    }

    public static function getAvailabilities(int $id, $date)
    {
        $now   = Carbon::now();
        $date  = Carbon::createFromTimestampMs($date);
        $date  = strtotime($date) > 0 ? $date->format('Y-m-d') : $now->format('Y-m-d');

        $data = TherapistShift::with('therapistSchedule', 'therapistShifts')
                        ->whereHas('therapistSchedule', function($q) use($id, $date) {
                            $q->where('date', $date)
                            ->where('therapist_id', $id);
                        })->get();
        if (!empty($data)) {
            
            $availability['schedule'] = $data[0]['therapistSchedule'];
            foreach ($data as $key => $value) {
                $availability['shifts'][] = $value['therapistShifts'];
            }
        }

        return collect($availability);
    }

    public static function getMissingDays(int $id, $month)
    {
        $currentMonth   = Carbon::now();
        $month          = empty($month) ? $currentMonth : new Carbon($month);
        $startDate      = $month->format('Y') . '-' . $month->format('m') . '-01';
        $endDate        = $month->format('Y') . '-' . $month->format('m') . '-' . $month->endOfMonth()->format('d');

        $date = self::where('therapist_id', $id)->whereBetween('date', [$startDate, $endDate])->where('is_absent', self::ABSENT)->get();

        return $date;
    }
}
