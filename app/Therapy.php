<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class Therapy extends BaseModel
{
    protected $fillable = [
        'name',
        'image'
    ];

    public $fileSystem = 'public';
    public $imagePath  = 'therapy\images\\';
    public $iconPath   = 'therapy\images\icons\\';

    public function validator(array $data)
    {
        return Validator::make($data, [
            'name'  => ['required', 'string', 'max:255'],
            'image' => ['string', 'max:255']
        ]);
    }
    
    public function timing() {
        return $this->hasMany('App\TherapiesTimings', 'therapy_id', 'id');
    }

    public function pricing() {
        return $this->hasMany('App\TherapiesPrices', 'therapy_id', 'id');
    }

}
