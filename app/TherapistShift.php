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

    public function validator(array $data)
    {
        return Validator::make($data, [
            'schedule_id'       => ['required', 'integer', 'exists:' . TherapistWorkingSchedule::getTableName() . ',id'],
            'shift_id'          => ['required', 'integer', 'exists:' . ShopShift::getTableName() . ',id']
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
