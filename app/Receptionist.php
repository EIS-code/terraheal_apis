<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class Receptionist extends Model
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
            $emailValidator = ['unique:receptionists,email,' . $id];
        } else {
            $emailValidator = ['unique:receptionists'];
        }

        return Validator::make($data, [
            'name'              => ['required', 'string', 'max:255'],
            'email'             => array_merge(['required', 'string', 'email', 'max:255'], $emailValidator),
            'tel_number'        => ['required', 'max:50'],
            'whatsapp_number'   => ['required', 'max:50'],
            'photo'             => ['required', 'max:255'],
            'upload_id'         => ['required', 'max:255'],
            'insurance'         => ['required', 'max:255'],
            'shop_id'           => ['required', 'integer']
        ]);
    }
}
