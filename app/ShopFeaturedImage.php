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

    public function validator(array $data, $id = false, $isUpdate = false)
    {
        return Validator::make($data, [
            'image'   => ['required', 'string', 'max:255'],
            'shop_id' => ['required', 'integer', 'exists:' . Shop::getTableName() . ',id']
        ]);
    }
}
