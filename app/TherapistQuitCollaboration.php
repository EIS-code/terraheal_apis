<?php

namespace App;

use App\Therapist;
use Illuminate\Support\Facades\Validator;

class TherapistQuitCollaboration extends BaseModel
{
    protected $fillable = [
        'reason',
        'therapist_id'
    ];

    public function validator(array $data, $isUpdate = false)
    {
        return Validator::make($data, [
            'reason'       => ['nullable', 'string'],
            'therapist_id' => ['required', 'exists:' . Therapist::getTableName() . ',id']
        ]);
    }
}
