<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use App\MassagePreference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MassagePreferenceOption extends BaseModel
{
    protected $fillable = [
        'name',
        'icon',
        'is_removed',
        'massage_preference_id',
    ];

    public static $massagePressures = [
        1 => [
            1, 2, 3, 4
        ]
    ];

    public static $massageGenders = [
        2 => [
            5, 6, 7, 8, 9
        ]
    ];

    public static $massageFocucAreas = [
        8 => [
            15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34
        ]
    ];

    public $fileSystem = 'public';
    public $iconPath   = 'therapist\preference\icons\\';

    public function validator(array $data)
    {
        return Validator::make($data, [
            'name'                  => ['required', 'integer'],
            'icon'                  => ['nullable', 'string', 'max:255'],
            'is_removed'            => ['integer', 'in:0,1'],
            'massage_preference_id' => ['required', 'exists:' . MassagePreference::getTableName() . ',id']
        ]);
    }

    public function selectedPreferences()
    {
        $request = Request();
        $userId  = $request->get('user_id', false);

        $query   = $this->hasOne('App\SelectedMassagePreference', 'mp_option_id', 'id')->where('is_removed', '=', self::$notRemoved)->where('user_id', '=', $userId);

        return $query;
    }

    public function validateIcon($request)
    {
        return Validator::make($request->all(), [
            'icon' => 'mimes:png',
        ], [
            'icon' => 'Please select proper file. The file must be a file of type: png.'
        ]);
    }

    public function getIconAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        $iconPath = (str_ireplace("\\", "/", $this->iconPath));
        return Storage::disk($this->fileSystem)->url($iconPath . $value);
    }
}
