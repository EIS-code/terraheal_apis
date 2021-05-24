<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class TherapistNews extends BaseModel
{
    protected $fillable = [
        'therapist_id',
        'news_id'
    ];

    
    public function validator(array $data)
    {
        return Validator::make($data, [
            'therapist_id'     => ['required', 'integer', 'exists:' . Therapist::getTableName() . ',id'],
            'news_id'          => ['required', 'integer', 'exists:' . News::getTableName() . ',id'],
        ]);
    }
}
