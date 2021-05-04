<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use App\Shop;

class ShopGallary extends Model
{
    protected $fillable = [
        'image',
        'shop_id'
    ];

    public $fileSystem  = 'public';
    public $storageFolderName = 'shop\\gallary';

    public function validator(array $data, $id = false, $isUpdate = false)
    {
        return Validator::make($data, [
            'image'   => ['required', 'string', 'max:255'],
            'shop_id' => ['required', 'integer', 'exists:' . Shop::getTableName() . ',id']
        ]);
    }
    
    public function validateImages($request)
    {
        return Validator::make($request, [
            'gallery' => 'mimes:jpeg,png,jpg',
        ], [
            'gallery.*' => 'Please select proper file. The file must be a file of type: jpeg, png, jpg.'
        ]);
    }
    
}
