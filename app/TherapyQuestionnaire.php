<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class TherapyQuestionnaire extends BaseModel
{
    protected $fillable = [
        'title',
        'placeholder',
        'type',
        'min',
        'max',
        'is_removed'
    ];

    public function validator(array $data)
    {
        return Validator::make($data, [
            'title'       => ['required', 'string', 'max:255'],
            'placeholder' => ['string', 'max:255'],
            'type'        => ['string', 'max:255'],
            'min'         => ['integer'],
            'max'         => ['integer'],
            'is_removed'  => ['integer', 'in:0,1']
        ]);
    }

    public function questionnaireAnswer()
    {
        $request = Request();
        $userId  = $request->get('user_id', false);

        $query   = $this->hasOne('App\TherapyQuestionnaireAnswer', 'questionnaire_id', 'id')->where('is_removed', '=', self::$notRemoved)->where('user_id', '=', $userId);

        return $query;
    }
}
