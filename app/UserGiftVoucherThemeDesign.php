<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use App\UserGiftVoucherTheme;

class UserGiftVoucherThemeDesign extends BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'image',
        'theme_id',
        'is_removed'
    ];

    public $fileSystem = 'public';
    public $imagePath   = 'user\gift\voucher\designs\\';

    public function __construct()
    {
        parent::addHidden('theme_id');
    }

    public function validator(array $data)
    {
        return Validator::make($data, [
            'image'      => ['required', 'string', 'max:255'],
            'theme_id'   => ['required', 'integer', 'exists:' . UserGiftVoucherTheme::getTableName() . ',id'],
            'is_removed' => ['integer', 'in:0,1']
        ]);
    }

    public function validateImage($request)
    {
        return Validator::make($request->all(), [
            'icon' => 'mimes:jpg,png',
        ], [
            'icon' => 'Please select proper file. The file must be a file of type: jpg, png.'
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
}
