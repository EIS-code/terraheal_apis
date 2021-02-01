<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use App\Province;
use App\City;
use App\User;
use Illuminate\Http\Request;

class UserAddress extends BaseModel
{
    protected $fillable = [
        'address_line_1',
        'address_line_2',
        'land_mark',
        'pin_code',
        'name',
        'longitude',
        'latitude',
        'is_removed',
        // 'province_id',
        // 'city_id',
        'province',
        'city',
        'user_id'
    ];

    public $httpRequest;

    public function checkHasColumns()
    {
        $this->httpRequest = Request();

        $requestColumns = $this->httpRequest->all();
        if (!empty($requestColumns)) {
            $isExists = $this->getConnection()->getSchemaBuilder()->hasColumns($this->getTable(), array_keys($requestColumns));
            
            if (!$isExists) {
                return false;
            }

            return true;
        }

        return false;
    }

    public function validator(array $data, $isUpdate = false)
    {
        $userId = ['required', 'exists:' . User::getTableName() . ',id'];
        if ($isUpdate === true) {
            $userId = [];
        }

        return Validator::make($data, [
            'address_line_1'    => ['required', 'string', 'max:255'],
            'address_line_2'    => ['max:255'],
            'land_mark'         => ['required', 'string', 'max:255'],
            'pin_code'          => ['required', 'string', 'max:255'],
            'name'              => ['required', 'string', 'max:255'],
            'longitude'         => ['required', 'string', 'max:255'],
            'latitude'          => ['required', 'string', 'max:255'],
            'is_removed'        => ['integer', 'in:0,1'],
            'province'          => ['required', 'string', 'max:255'],
            'city'              => ['required', 'string', 'max:255'],
            // 'province_id'       => ['required', 'exists:' . Province::getTableName() . ',id'],
            // 'city_id'           => ['required', 'exists:' . City::getTableName() . ',id'],
            'user_id'           => $userId
        ]);
    }
}
