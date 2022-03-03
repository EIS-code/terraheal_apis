<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use App\User;
use App\Therapist;
use App\TherapistReviewQuestion;
use DB;

class TherapistReview extends BaseModel
{
    const MAX_RATING = 5;

    public static $ratings = [
        '1', '1.5', '2', '2.5', '3', '3.5', '4', '4.5', '5'
    ];

    protected $fillable = [
        'user_id',
        'therapist_id',
        'question_id',
        'rating',
        'message'
    ];

    const TODAY = '0';
    const YESTERDAY = '1';
    const THIS_WEEK = '2';
    const CURRENT_MONTH = '3';
    const LAST_7_DAYS = '4';
    const LAST_14_DAYS = '5';
    const LAST_30_DAYS = '6';
    const CUSTOM = '7';
    
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

    public function question()
    {
        return $this->hasOne('App\TherapistReviewQuestion', 'id', 'question_id');
    }
    
    public static function ratingSwitchCaseSql()
    {
        $sql = NULL;

        foreach (self::$ratings as $rating) {
            $sql .= " WHEN `rating` = '{$rating}' THEN " . $rating;
        }

        $sql .= ' ELSE 0 END';

        $sql = 'CASE' . $sql;

        return $sql;
    }

    public static function getAverageRatings(int $id)
    {
        $therapistReviewQuestions = new TherapistReviewQuestion();

        $therapistReviewQuestions->setMysqlStrictFalse();

        $data = self::select(DB::raw('ROUND(SUM(' . static::ratingSwitchCaseSql() . ') / COUNT(user_id), 2) as average_rating'), static::getTableName() . '.question_id', $therapistReviewQuestions::getTableName() . '.question', $therapistReviewQuestions::getTableName() . '.brief_description')
                    ->join($therapistReviewQuestions::getTableName(), static::getTableName() . '.question_id', '=', $therapistReviewQuestions::getTableName() . '.id')
                    ->where(static::getTableName() . '.therapist_id', $id)
                    ->groupBy(static::getTableName() . '.question_id')
                    ->orderBy(static::getTableName() . '.question_id')
                    ->get();

        $therapistReviewQuestions->setMysqlStrictTrue();

        return $data;
    }
}
