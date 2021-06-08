<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class ServiceRequirement extends BaseModel
{
    protected $fillable = [
        'service_id',
        'massage_through',
        'special_tools',
        'platform',
        'oil_usage'
    ];
    
    protected $hidden = ['created_at', 'updated_at'];
    
    const MASSAGE_TABLE = '0';
    const TATAMI_FUTON = '1';
    const BOTH = '2';

    public static $platforms = [
        self::MASSAGE_TABLE => 'Massage table',
        self::TATAMI_FUTON => 'Tatami/Futon',
        self::BOTH => 'Both'
    ];
    
    const USE_OIL = '0';
    const A_BIT = '1';
    const DRY = '2';
    
    public static $oil_usage = [
        self::USE_OIL => 'Use oil',
        self::A_BIT => 'Use just a bit of oil',
        self::DRY => 'Dry massage'
    ];
    
    public $fileSystem = 'public';
    public $directory  = 'service\images\\';

    public static function validator(array $data)
    {
        return Validator::make($data, [
            'service_id'        => ['required', 'integer', 'exists:' . Service::getTableName() . ',id'],
            'massage_through'   => ['required', 'string', 'max:255'],
            'special_tools'     => ['required', 'string', 'max:255'],
            'platform'          => ['required', 'in:' . implode(",", array_keys(self::$platforms))],
            'oil_usage'         => ['required', 'in:' . implode(",", array_keys(self::$oil_usage))],
        ]);
    }
    
    public function getPlatformAttribute($value)
    {
        return (isset(self::$platforms[$value])) ? self::$platforms[$value] : $value;
    }
    
    public function getOilUsageAttribute($value)
    {
        return (isset(self::$oil_usage[$value])) ? self::$oil_usage[$value] : $value;
    }
    
}
