<?php

namespace App;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ServiceImage extends BaseModel
{
    protected $fillable = [
        'service_id',
        'image',
        'is_featured'
    ];
    
    protected $hidden = ['created_at', 'updated_at'];
    
    const IS_FEATURED = '1';
    const IS_NOT_FEATURED = '0';

    public static $images = [
        self::IS_FEATURED => 'Featured image',
        self::IS_NOT_FEATURED => 'Gallery image'
    ];
    
    public $fileSystem = 'public';
    public $directory  = 'service\images\\';

    public static function validator(array $data, $mimes = 'jpeg,png,jpg')
    {
        return Validator::make($data, [
            'service_id'        => ['required', 'integer', 'exists:' . Service::getTableName() . ',id'],
            'image'             => ['required'],
            'is_featured'       => ['required', 'in:' . implode(",", array_keys(self::$images))]
        ]);
    }
    
    public function validateFeaturedImage($request)
    {
        return Validator::make($request, [
            'featured_image' => 'mimes:jpeg,png,jpg',
        ], [
            'featured_image' => 'Please select proper file. The file must be a file of type: jpeg, png, jpg.'
        ]);
    }
    
    public function getImageAttribute($value)
    {

        $directory = (str_ireplace("\\", "/", $this->directory));
        if (Storage::disk($this->fileSystem)->exists($directory . $value)) {
            return Storage::disk($this->fileSystem)->url($directory . $value);
        }

        return $value;
    }
    
    public function getIsFeaturedAttribute($value)
    {
        return (isset(self::$images[$value])) ? self::$images[$value] : $value;
    }
}
