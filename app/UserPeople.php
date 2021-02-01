<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use App\User;
use App\UserGenderPreference;
use Illuminate\Support\Facades\Storage;

class UserPeople extends BaseModel
{
    protected $table = 'user_peoples';

    protected $fillable = [
        'name',
        'age',
        'gender',
        'photo',
        'is_removed',
        'user_id',
        'user_gender_preference_id'
    ];

    public $fileSystem = 'public';
    public $photoPath  = 'user\people\\';

    public function validator(array $data, $isUpdate = false)
    {
        $userId = ['required', 'exists:' . User::getTableName() . ',id'];
        if ($isUpdate === true) {
            $userId = [];
        }

        return Validator::make($data, [
            'name'       => ['required', 'string', 'max:255'],
            'age'        => ['required', 'integer'],
            'gender'     => ['required', 'in:m,f'],
            'photo'      => ['max:10240'],
            'is_removed' => ['integer', 'in:0,1'],
            'user_id'    => $userId,
            'user_gender_preference_id' => ['required', 'exists:' . UserGenderPreference::getTableName() . ',id']
        ]);
    }

    public function validatePhoto($request)
    {
        return Validator::make($request->all(), [
            'photo' => 'mimes:jpeg,png,jpg',
        ], [
            'photo' => __('Please select proper file. The file must be a file of type: jpeg, png, jpg.')
        ]);
    }

    public function getPhotoAttribute($value)
    {
        $default = asset('images/user-people.png');

        if (empty($value)) {
            return $default;
        }

        $photoPath = (str_ireplace("\\", "/", $this->photoPath));
        if (Storage::disk($this->fileSystem)->exists($photoPath . $value)) {
            return Storage::disk($this->fileSystem)->url($photoPath . $value);
        }

        return $default;
    }
}
