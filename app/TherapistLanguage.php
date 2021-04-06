<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use App\Language;
use App\Therapist;

class TherapistLanguage extends BaseModel
{
    protected $fillable = [
        'type',
        'value',
        'language_id',
        'therapist_id'
    ];

    const TYPE_1 = '1';
    const TYPE_2 = '2';
    const TYPE_3 = '3';

    public static $types = [
        self::TYPE_1 => "Basic",
        self::TYPE_2 => "Good",
        self::TYPE_3 => "Fluent"
    ];

    const DEFAULT_VALUE  = '0';
    const THEY_CAN_VALUE = '1';

    public function validator(array $data)
    {
        return Validator::make($data, [
            'type'         => ['required', 'in:1,2,3'],
            'value'        => ['required', 'in:0,1'],
            'language_id'  => ['required', 'integer', 'exists:' . Language::getTableName() . ',id'],
            'therapist_id' => ['required', 'integer', 'exists:' . Therapist::getTableName() . ',id']
        ]);
    }

    public function validators(array $data)
    {
        return Validator::make($data, [
            '*.type'         => ['required', 'in:1,2,3'],
            '*.value'        => ['required', 'in:0,1'],
            '*.language_id'  => ['required', 'integer', 'exists:' . Language::getTableName() . ',id'],
            '*.therapist_id' => ['required', 'integer', 'exists:' . Therapist::getTableName() . ',id']
        ]);
    }
}
