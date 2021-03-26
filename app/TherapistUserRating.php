<?php

namespace App;

use App\User;
use App\Therapist;
use Illuminate\Support\Facades\Validator;

class TherapistUserRating extends BaseModel
{
    protected $fillable = [
        'rating',
        'type',
        'user_id',
        'therapist_id'
    ];

    public function validator(array $data, $id = false, $isUpdate = false)
    {
        return Validator::make($data, [
            'rating'        => ['in:0,1,2,3,4,5'],
            'type'          => ['in:0,1,2,3,4,5'],
            'user_id'       => ['required', 'integer', 'exists:' . User::getTableName() . ',id'],
            'therapist_id'  => ['required', 'integer', 'exists:' . Therapist::getTableName() . ',id']
        ]);
    }

    public function validators(array $data, $id = false, $isUpdate = false)
    {
        return Validator::make($data, [
            'rating.*'      => ['in:0,1,2,3,4,5'],
            'type.*'        => ['in:0,1,2,3,4,5'],
            'user_id'       => ['required', 'integer', 'exists:' . User::getTableName() . ',id'],
            'therapist_id'  => ['required', 'integer', 'exists:' . Therapist::getTableName() . ',id']
        ]);
    }
    
    public function therapist()
    {
        return $this->hasOne('App\Therapist', 'id', 'therapist_id');
    }
}
