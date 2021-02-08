<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class MassagePreference extends BaseModel
{
    protected $fillable = [
        'name',
        'is_removed',
    ];

    public function validator(array $data)
    {
        return Validator::make($data, [
            'name'       => ['required', 'integer'],
            'is_removed' => ['integer', 'in:0,1'],
        ]);
    }

    public function preferenceOptions()
    {
        return $this->hasMany('App\MassagePreferenceOption', 'massage_preference_id', 'id')->where('is_removed', '=', self::$notRemoved);
    }
}
