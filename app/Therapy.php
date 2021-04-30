<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class Therapy extends BaseModel
{
    protected $fillable = [
        'name',
        'image',
        'shop_id'
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
    
    public function getImageAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        $imagePath = (str_ireplace("\\", "/", $this->imagePath));
        return Storage::disk($this->fileSystem)->url($imagePath . $value);
    }
    
    public function timing() {
        return $this->hasMany('App\TherapiesTimings', 'therapy_id', 'id');
    }

    public function pricing() {
        return $this->hasMany('App\TherapiesPrices', 'therapy_id', 'id');
    }

}
