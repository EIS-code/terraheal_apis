<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class SessionType extends BaseModel
{
    protected $fillable = [
        'type',
        'descriptions',
        'booking_type',
        'icon',
		'is_removed'
    ];

    public static $bookingTypes = ['0', '1'];

    public $fileSystem = 'public';
    public $iconPath   = 'user\massage\session\icons\\';

    public function validator(array $data)
    {
        return Validator::make($data, [
            'type'   	   => ['required', 'string'],
            'descriptions' => ['nullable', 'string'],
            'booking_type' => ['in:' . implode(",", self::$bookingTypes)],
            'icon'         => ['nullable', 'string', 'max:255'],
        ]);
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
