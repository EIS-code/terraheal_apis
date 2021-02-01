<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use App\TherapyQuestionnaire;
use App\User;

class TherapyQuestionnaireAnswer extends BaseModel
{
    protected $fillable = [
        'value',
        'is_removed',
        'questionnaire_id',
        'user_id'
    ];

    public function validator(array $data)
    {
        return Validator::make($data, [
            'value'            => ['required', 'string', 'max:255'],
            'is_removed'       => ['integer', 'in:0,1'],
            'questionnaire_id' => ['required', 'exists:' . TherapyQuestionnaire::getTableName() . ',id'],
            'user_id'          => ['required', 'exists:' . User::getTableName() . ',id']
        ]);
    }
}
