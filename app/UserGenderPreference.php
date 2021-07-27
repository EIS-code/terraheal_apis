<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class UserGenderPreference extends BaseModel
{
    protected $fillable = [
        'name',
        'is_removed'
    ];

    public function validator(array $data)
    {
        return Validator::make($data, [
            'name'       => ['required', 'string', 'max:255'],
            'is_removed' => ['integer', 'in:0,1']
        ]);
    }

    public function selectedGenderPreferences()
    {
        $request = Request();
        $userId  = $request->get('user_id', false);

        $query   = $this->hasOne('App\User', 'user_gender_preference_id', 'id')->where('is_removed', '=', self::$notRemoved)->where('user_id', '=', $userId);

        return $query;
    }
}
