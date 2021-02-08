<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use App\User;
use App\Therapist;
use App\TherapistReviewQuestion;

class TherapistReview extends BaseModel
{
    protected $fillable = [
        'user_id',
        'therapist_id',
        'question_id',
        'rating',
        'message'
    ];

    public function validator(array $data, $id = false, $isUpdate = false)
    {
        return Validator::make($data, [
            'user_id'      => ['required', 'integer', 'exists:' . User::getTableName() . ',id'],
            'therapist_id' => ['required', 'integer', 'exists:' . Therapist::getTableName() . ',id'],
            'question_id'  => ['nullable', 'integer', 'exists:' . TherapistReviewQuestion::getTableName() . ',id'],
            'rating'       => ['required', 'in:1,1.5,2,2.5,3,3.5,4,4.5,5'],
            'message'      => ['nullable']
        ]);
    }
}
