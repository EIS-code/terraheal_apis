<?php

namespace App;

use App\Therapist;
use Illuminate\Support\Facades\Validator;

class TherapistExchange extends BaseModel
{
    protected $fillable = [
        'date',
        'is_approved',
        'therapist_id'
    ];

    const IS_APPROVED = '1';
    const IS_NOT_APPROVED = '0';

    public static $isApproved = [
        self::IS_APPROVED       => 'Nope',
        self::IS_NOT_APPROVED   => 'Yes'
    ];

    public function validator(array $data, $isUpdate = false)
    {
        return Validator::make($data, [
            'date'          => ['required', 'date:Y-m-d'],
            'is_approved'   => ['in:' . implode(",", array_keys(self::$isApproved))],
            'therapist_id'  => ['required', 'exists:' . Therapist::getTableName() . ',id']
        ]);
    }
}
