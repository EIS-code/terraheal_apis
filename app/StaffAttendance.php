<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class StaffAttendance extends Model
{
    protected $fillable = [
        'date',
        'start_time',
        'end_time',
        'break_from',
        'break_to',
        'staff_id'
    ];

    public function validator(array $data, string $type)
    {
        $startTime = ($type == 'in');
        $endTime   = ($type == 'out');
        $breakFrom = ($type == 'breakIn');
        $breakTo   = ($type == 'breakOut');

        return Validator::make($data, [
            'date'       => ['required', 'date'],
            'start_time' => [($startTime ? 'required' : ''), 'string', 'date_format:H:i'],
            'end_time'   => [($endTime ? 'required' : ''), 'string', 'date_format:H:i'],
            'break_from' => [($breakFrom ? 'required' : ''), 'string', 'date_format:H:i'],
            'break_to'   => [($breakTo ? 'required' : ''), 'string', 'date_format:H:i'],
            'staff_id'   => ['required', 'integer']
        ]);
    }
}
