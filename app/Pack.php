<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use App\Receptionist;

class Pack extends BaseModel
{
    protected $fillable = [
        'name',
        'number',
        'total_price',
        'pack_price',
        'expired_date',
        'receptionist_id',
        'is_personalized'
    ];

    public function validator(array $data)
    {
        return Validator::make($data, [
            'name'       => ['required', 'string', 'max:255'],
            'number'       => ['required', 'string'],
            'total_price' => ['required'],
            'pack_price' => ['required'],
            'expired_date' => ['required'],
            'receptionist_id' => ['integer',  'exists:' . Receptionist::getTableName() . ',id'],
        ]);
    }
    
    public function services(){
        
        return $this->hasMany('App\PackService', 'pack_id', 'id');
    }
}
