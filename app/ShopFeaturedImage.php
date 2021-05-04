<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use App\Shop;

class ShopFeaturedImage extends Model
{
    protected $fillable = [
        'image',
        'shop_id'
    ];

    public $fileSystem  = 'public';
    public $storageFolderName = 'shop\\featured';

    public function validator(array $data)
    {
        return Validator::make($data, [
            'image'   => ['required', 'string', 'max:255'],
            'shop_id' => ['required', 'integer', 'exists:' . Shop::getTableName() . ',id']
        ]);
    }
    
    public function validateImages($request)
    {
        return Validator::make($request, [
            'featured_images' => 'mimes:jpeg,png,jpg',
        ], [
            'featured_images.*' => 'Please select proper file. The file must be a file of type: jpeg, png, jpg.'
        ]);
    }
}
