<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class News extends BaseModel
{
    protected $fillable = [
        'title',
        'sub_title',
        'description',
        'manager_id'
    ];
    
    const TODAY = '0';
    const CURRENT_MONTH = '1';
    const LAST_7_DAYS = '2';
    const LAST_14_DAYS = '3';
    const LAST_30_DAYS = '4';
    const CUSTOM = '5';
    const YESTERDAY = '6';
    const THIS_WEEK = '7';

    public function validator(array $data)
    {
        return Validator::make($data, [
            'title'         => ['required', 'string', 'max:255'],
            'sub_title'     => ['string', 'max:255'],
            'description'   => ['string'],
            'manager_id'    => ['integer',  'exists:' . Manager::getTableName() . ',id'],
        ]);
    }
    
    public function therapistsNews()
    {
        return $this->hasMany('App\TherapistNews', 'news_id', 'id');
    }
}
