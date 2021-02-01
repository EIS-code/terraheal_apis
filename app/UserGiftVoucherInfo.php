<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class UserGiftVoucherInfo extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'short_description',
        'description',
        'is_removed'
    ];

    public function validator(array $data)
    {
        return Validator::make($data, [
            'title'             => ['required', 'string', 'max:255'],
            'short_description' => ['nullable', 'string'],
            'description'       => ['nullable', 'string'],
            'is_removed'        => ['integer', 'in:0,1']
        ]);
    }
}
