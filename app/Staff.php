<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class Staff extends Model
{
    protected $fillable = [
        'name',
        'email',
        'tel_number',
        'whatsapp_number',
        'photo',
        'upload_id',
        'insurance',
        'shop_id'
    ];

    public function validator(array $data, $id = false, $isUpdate = false)
    {
        $user = NULL;
        if ($isUpdate === true && !empty($id)) {
            $emailValidator = ['unique:staff,email,' . $id];
        } else {
            $emailValidator = ['unique:staff'];
        }

        return Validator::make($data, [
            'name'            => ['required', 'string', 'max:255'],
            'email'           => array_merge(['required', 'string', 'email', 'max:255'], $emailValidator),
            'tel_number'      => ['required', 'string', 'max:255'],
            'whatsapp_number' => ['required', 'string', 'max:255'],
            'photo'           => ['string', 'max:255'],
            'upload_id'       => ['string', 'max:255'],
            'insurance'       => ['string', 'max:255'],
            'shop_id'         => ['required', 'integer']
        ]);
    }
}
