<?php

namespace App;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\SoftDeletes;

class TherapistDocument extends BaseModel
{
    use SoftDeletes;

    protected $fillable = [
        'type',
        'file_name',
        'description',
        'therapist_id',
        'doc_name',
        'is_expired',
        'expired_date',
        'uploaded_by'
    ];
    
    protected $hidden = ['is_removed', 'updated_at'];

    public $fileSystem = 'public';
    public $directory  = 'therapists\document\\';

    // const TYPE_ADDRESS_PROOF                    = '1';
    const TYPE_IDENTITY_PROOF_FRONT             = '2';
    const TYPE_IDENTITY_PROOF_BACK              = '3';
    const TYPE_INSURANCE                        = '4';
    const TYPE_FREELANCER_FINANCIAL_DOCUMENT    = '5';
    const TYPE_CERTIFICATES                     = '6';
    const TYPE_CV                               = '7';
    const TYPE_REFERENCE_LATTER                 = '8';
    const PERSONAL_EXPERIENCES                  = '9';
    const TYPE_OTHERS                           = '10';

    public $documentTypes = [
        // self::TYPE_ADDRESS_PROOF                    => 'Address Proof',
        self::TYPE_IDENTITY_PROOF_FRONT             => 'Identity Proof Front',
        self::TYPE_IDENTITY_PROOF_BACK              => 'Identity Proof back',
        self::TYPE_INSURANCE                        => 'Insurance',
        self::TYPE_FREELANCER_FINANCIAL_DOCUMENT    => 'Freelancer financial document',
        self::TYPE_CERTIFICATES                     => 'Certificates',
        self::TYPE_CV                               => 'CV',
        self::TYPE_REFERENCE_LATTER                 => 'Reference Latter',
        self::PERSONAL_EXPERIENCES                  => 'Personal Experience',
        self::TYPE_OTHERS                           => 'Others'
    ];

    public static function validator(array $data, $file, $mimes = 'jpeg,png,jpg,doc,docx,pdf')
    {
        return Validator::make($data, [
            'type'         => ['required', 'in:1,2,3,4,5,6,7,8,9,10'],
            'file_name'    => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'therapist_id' => ['required', 'integer'],
            $file          => ['mimes:' . $mimes],
        ], [
            $file => 'Please select proper file. The file must be a file of type: ' . $mimes . '.'
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
    
    public function checkMimeTypes($request, $file, $mimes = 'jpeg,png,jpg,doc,docx,pdf')
    {
        return Validator::make($request, [
            $file => 'mimes:' . $mimes,
        ], [
            $file => 'Please select proper file. The file must be a file of type: ' . $mimes . '.'
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

    public function removeDocument(string $documentName)
    {
        $directory = (str_ireplace("\\", "/", $this->directory));

        if (Storage::disk($this->fileSystem)->exists($directory . $documentName)) {
            return Storage::disk($this->fileSystem)->delete($directory . $documentName);
        }

        return false;
    }

    public function checkAllDocumentsUploaded(int $therapistId) : bool
    {
        $isUploadedAll = false;

        $modelTherapistDocument = new TherapistDocument();

        $getDocuments = $modelTherapistDocument::where('therapist_id', $therapistId)->get();

        if (!empty($getDocuments) && !$getDocuments->isEmpty()) {
            $documentTypes = $modelTherapistDocument->documentTypes;
            $uploadedType  = $getDocuments->pluck('type')->unique();

            foreach ($uploadedType as $type) {
                if (array_key_exists($type, $documentTypes)) {
                    unset($documentTypes[$type]);
                }
            }

            $isUploadedAll = ((empty($documentTypes)) ? true : false);
        }

        return $isUploadedAll;
    }
}
