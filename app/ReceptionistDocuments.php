<?php

namespace App;

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
}
