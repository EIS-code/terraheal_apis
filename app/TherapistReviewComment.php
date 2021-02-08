<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class TherapistReviewComment extends Model
{
    protected $fillable = [
        'comment',
        'review_id'
    ];

    public function validator(array $data, $id = false, $isUpdate = false)
    {
        return Validator::make($data, [
            'comment'   => ['string', 'max:255'],
            'review_id' => ['required', 'string', 'max:255']
        ]);
    }
}
