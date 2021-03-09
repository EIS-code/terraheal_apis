<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class Review extends Model
{
    protected $fillable = [
        'rating',
        'user_id',
    ];

    const RATING_VERY_BAD  = 1;
    const RATING_BAD       = 2;
    const RATING_MEDIUM    = 3;
    const RATING_GOOD_ONE  = 4;
    const RATING_TOO_HAPPY = 5;
    public static $rating = [
        self::RATING_VERY_BAD  => 'Very Bad',
        self::RATING_BAD       => 'Bad',
        self::RATING_MEDIUM    => 'Medium',
        self::RATING_GOOD_ONE  => 'Good One',
        self::RATING_TOO_HAPPY => 'Too Happy'
    ];

    public function validator(array $data)
    {
        return Validator::make($data, [
            'rating'  => ['required', 'integer', 'in:1,2,3,4,5'],
            'user_id' => ['required', 'integer']
        ]);
    }
    
    public function user()
    {
        return $this->hasOne('App\User', 'id', 'user_id');
    }
}
