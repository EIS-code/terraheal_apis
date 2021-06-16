<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class PackService extends BaseModel
{
    protected $fillable = [
        "pack_id",
        "service_id",
        "service_timing_id"
    ];

    public function validator(array $data)
    {
        return Validator::make($data, [
            'pack_id'           => ['required', 'integer', 'exists:' . Pack::getTableName() . ',id'],
            'service_id'        => ['integer',  'exists:' . Service::getTableName() . ',id'],
            'service_timing_id' => ['integer',  'exists:' . ServiceTiming::getTableName() . ',id']
        ]);
    }
}
