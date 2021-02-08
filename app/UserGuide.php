<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class UserGuide extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'short_description',
        'description',
        'icon',
        'is_removed'
    ];

    public $fileSystem = 'public';
    public $iconPath   = 'user\guide\icons\\';

    public function validator(array $data)
    {
        return Validator::make($data, [
            'name'              => ['required', 'string', 'max:255'],
            'short_description' => ['nullable', 'string'],
            'description'       => ['nullable', 'string'],
            'icon'              => ['nullable', 'string', 'max:255'],
            'is_removed'        => ['integer', 'in:0,1']
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
