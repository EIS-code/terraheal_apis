<?php

namespace App;

use App\User;
use Illuminate\Support\Facades\Validator;

class TherapistUserRating extends BaseModel
{
    protected $fillable = [
        'rating',
        'type',
        'user_id',
        'model_id',
        'model',
        'edit_by'
    ];
    
    
    public static $types = [
        0 => 'Punctuality And Presence For Reservations',
        1 => 'Behavior',
        2 => 'Sexual Issues',
        3 => 'Hygiene',
        4 => 'Left Bad / Good Review',
        5 => 'Payment Issues',
    ];
    
    public function validator(array $data, $id = false, $isUpdate = false)
    {
        return Validator::make($data, [
            'rating'        => ['in:0,1,2,3,4,5'],
            'type'          => ['in:0,1,2,3,4,5'],
            'user_id'       => ['required', 'integer', 'exists:' . User::getTableName() . ',id'],
            'model_id'      => ['required', 'integer'],
            'model'         => ['required', 'string', 'max:255']
        ]);
    }

    public function validators(array $data, $id = false, $isUpdate = false)
    {
        return Validator::make($data, [
            'rating.*'      => ['in:0,1,2,3,4,5'],
            'type.*'        => ['in:0,1,2,3,4,5'],
            'user_id'       => ['required', 'integer', 'exists:' . User::getTableName() . ',id'],
            'model_id'      => ['required', 'integer'],
            'model'         => ['required', 'string', 'max:255']
        ]);
    }
    
    public function therapist()
    {
        return $this->hasOne('App\Therapist', 'id', 'therapist_id');
    }
}
