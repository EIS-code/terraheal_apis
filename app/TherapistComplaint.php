<?php

namespace App;

use App\Therapist;
use App\Shop;
use App\Receptionist;
use Illuminate\Support\Facades\Validator;

class TherapistComplaint extends BaseModel
{
    protected $fillable = [
        'complaint',
        'therapist_id',
        'receptionist_id',
        'shop_id'
    ];

    public function validator(array $data, $isUpdate = false)
    {
        return Validator::make($data, [
            'complaint'    => ['required', 'string'],
            'therapist_id' => ['nullable', 'exists:' . Therapist::getTableName() . ',id'],
            'receptionist_id' => ['nullable', 'exists:' . Receptionist::getTableName() . ',id'],
            'shop_id'      => ['required', 'exists:' . Shop::getTableName() . ',id']
        ]);
    }
}
