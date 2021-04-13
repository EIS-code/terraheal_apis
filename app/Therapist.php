<?php

namespace App;

use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Massage;
use Illuminate\Http\UploadedFile;
use DB;
use Carbon\Carbon;

class Therapist extends BaseModel implements CanResetPasswordContract
{
    use CanResetPassword, Notifiable;

    protected $fillable = [
        'name',
        'surname',
        'dob',
        'gender',
        'email',
        'tel_number',
        'mobile_number',
        'emergence_contact_number',
        'hobbies',
        'short_description',
        'is_freelancer',
        'paid_percentage',
        'is_deleted',
        'shop_id',
        'avatar',
        'avatar_original',
        'device_token',
        'device_type',
        'app_version',
        'oauth_uid',
        'oauth_provider',
        'profile_photo',
        'password',
        'is_email_verified',
        'is_mobile_verified',
        'is_document_verified',
        'account_number',
        'nif',
        'social_security_number',
        'health_conditions_allergies',
        'city_id',
        'country_id',
        'personal_description',
        'address'
    ];

    protected $hidden = ['is_deleted', 'created_at', 'updated_at', 'password'];

    public $fileSystem = 'public';
    public $profilePhotoPath = 'therapists\profile\\';

    const GENDER_MALE   = 'm';
    const GENDER_FEMALE = 'f';

    public static $bookingTypes = [
        self::GENDER_MALE   => 'Male',
        self::GENDER_FEMALE => 'Female'
    ];

    const IS_FREELANCER     = '1';
    const IS_NOT_FREELANCER = '0';

    public static $isFreelancer = [
        self::IS_FREELANCER     => 'Yes',
        self::IS_NOT_FREELANCER => 'No'
    ];
    
    const IS_DELETED = '1';
    const IS_NOT_DELETED = '0';

    public function getFullNameAttribute()
    {
        return $this->name . ' ' . $this->surname;
    }

    public function validator(array $data, $requiredFileds = [], $extraFields = [], $id = false, $isUpdate = false)
    {
        $user = NULL;
        if ($isUpdate === true && !empty($id)) {
            $emailValidator = ['unique:therapists,email,' . $id];
        } else {
            $emailValidator = ['unique:therapists'];
        }

        return Validator::make($data, array_merge([
            'name'                 => array_merge(['string', 'max:255'], !empty($requiredFileds['name']) ? $requiredFileds['name'] : ['nullable']),
            'surname'              => array_merge(['string', 'max:255'], !empty($requiredFileds['surname']) ? $requiredFileds['surname'] : ['nullable']),
            'dob'                  => array_merge(['date:Y-m-d'], !empty($requiredFileds['dob']) ? $requiredFileds['dob'] : ['nullable']),
            'gender'               => array_merge(['in:m,f'], !empty($requiredFileds['gender']) ? $requiredFileds['gender'] : ['nullable']),
            'email'                => array_merge(array_merge(['string', 'email', 'max:255'], $emailValidator), !empty($requiredFileds['email']) ? $requiredFileds['email'] : ['nullable']),
            'tel_number'           => array_merge(['string', 'max:50'], !empty($requiredFileds['tel_number']) ? $requiredFileds['tel_number'] : ['nullable']),
            'hobbies'              => array_merge(['string', 'max:255'], !empty($requiredFileds['hobbies']) ? $requiredFileds['hobbies'] : ['nullable']),
            'short_description'    => array_merge(['string', 'max:255'], !empty($requiredFileds['short_description']) ? $requiredFileds['short_description'] : ['nullable']),
            'shop_id'              => array_merge(['integer'], !empty($requiredFileds['shop_id']) ? $requiredFileds['shop_id'] : ['required']),
            'is_freelancer'        => array_merge(['required', 'in:' . implode(",", array_keys(self::$isFreelancer))], !empty($requiredFileds['is_freelancer']) ? $requiredFileds['is_freelancer'] : ['required']),
            'paid_percentage'      => array_merge(['integer'], !empty($requiredFileds['paid_percentage']) ? $requiredFileds['paid_percentage'] : ['nullable']),
            'avatar'               => array_merge(['max:255'], !empty($requiredFileds['avatar']) ? $requiredFileds['avatar'] : ['nullable']),
            'avatar_original'      => array_merge(['max:255'], !empty($requiredFileds['avatar_original']) ? $requiredFileds['avatar_original'] : ['nullable']),
            'device_token'         => array_merge(['max:255'], !empty($requiredFileds['device_token']) ? $requiredFileds['device_token'] : ['nullable']),
            'device_type'          => array_merge(['max:255'], !empty($requiredFileds['device_type']) ? $requiredFileds['device_type'] : ['nullable']),
            'app_version'          => array_merge(['max:255'], !empty($requiredFileds['app_version']) ? $requiredFileds['app_version'] : ['nullable']),
            'oauth_uid'            => array_merge(['max:255'], !empty($requiredFileds['oauth_uid']) ? $requiredFileds['oauth_uid'] : ['nullable']),
            'oauth_provider'       => array_merge([(!empty($data['oauth_uid']) ? 'required' : ''), (!empty($data['oauth_uid']) ? 'in:1,2,3,4' : '')], !empty($requiredFileds['oauth_provider']) ? $requiredFileds['oauth_provider'] : ['nullable']),
            'password'             => array_merge([(!$isUpdate ? 'required': ''), 'min:6', 'regex:/[a-z]/', 'regex:/[A-Z]/', 'regex:/[0-9]/', 'regex:/[@$!%*#?&]/'], !empty($requiredFileds['password']) ? $requiredFileds['password'] : ['nullable']),
            'is_email_verified'    => array_merge(['in:0,1'], !empty($requiredFileds['is_email_verified']) ? $requiredFileds['is_email_verified'] : ['nullable']),
            'is_mobile_verified'   => array_merge(['in:0,1'], !empty($requiredFileds['is_mobile_verified']) ? $requiredFileds['is_mobile_verified'] : ['nullable']),
            'is_document_verified' => array_merge(['in:0,1'], !empty($requiredFileds['is_document_verified']) ? $requiredFileds['is_document_verified'] : ['nullable']),
            'account_number'            => array_merge(['string', 'max:255'], !empty($requiredFileds['account_number']) ? $requiredFileds['account_number'] : ['nullable']),
            'nif'                       => array_merge(['string', 'max:255'], !empty($requiredFileds['nif']) ? $requiredFileds['nif'] : ['nullable']),
            'social_security_number'    => array_merge(['string', 'max:255'], !empty($requiredFileds['social_security_number']) ? $requiredFileds['social_security_number'] : ['nullable']),
            'health_conditions_allergies' => array_merge(['string'], !empty($requiredFileds['health_conditions_allergies']) ? $requiredFileds['health_conditions_allergies'] : ['nullable']),
            'mobile_number'             => array_merge(['string', 'max:255'], !empty($requiredFileds['mobile_number']) ? $requiredFileds['mobile_number'] : ['nullable']),
            'emergence_contact_number'  => array_merge(['string', 'max:255'], !empty($requiredFileds['emergence_contact_number']) ? $requiredFileds['emergence_contact_number'] : ['nullable']),
        ], $extraFields), [
            'password.regex'    => __('Password should contains at least one [a-z, A-Z, 0-9, @, $, !, %, *, #, ?, &].')
        ]);
    }

    public function validateProfilePhoto($request)
    {
        return Validator::make($request, [
            'profile_photo' => 'mimes:jpeg,png,jpg',
        ], [
            'profile_photo' => 'Please select proper file. The file must be a file of type: jpeg, png, jpg.'
        ]);
    }

    public function selectedMassages()
    {
        return $this->hasMany('App\TherapistSelectedMassage', 'therapist_id', 'id');
    }

    public function selectedTherapies()
    {
        return $this->hasMany('App\TherapistSelectedTherapy', 'therapist_id', 'id');
    }

    public function bookingInfos()
    {
        return $this->hasMany('App\BookingInfo', 'therapist_id', 'id');
    }

    public function documents()
    {
        return $this->hasMany('App\TherapistDocument', 'therapist_id', 'id');
    }

    public function languageSpokens()
    {
        return $this->hasMany('App\TherapistLanguage', 'therapist_id', 'id');
    }

    public function getProfilePhotoAttribute($value)
    {
        $default = asset('images/therapists/therapist.png');

        // For set default image.
        if (empty($value)) {
            return $default;
        }

        $profilePhotoPath = (str_ireplace("\\", "/", $this->profilePhotoPath));

        if (Storage::disk($this->fileSystem)->exists($profilePhotoPath . $value)) {
            return Storage::disk($this->fileSystem)->url($profilePhotoPath . $value);
        }

        return $default;
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $classPasswordNotification = new ResetPasswordNotification($token);

        $classPasswordNotification::$createUrlCallback = 'toMailContentsUrl';

        $this->notify($classPasswordNotification);
    }

    public static function isDocumentVerified(int $id, string $isVerified = '0')
    {
        $isVerified = (!in_array($isVerified, ['0', '1'])) ? '0' : $isVerified;

        return self::where('id', $id)->update(['is_document_verified' => $isVerified]);
    }

    public static function getGlobalQuery(int $isFreelancer = self::IS_NOT_FREELANCER, Request $request)
    {
        $model                  = new Self();
        $modelTherapistLanguage = new TherapistLanguage();
        $modelLanguage          = new Language();
        $modelCountry           = new Country();
        $modelCity              = new City();
        $data                   = $request->all();
        $id                     = !empty($data['id']) ? (int)$data['id'] : false;

        if (empty($id)) {
            return collect([]);
        }

        $model->setMysqlStrictFalse();

        $data = $model::selectRaw($model::getTableName() . '.*, CONCAT(' . $modelLanguage::getTableName() . '.name, " ", CASE WHEN ' . $modelTherapistLanguage::getTableName() . '.type = "1" THEN "- Basic" WHEN ' . $modelTherapistLanguage::getTableName() . '.type = "2" THEN "- Good" WHEN ' . $modelTherapistLanguage::getTableName() . '.type = "3" THEN "- Fluent" ELSE NULL END) AS language_spoken, ' . $modelCountry::getTableName() . '.name AS country_name, ' . $modelCity::getTableName() . '.name AS city_name')
                      ->with(['documents', 'languageSpokens'])
                      ->leftJoin($modelTherapistLanguage::getTableName(), $model::getTableName() . '.id', '=', $modelTherapistLanguage::getTableName() . '.therapist_id')
                      ->leftJoin($modelLanguage::getTableName(), $modelTherapistLanguage::getTableName() . '.language_id', '=', $modelLanguage::getTableName() . '.id')
                      ->leftJoin($modelCountry::getTableName(), $model::getTableName() . '.country_id', '=', $modelCountry::getTableName() . '.id')
                      ->leftJoin($modelCity::getTableName(), $model::getTableName() . '.city_id', '=', $modelCity::getTableName() . '.id')
                      ->where($model::getTableName() . '.id', $id)
                      ->groupBy($model::getTableName() . '.id')
                      ->get();

        if (!empty($data) && !$data->isEmpty($data)) {
            $bookingInfo = new BookingInfo();

            $data->map(function($record, $key) use($bookingInfo, $request) {
                $record->selected_services  = $record->selectedServices();

                $record->total_massages     = $bookingInfo->getMassageCountByTherapist($record->id);
                $record->total_therapies    = $bookingInfo->getTherapyCountByTherapist($record->id);

                $record->request = $request->all();
            });
        }

        $model->setMysqlStrictTrue();

        return $data;
    }

    public function selectedServices()
    {
        $selectedMassages  = TherapistSelectedMassage::select(TherapistSelectedMassage::getTableName() . '.id', 'massage_id', Massage::getTableName() . '.name as massage_name', Massage::getTableName() . '.image')->join(Massage::getTableName(), TherapistSelectedMassage::getTableName() . '.massage_id', '=', Massage::getTableName() . '.id')->where('therapist_id', $this->id)->get();

        if (!empty($selectedMassages) && !$selectedMassages->isEmpty()) {
            $model = new Massage();

            $selectedMassages->map(function($record) use($model) {
                if (!empty($record->image)) {
                    $imagePath = (str_ireplace("\\", "/", $model->imagePath));

                    $record->image = Storage::disk($model->fileSystem)->url($imagePath . $record->image);
                }
            });
        }

        $selectedTherapies = TherapistSelectedTherapy::select(TherapistSelectedTherapy::getTableName() . '.id', 'therapy_id', Therapy::getTableName() . '.name as therapy_name', Therapy::getTableName() . '.image')->join(Therapy::getTableName(), TherapistSelectedTherapy::getTableName() . '.therapy_id', '=', Therapy::getTableName() . '.id')->where('therapist_id', $this->id)->get();

        if (!empty($selectedTherapies) && !$selectedTherapies->isEmpty()) {
            $model = new Therapy();

            $selectedTherapies->map(function($record) use($model) {
                if (!empty($record->image)) {
                    $imagePath = (str_ireplace("\\", "/", $model->imagePath));

                    $record->image = Storage::disk($model->fileSystem)->url($imagePath . $record->image);
                }
            });
        }

        return collect(['massages' => $selectedMassages, 'therapies' => $selectedTherapies]);
    }

    public function getMassageCountAttribute()
    {
        return $this->selectedMassages()->count();
    }

    public function getTherapyCountAttribute()
    {
        return $this->selectedTherapies()->count();
    }

    public static function updateProfile(int $isFreelancer = Therapist::IS_NOT_FREELANCER, Request $request)
    {
        $model                          = new self();
        $modelTherapistLanguage         = new TherapistLanguage();
        $modelTherapistDocument         = new TherapistDocument();
        $modelTherapistSelectedMassage  = new TherapistSelectedMassage();

        $data   = $request->all();
        $id     = !empty($data['id']) ? (int)$data['id'] : false;
        $inc    = 0;

        if (!empty($data['dob'])) {
            $data['dob'] = date('Y-m-d', ($data['dob'] / 1000));
        } else {
            unset($data['dob']);
        }

        $data['is_freelancer'] = $isFreelancer;

        if (empty($id)) {
            return ['isError' => true, 'message' => 'notFound'];
        }

        if (!$model::find($id)->where('is_freelancer', (string)$isFreelancer)->exists()) {
            return ['isError' => true, 'message' => 'notFound'];
        }

        $checks = $model->validator($data, [], [], $id, true);
        if ($checks->fails()) {
            return ['isError' => true, 'message' => $checks->errors()->first()];
        }

        /* For language spoken. */
        // For language_spoken[2] ...
        if (!empty($data['language_spoken'])) {
            if (is_array($data['language_spoken'])) {
                /*foreach ($data['language_spoken'] as $languageId => $languageSpoken) {
                    $languageData[] = [
                        'type'          => $languageSpoken,
                        'value'         => $modelTherapistLanguage::THEY_CAN_VALUE,
                        'language_id'   => $languageId,
                        'therapist_id'  => $id
                    ];
                }*/

                $checks = $modelTherapistLanguage->validators($languageData);
                if ($checks->fails()) {
                    return ['isError' => true, 'message' => $checks->errors()->first()];
                }
            }
        }
        // For language_spoken_2 ...
        $keys            = array_keys($data);
        $pattern         = '#^language_spoken_(.*)$#i';
        $languageSpokens = preg_grep($pattern, $keys);

        foreach ($languageSpokens as $key => $languageKey) {
            $languageSpoken = $data[$languageKey];
            $keyData        = explode("_", $languageKey);

            if (!empty($keyData[2]) && is_numeric($keyData[2])) {
                $languageId = $keyData[2];

                $languageData[] = [
                    'type'          => $languageSpoken,
                    'value'         => $modelTherapistLanguage::THEY_CAN_VALUE,
                    'language_id'   => $languageId,
                    'therapist_id'  => $id
                ];
            }
        }

        if (isset($data['language_id']) & isset($data['language_type'])) {
            $languageData[] = [
                'type' => $data['language_type'],
                'value' => $modelTherapistLanguage::THEY_CAN_VALUE,
                'language_id' => $data['language_id'],
                'therapist_id' => $id
            ];

            $checks = $modelTherapistLanguage->validators($languageData);
            if ($checks->fails()) {
                return ['isError' => true, 'message' => $checks->errors()->first()];
            }
        }

        /* For profile Image */
        if (!empty($data['profile_photo']) && $data['profile_photo'] instanceof UploadedFile) {
            $checkImage = $model->validateProfilePhoto($data);

            if ($checkImage->fails()) {
                unset($data['profile_photo']);

                return ['isError' => true, 'message' => $checks->errors()->first()];
            }

            $fileName = $data['profile_photo']->getClientOriginalName();
            $fileName = time() . '_' . $id . '.' . $data['profile_photo']->getClientOriginalExtension();

            $storeFile = $data['profile_photo']->storeAs($model->profilePhotoPath, $fileName, $model->fileSystem);

            if ($storeFile) {
                $data['profile_photo_name'] = $data['profile_photo'] = $fileName;
            }
        }

        /* For document uploads. */
        $documents    = array_keys($data);
        $documentData = [];

        $checkDocument = function($request, $key, $format, $inc, $type) use(&$documentData) {
            $getDocument = self::getDocumentFromRequest($request, $key, $format, $inc, $type);

            if (!empty($getDocument['error'])) {
                return ['isError' => true, 'message' => $getDocument['error']];
            } elseif (!empty($getDocument)) {
                foreach ((array)$getDocument as $document) {
                    if (!empty($document['error'])) {
                        return $document['error'];
                    } elseif (!empty($document['data'])) {
                        array_push($documentData, $document['data']);
                    }
                }
            }
        };

        foreach ($documents as $document) {
            switch ($document) {
                case 'document_id_passport_front':
                    $key = 'document_id_passport_front';

                    $checkDocumentError = $checkDocument($request, $key, 'jpeg,png,jpg', $inc, $modelTherapistDocument::TYPE_IDENTITY_PROOF_FRONT);

                    if ($checkDocumentError) {
                        return ['isError' => true, 'message' => $checkDocumentError];
                    }

                    break;
                case 'document_id_passport_back':
                    $key = 'document_id_passport_back';

                    $checkDocumentError = $checkDocument($request, $key, 'jpeg,png,jpg', $inc, $modelTherapistDocument::TYPE_IDENTITY_PROOF_BACK);

                    if ($checkDocumentError) {
                        return ['isError' => true, 'message' => $checkDocumentError];
                    }

                    break;
                case 'document_insurance':
                    $key = 'document_insurance';

                    $checkDocumentError = $checkDocument($request, $key, 'jpeg,png,jpg,pdf', $inc, $modelTherapistDocument::TYPE_INSURANCE);

                    if ($checkDocumentError) {
                        return ['isError' => true, 'message' => $checkDocumentError];
                    }

                    break;
                case 'document_freelancer_financial_document':
                    $key = 'document_freelancer_financial_document';

                    $checkDocumentError = $checkDocument($request, $key, 'jpeg,png,jpg,pdf', $inc, $modelTherapistDocument::TYPE_FREELANCER_FINANCIAL_DOCUMENT);

                    if ($checkDocumentError) {
                        return ['isError' => true, 'message' => $checkDocumentError];
                    }

                    break;
                case 'document_certificates':
                    $key = 'document_certificates';

                    $checkDocumentError = $checkDocument($request, $key, 'jpeg,png,jpg,pdf', $inc, $modelTherapistDocument::TYPE_CERTIFICATES);

                    if ($checkDocumentError) {
                        return ['isError' => true, 'message' => $checkDocumentError];
                    }

                    break;
                case 'document_cv':
                    $key = 'document_cv';

                    $checkDocumentError = $checkDocument($request, $key, 'jpeg,png,jpg,pdf,doc,docx', $inc, $modelTherapistDocument::TYPE_CV);

                    if ($checkDocumentError) {
                        return ['isError' => true, 'message' => $checkDocumentError];
                    }

                    break;
                case 'document_reference_letter':
                    $key = 'document_reference_letter';

                    $checkDocumentError = $checkDocument($request, $key, 'jpeg,png,jpg,pdf,doc,docx', $inc, $modelTherapistDocument::TYPE_REFERENCE_LATTER);

                    if ($checkDocumentError) {
                        return ['isError' => true, 'message' => $checkDocumentError];
                    }

                    break;
                case 'document_others':
                    $key = 'document_others';

                    $checkDocumentError = $checkDocument($request, $key, 'jpeg,png,jpg,pdf,doc,docx', $inc, $modelTherapistDocument::TYPE_OTHERS);

                    if ($checkDocumentError) {
                        return ['isError' => true, 'message' => $checkDocumentError];
                    }

                    break;
                case 'document_personal_experience':
                    $key = 'document_personal_experience';

                    $checkDocumentError = $checkDocument($request, $key, 'jpeg,png,jpg,pdf', $inc, $modelTherapistDocument::PERSONAL_EXPERIENCES);

                    if ($checkDocumentError) {
                        return ['isError' => true, 'message' => $checkDocumentError];
                    }

                    break;
                default:
                    break;
            }
        }

        // My services.
        $massageData = [];

        if (!empty($data['my_massages'])) {
            foreach ((array)$data['my_massages'] as $myMassage) {
                $data['my_services']['massages'][] = $myMassage;
            }
        }

        if (!empty($data['my_services']['massages'])) {
            foreach ((array)$data['my_services']['massages'] as $massageId) {
                $massageData[] = [
                    'massage_id'    => $massageId,
                    'therapist_id'  => $id
                ];
            }

            $checks = $modelTherapistSelectedMassage->validators($massageData);
            if ($checks->fails()) {
                return ['isError' => true, 'message' => $checks->errors()->first()];
            }
        }

        // Insert therapist data.
        $model::find($id)->update($data);

        // Insert language spoken data.
        if (!empty($languageData)) {
            $modelTherapistLanguage::where('therapist_id', $id)->delete();

            foreach ($languageData as $language) {
                $modelTherapistLanguage::updateOrCreate(['language_id' => $language['language_id'], 'therapist_id' => $language['therapist_id']], $language);
            }
        }
        
        if(!empty($data['doc_name']) && !empty($data['document']))
        {
            $key = 'document';
            $checkDocumentError = $checkDocument($request, $key, 'jpeg,png,jpg,pdf,doc,docx', $inc, $modelTherapistDocument::TYPE_OTHERS);
            if ($checkDocumentError) {
                return $this->returns($checkDocumentError, NULL, true);
            }
        }

        // Store documents.
        if (!empty($documentData)) {
            foreach ($documentData as $document) {
                if (empty($document['file_name'])) {
                    continue;
                }

                $fileName  = $document['file_name'];

                $storeFile = $document[$document['key']]->storeAs($modelTherapistDocument->directory, $fileName, $modelTherapistDocument->fileSystem);

                if ($storeFile) {
                    if($document['key'] == 'document')
                    {
                        $document['doc_name'] = $data['doc_name'];
                        $document['is_expired'] = $data['is_expired'];
                        $document['expired_date'] = $data['expired_date'];
                        $document['uploaded_by'] = $data['uploaded_by'];
                    }
                    if (in_array($document['type'], [$modelTherapistDocument::TYPE_CERTIFICATES, $modelTherapistDocument::TYPE_OTHERS, $modelTherapistDocument::PERSONAL_EXPERIENCES])) {
                        $modelTherapistDocument::create($document);
                    } else {
                        $modelTherapistDocument::updateOrCreate(['therapist_id' => $id, 'type' => $document['type']], $document);
                    }
                }
            }

            // Check all documents uploaded.
            if ($modelTherapistDocument->checkAllDocumentsUploaded($id)) {
                $model::isDocumentVerified($id, '1');
            }
        }

        if (!empty($massageData)) {
            foreach ($massageData as $massage) {
                $modelTherapistSelectedMassage::updateOrCreate(['massage_id' => $massage['massage_id'], 'therapist_id' => $massage['therapist_id']], $massage);
            }
        }

        return true;
    }

    public static function getDocumentFromRequest(Request $request, string $key, string $formats = 'jpeg,png,jpg', int &$inc, string $type) : Array
    {
        $data           = $request->all();

        $descriptions   = !empty($data['description_personal_experience']) ? $data['description_personal_experience'] : [];

        $id             = !empty($data['id']) ? (int)$data['id'] : false;

        $documentData   = [];

        $createData = function($file, $key, $formats, &$inc, $id, $type) {
            $pathInfo = pathinfo($file->getClientOriginalName());

            $data     = [];

            if (!empty($pathInfo['extension'])) {
                $ramdomStrings = generateRandomString(6);

                $fileName = !empty($pathInfo['filename']) ? $pathInfo['filename'] . $ramdomStrings . "." . $pathInfo['extension'] : $ramdomStrings . "." . $pathInfo['extension'];

                $data = [
                    'type'          => $type,
                    'file_name'     => $fileName,
                    'therapist_id'  => $id,
                    'key'           => $key,
                    $key            => $file
                ];

                $checks = TherapistDocument::validator($data, $key, $formats);
                if ($checks->fails()) {
                    return ['error' => $checks->errors()->first(), 'data' => NULL];
                } else {
                    $inc++;
                }
            }

            return ['error' => false, 'data' => $data];
        };

        if (!empty($data[$key])) {
            if (is_array($data[$key])) {
                foreach ($data[$key] as $index => $document) {
                    $documentData[$inc] = $createData($document, $key, $formats, $inc, $id, $type);

                    if (!empty($descriptions[$index])) {
                        $documentData[$inc]['data']['description'] = $descriptions[$index];
                    }
                }
            } elseif ($data[$key] instanceof UploadedFile) {
                $documentData[$inc] = $createData($data[$key], $key, $formats, $inc, $id, $type);

                if (!empty($descriptions[0])) {
                    $documentData[$inc]['data']['description'] = $descriptions[$index];
                }
            }
        }

        return $documentData;
    }
    
    public function getTherapist(Request $request) {

        $therapists = DB::table('booking_massages')
                ->join('booking_infos', 'booking_infos.id', '=', 'booking_massages.booking_info_id')
                ->join('bookings', 'bookings.id', '=', 'booking_infos.booking_id')
                ->join('therapists', 'therapists.id', '=', 'booking_infos.therapist_id')
                ->select('bookings.id as booking_id', 'booking_infos.id as booking_info_id', 'booking_massages.id as booking_massage_id', 'booking_infos.massage_time as massageStartTime', 'booking_infos.massage_date as massageDate', 'therapists.id as therapist_id', DB::raw('CONCAT(COALESCE(therapists.name,"")," ",COALESCE(therapists.surname,"")) AS therapistName'))
                ->where('bookings.shop_id', $request->shop_id);

        //0 for today, 1 for all therapist
        if (isset($request->filter) && $request->filter == 0) {

            $therapists = $therapists->where('booking_infos.massage_date', Carbon::now()->format('Y-m-d'))->orderBy('booking_massage_id', 'DESC')->get()->groupBy('therapist_id')->toArray();
            $todayTherapist = [];
            foreach ($therapists as $key => $value) {

                $current = Carbon::now()->format('H:i');
                $date = Carbon::parse($value[0]->massageStartTime);

                if ($current >= $date->format('H:i')) {
                    $available = 'now';
                } else {
                    $start_time = new Carbon($current);
                    $end_time = new Carbon($date->format('H:i:s'));
                    $diff = $start_time->diff($end_time)->format("%h:%i");
                    $time = explode(':', $diff);
                    if ($time[0] == 0) {
                        $available = 'In ' . $time[1] . ' min';
                    } else {
                        $available = $diff;
                    }
                }
                $data = [
                    'therapist_id' => $value[0]->therapist_id,
                    'therapistName' => $value[0]->therapistName,
                    'massageDate' => $value[0]->massageDate,
                    'massageStartTime' => strtotime($value[0]->massageStartTime) * 1000,
                    'available' => $available
                ];
                array_push($todayTherapist, $data);
            }

            return $todayTherapist;
        } else {
            $therapists = Therapist::all()->toArray();
            return $therapists;
        }
    }
    
    public function country()
    {
        return $this->hasOne('App\Country', 'id', 'country_id');
    }
    
    public function city()
    {
        return $this->hasOne('App\City', 'id', 'city_id');
    }
    
    public function shop()
    {
        return $this->hasOne('App\Shop', 'id', 'shop_id');
    }
    
    public function serviceStart(Request $request) {
        
        $model              = new BookingMassageStart();
        $data               = $request->all();
        $bookingMassageId   = $request->get('booking_massage_id', false);

        if (empty($bookingMassageId)) {
            return $this->returns('Booking massage not found.', NULL, true);
        }

        $data['actual_total_time']  = BookingMassage::getMassageTime($bookingMassageId);

        $data['start_time']         = !empty($data['start_time']) ? Carbon::createFromTimestampMs($data['start_time']) : false;
        $startTime                  = clone $data['start_time'];
        $data['end_time']           = !empty($startTime) ? $startTime->addMinutes($data['actual_total_time'])->format('H:i:s') : false;
        $data['start_time']         = !empty($data['start_time']) ? $data['start_time']->format('H:i:s') : false;

        $checks = $model->validator($data);
        if ($checks->fails()) {
            return ['isError' => true, 'message' => $checks->errors()->first()];
        }

        $create = $model::updateOrCreate(['booking_massage_id' => $bookingMassageId], $data);
        
        return collect(['create' => $create]);
        
    }
    
    public function serviceEnd(Request $request) {
        
        $model              = new BookingMassageStart();
        $data               = $request->all();
        $bookingMassageId   = $request->get('booking_massage_id', false);

        $currentTime        = Carbon::now();


        if (empty($data['end_time'])) {
            return ['isError' => true, 'message' => 'End time not found.'];
        }

        $find = $model::where('booking_massage_id', $bookingMassageId)->first();

        if (empty($find)) {
            return ['isError' => true, 'message' => 'Booking massage not found.'];
        }
        
        if ($find->start_time > $data['end_time']) {
            return ['isError' => true, 'message' => 'End time always greater than start time.'];
        }
        
        $data['end_time']   = !empty($data['end_time']) ? Carbon::createFromTimestampMs($data['end_time'])->format('H:i:s') : false;

        $data['taken_total_time'] = (new Carbon($find->start_time))->diffInMinutes($currentTime->format('H:i:s'));
        
        $find->end_time         = $data['end_time'];

        $find->taken_total_time = $data['taken_total_time'];

        $find->update();
        
        return collect(['find' => $find]);
        
    }
}
