<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class UserMassagePreferences extends BaseModel
{
    protected $fillable = [
        'massage_preference_id',
        'mp_option_id',
        'user_id',
        'answer'
    ];

    public function validator(array $data)
    {
        return Validator::make($data, [
            'massage_preference_id' => ['required', 'integer'],
            'mp_option_id' => ['integer'],
            'user_id' => ['required', 'integer'],
            'answer' => ['string'],
        ]);
    }
    
    public function massagePreference(){
        
        return $this->hasOne('App\MassagePreference', 'id', 'massage_preference_id');
    }
    public function massagePreferenceOption(){
        
        return $this->hasOne('App\MassagePreferenceOption', 'id', 'mp_option_id');
    }
}
