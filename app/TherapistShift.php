<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class TherapistShift extends BaseModel
{
    protected $fillable = [
        'schedule_id',
        'shift_id',
        'is_working',
        'is_absent',
        'absent_reason'
    ];

    const ABSENT        = '1';
    const NOT_ABSENT    = '0';
    
    const WORKING       = '1';
    const NOT_WORKING   = '0';
    
     public static $isWorking = [
        self::WORKING       => 'Working',
        self::NOT_WORKING   => 'Nope'
    ];

    public static $isAbsent = [
        self::ABSENT        => 'Yes',
        self::NOT_ABSENT    => 'Nope'
    ];
    
    public function validator(array $data)
    {
        return Validator::make($data, [
            'schedule_id'       => ['required', 'integer', 'exists:' . TherapistWorkingSchedule::getTableName() . ',id'],
            'shift_id'          => ['required', 'integer', 'exists:' . ShopShift::getTableName() . ',id'],
            'is_working'        => ['in:' . implode(",", array_keys(self::$isWorking))],
            'is_absent'         => ['in:' . implode(",", array_keys(self::$isAbsent))],
        ]);
    }
    
    public function therapistSchedule()
    {
        return $this->belongsTo('App\TherapistWorkingSchedule', 'schedule_id', 'id');
    }
    
    public function therapistShifts()
    {
        return $this->belongsTo('App\ShopShift', 'shift_id', 'id');
    }
    
    public static function getAvailabilities(int $id, $date)
    {
        
    }
}
