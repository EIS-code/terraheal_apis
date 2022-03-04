<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use App\ServiceImage;

class Service extends BaseModel
{

    protected $fillable = [
        'english_name',
        'portugese_name',
        'short_description',
        'priority',
        'expenses',
        'service_type'
    ];
    
    protected $hidden = ['created_at', 'updated_at'];

    const MASSAGE = '0';
    const THERAPY = '1';
    
    public static $serviceType = [
        self::MASSAGE => 'Massage',
        self::THERAPY => 'Therapy'
    ];
    
    public function validator(array $data)
    {
        return Validator::make($data, [
            'english_name'      => ['required', 'string', 'max:255'],
            'portugese_name'    => ['required', 'string', 'max:255'],
            'short_description' => ['required', 'string', 'max:255'],
            'priority'          => ['required', 'string', 'max:255'],
            'expenses'          => ['required', 'string', 'max:255'],
            'service_type'      => ['required', 'in:' . implode(",", array_keys(self::$serviceType))]
        ]);
    }

    public function getServiceTypeAttribute($value)
    {
        return (isset(self::$serviceType[$value])) ? self::$serviceType[$value] : $value;
    }
    
    public function timings()
    {
        return $this->hasMany('App\ServiceTiming', 'service_id', 'id');
    }
    
    public function images()
    {
        return $this->hasMany('App\ServiceImage', 'service_id', 'id');
    }

    public function imageFeatured()
    {
        return $this->hasOne('App\ServiceImage', 'service_id', 'id')->where('is_featured', ServiceImage::IS_FEATURED);
    }

    public function requirement()
    {
        return $this->hasOne('App\ServiceRequirement', 'service_id');
    }
       
    public function pricings()
    {
        return $this->hasMany('App\ServicePricing', 'service_id', 'id');
    }    
}
