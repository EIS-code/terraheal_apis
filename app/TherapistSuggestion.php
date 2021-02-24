<?php

namespace App;

use App\Therapist;
use App\Shop;
use Illuminate\Support\Facades\Validator;

class TherapistSuggestion extends BaseModel
{
    protected $fillable = [
        'suggestion',
        'therapist_id',
        'shop_id'
    ];

    public function validator(array $data, $isUpdate = false)
    {
        return Validator::make($data, [
            'suggestion'   => ['required', 'string'],
            'shop_id'      => ['required', 'exists:' . Shop::getTableName() . ',id'],
            'therapist_id' => ['required', 'exists:' . Therapist::getTableName() . ',id']
        ]);
    }
}
