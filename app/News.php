<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\SoftDeletes;

class News extends BaseModel
{
    use SoftDeletes;
    
    protected $fillable = [
        'title',
        'sub_title',
        'description',
        'manager_id'
    ];
    
    const TODAY = '0';
    const YESTERDAY = '1';
    const THIS_WEEK = '2';
    const CURRENT_MONTH = '3';
    const LAST_7_DAYS = '4';
    const LAST_14_DAYS = '5';
    const LAST_30_DAYS = '6';
    const CUSTOM = '7';

    public function validator(array $data)
    {
        return Validator::make($data, [
            'title'         => ['required', 'string', 'max:255'],
            'sub_title'     => ['string', 'max:255'],
            'description'   => ['string'],
            'manager_id'    => ['integer',  'exists:' . Manager::getTableName() . ',id'],
        ]);
    }
    
    public function getCreatedAtAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        return strtotime($value) * 1000;
    }
    
    public function therapistsNews()
    {
        return $this->hasMany('App\TherapistNews', 'news_id', 'id')->with('therapists');
    }
}
