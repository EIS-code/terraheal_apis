<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class TherapistShift extends BaseModel
{
    protected $fillable = [
        'schedule_id',
        'shift_id'
    ];

    public function validator(array $data)
    {
        return Validator::make($data, [
            'schedule_id'       => ['required', 'integer', 'exists:' . TherapistWorkingSchedule::getTableName() . ',id'],
            'shift_id'          => ['required', 'integer', 'exists:' . ShopShift::getTableName() . ',id']
        ]);
    }
}
