<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use App\Pack;
use App\Massage;
use App\MassageTiming;
use App\Therapy;
use App\TherapiesTimings;

class PackService extends BaseModel
{
    protected $fillable = [
        "pack_id",
        "massage_id",
        "massage_timing_id",
        "therapy_id",
        "therapy_timing_id",
    ];

    public function validator(array $data)
    {
        return Validator::make($data, [
            'pack_id'           => ['required', 'integer', 'exists:' . Pack::getTableName() . ',id'],
            'massage_id'        => ['integer',  'exists:' . Massage::getTableName() . ',id'],
            'massage_timing_id' => ['integer',  'exists:' . MassageTiming::getTableName() . ',id'],
            'therapy_id'        => ['integer',  'exists:' . Therapy::getTableName() . ',id'],
            'therapy_timing_id' => ['integer',  'exists:' . TherapiesTimings::getTableName() . ',id']
        ]);
    }
}
