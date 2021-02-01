<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

class TherapistDocument extends Model
{
    protected $fillable = [
        'type',
        'file_name',
        'therapist_id'
    ];

    public $directory = 'therapist/document';

    const TYPE_ADDRESS_PROOF  = '1';
    const TYPE_IDENTITY_PROOF = '2';
    const TYPE_INSURANCE      = '3';
    const TYPE_FREELANCER_FINANCIAL_DOCUMENT = '4';
    const TYPE_CERTIFICATES = '5';
    const TYPE_CV = '6';
    const TYPE_REFERENCE_LATTER = '7';
    const TYPE_OTHERS = '8';

    public $documentTypes = [
        self::TYPE_ADDRESS_PROOF  => 'Address Proof',
        self::TYPE_IDENTITY_PROOF => 'Identity Proof',
        self::TYPE_INSURANCE      => 'Insurance',
        self::TYPE_FREELANCER_FINANCIAL_DOCUMENT => 'Freelancer financial document',
        self::TYPE_CERTIFICATES   => 'Certificates',
        self::TYPE_CV             => 'CV',
        self::TYPE_REFERENCE_LATTER => 'Reference Latter',
        self::TYPE_OTHERS         => 'Others'
    ];

    public function validator(array $data)
    {
        return Validator::make($data, [
            'type'         => ['required', 'in:1,2,3'],
            'file_name'    => ['required', 'string', 'max:255'],
            'therapist_id' => ['required', 'integer']
        ]);
    }
    
    public function validateMimeTypes($request)
    {
        return Validator::make($request->all(), [
            'file.*' => 'mimes:jpeg,png,jpg,doc,docx,pdf',
        ], [
            'file.*' => 'Please select proper file. The file must be a file of type: jpeg, png, jpg, doc, docx, pdf.'
        ]);
    }
}
