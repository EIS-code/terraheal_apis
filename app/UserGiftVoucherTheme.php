<?php

namespace App;

use Illuminate\Support\Facades\Validator;

class UserGiftVoucherTheme extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'is_removed'
    ];

    public $fileSystem = 'public';
    public $imagePath   = 'user\gift\voucher\designs\\';

    public function validator(array $data)
    {
        return Validator::make($data, [
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_removed'  => ['integer', 'in:0,1']
        ]);
    }

    public function designs()
    {
        return $this->hasMany('App\UserGiftVoucherThemeDesign', 'theme_id', 'id');
    }
}
