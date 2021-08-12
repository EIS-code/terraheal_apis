<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use App\User;

class EventsAndCorporateRequest extends BaseModel
{
    protected $fillable = [
        'name',
        'mobile_number',
        'email',
        'message',
        'user_id'
    ];

    public function validator(array $data)
    {
        return Validator::make($data, [
            'name'          => ['required', 'string', 'max:255'],
            'mobile_number' => ['nullable', 'string'],
            'email'         => ['nullable', 'string', 'email', 'max:255'],
            'message'       => ['nullable'],
            'user_id'       => ['required',  'exists:' . User::getTableName() . ',id']
        ]);
    }
}
