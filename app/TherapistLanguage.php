<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class TherapistLanguage extends Model
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

    public $types = [
        'R' => self::TYPE_1,
        'W' => self::TYPE_2,
        'S' => self::TYPE_3
    ];

    const DEFAULT_VALUE  = '0';
    const THEY_CAN_VALUE = '1';

    public function validator(array $data)
    {
        return Validator::make($data, [
            'type'         => ['required', 'in:1,2,3'],
            'value'        => ['required', 'in:0,1'],
            'language_id'  => ['required', 'integer'],
            'therapist_id' => ['required', 'integer']
        ]);
    }

    public function validators(array $data)
    {
        return Validator::make($data, [
            '*.type'         => ['required', 'in:1,2,3'],
            '*.value'        => ['required', 'in:0,1'],
            '*.language_id'  => ['required', 'integer'],
            '*.therapist_id' => ['required', 'integer']
        ]);
    }
}
