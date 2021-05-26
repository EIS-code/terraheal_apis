<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use App\Massage;
use App\Therapist;

class TherapistSelectedMassage extends BaseModel
{
    protected $fillable = [
        'massage_id',
        'therapist_id'
    ];

    public function validator(array $data)
    {
        return Validator::make($data, [
            'massage_id'   => ['required', 'integer', 'exists:' . Massage::getTableName() . ',id'],
            'therapist_id' => ['required', 'integer', 'exists:' . Therapist::getTableName() . ',id']
        ]);
    }

    public function validators(array $data)
    {
        return Validator::make($data, [
            '*.massage_id'   => ['required', 'integer', 'exists:' . Massage::getTableName() . ',id'],
            '*.therapist_id' => ['required', 'integer', 'exists:' . Therapist::getTableName() . ',id']
        ]);
    }

    public function massage()
    {
        return $this->hasOne('App\Massage', 'id', 'massage_id');
    }
    
}
