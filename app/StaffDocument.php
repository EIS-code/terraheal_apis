<?php

namespace App;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class StaffDocument extends BaseModel
{
    protected $fillable = [
        'document_name',
        'document',
        'staff_id',
        'is_expired',
        'expired_date',
        'model_id',
        'uploaded_by'
    ];
    
    protected $hidden = ['updated_at'];

    public $fileSystem = 'public';
    public $directory  = 'staff\document\\';

    public static function validator(array $data, $mimes = 'jpeg,png,jpg,doc,docx,pdf')
    {
        return Validator::make($data, [
            'document_name'         => ['required','string'],           
            'document'              => ['required','string'],         
            'staff_id'              => ['required', 'integer', 'exists:' . Staff::getTableName() . ',id'],
            'model_id'              => ['required', 'integer', 'exists:' . Manager::getTableName() . ',id'],
            'uploaded_by'           => ['required', 'string'],
            'document'              => ['required','mimes:' . $mimes],
        ], [
            'document'              => 'Please select proper file. The file must be a file of type: ' . $mimes . '.'
        ]);
    }
    
    public function getDocumentAttribute($value)
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
    
    public function getExpiredDateAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        return strtotime($value) * 1000;
    }
    
    public function getCreatedAtAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        return strtotime($value) * 1000;
    }
}
