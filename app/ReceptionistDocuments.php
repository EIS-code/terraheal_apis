<?php

namespace App;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ReceptionistDocuments extends BaseModel
{
    protected $fillable = [
        'document_name',
        'file_name',
        'receptionist_id',
        'is_expired',
        'expire_date'
    ];

    public $fileSystem = 'public';
    public $directory  = 'receptionist\document\\';

    public static function validator(array $data, $mimes = 'jpeg,png,jpg,doc,docx,pdf')
    {
        return Validator::make($data, [
            'document_name'         => ['required','string'],           
            'receptionist_id' => ['required', 'integer'],
            'file_name'    => ['mimes:' . $mimes],
        ], [
            'file_name' => 'Please select proper file. The file must be a file of type: ' . $mimes . '.'
        ]);
    }
    
    public function getFileNameAttribute($value)
    {

        $default = 'document.png';

        // For set default image.
        if (empty($value)) {
            $value = $default;
        }

        $directory = (str_ireplace("\\", "/", $this->directory));
        if (Storage::disk($this->fileSystem)->exists($directory . $value)) {
            return Storage::disk($this->fileSystem)->url($directory . $value);
        }

        return $default;
    }
    
    public function getExpireDateAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        return strtotime($value) * 1000;
    }
}
