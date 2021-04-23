<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use App\User;
use App\UserHtmlField;

class UserSetting extends BaseModel
{
    protected $fillable = [
        'language_code',
        'notification',
        'unit',
        'currency_code',
        'is_removed',
        'user_id'
    ];

    public function validator(array $data, $isUpdate = false)
    {
        $userId = ['required', 'exists:' . User::getTableName() . ',id'];
        if ($isUpdate === true) {
            $userId = [];
        }

        return Validator::make($data, [
            'language_code' => ['string', 'max:20'],
            'notification'  => ['boolean'],
            'unit'          => ['string', 'in:km,miles'],
            'is_removed'    => ['integer', 'in:0,1'],
            'user_id'       => $userId,
            'currency_code' => ['string', 'max:255']
        ]);
    }

    public function getGlobalResponse(int $id)
    {
        $data          = $this->where('user_id', $id)->where('is_removed', '=', self::$notRemoved)->first();
        $userHtmlField = UserHtmlField::where('is_removed', '=', UserHtmlField::$notRemoved)->first();

        $userSettings = $userHtmlFields = [];

        if (!empty($data)) {
            $userSettings = $data->toArray();
        }

        if (!empty($userHtmlField)) {
            $userHtmlFields = $userHtmlField->toArray();
        }

        $data = array_merge($userSettings, $userHtmlFields);

        return collect($data);
    }
}
