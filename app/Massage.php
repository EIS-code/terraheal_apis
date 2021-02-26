<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use Laravel\Scout\Searchable;
use Illuminate\Support\Facades\Storage;

class Massage extends BaseModel
{
    use Searchable;

    protected $fillable = [
        'name',
        'image',
        'icon'
    ];

    public $fileSystem = 'public';
    public $imagePath  = 'massage\images\\';
    public $iconPath   = 'massage\images\icons\\';

    public function validator(array $data)
    {
        return Validator::make($data, [
            'name'  => ['required', 'string', 'max:255'],
            'image' => ['string', 'max:255'],
            'icon'  => ['nullable', 'string', 'max:255']
        ]);
    }

    public function validateImage($request)
    {
        return Validator::make($request->all(), [
            'photo' => 'mimes:jpeg,png,jpg',
        ], [
            'photo' => __('Please select proper file. The file must be a file of type: jpeg, png, jpg.')
        ]);
    }

    public function validateIcon($request)
    {
        return Validator::make($request->all(), [
            'photo' => 'mimes:ico,png',
        ], [
            'photo' => __('Please select proper file. The file must be a file of type: ico, png.')
        ]);
    }

    public function getImageAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        $imagePath = (str_ireplace("\\", "/", $this->imagePath));
        return Storage::disk($this->fileSystem)->url($imagePath . $value);
    }

    public function getIconAttribute($value)
    {
//        $default = asset('images/services.png');
//
//        if (empty($value)) {
//            return $default;
//        }
//
//        $iconPath = (str_ireplace("\\", "/", $this->iconPath));
//        if (Storage::disk($this->fileSystem)->exists($iconPath . $value)) {
//            return Storage::disk($this->fileSystem)->url($iconPath . $value);
//        }
//
        return 'services.png';
    }

    public function timing()
    {
        return $this->hasMany('App\MassageTiming', 'massage_id', 'id');
    }

    /**
     * Get the index name for the model.
     *
     * @return string
     */
    public function searchableAs()
    {}

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        $array = $this->toArray();

        return $array;
    }

    /**
     * Get the value used to index the model.
     *
     * @return mixed
     */
    public function getScoutKey()
    {}

    /**
     * Get the key name used to index the model.
     *
     * @return mixed
     */
    public function getScoutKeyName()
    {}
}
