<?php

namespace App\Http\Controllers\Therapist;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use App\Therapist;
use App\UserPeople;
use App\SessionType;
use App\Booking;
use App\BookingInfo;
use App\BookingMassage;
use App\Massage;
use App\MassagePrice;
use App\MassageTiming;
use App\MassagePreferenceOption;
use App\TherapistLanguage;
use App\TherapistDocument;
use App\TherapistSelectedMassage;
use DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\UploadedFile;

class TherapistController extends BaseController
{

    public $errorMsg = [
        'loginEmail' => "Please provide email properly.",
        'loginPass'  => "Please provide password properly.",
        'loginBoth'  => "Therapist email or password seems wrong.",
        'profile.update.error' => "Therapist not found."
    ];

    public $successMsg = [
        'login' => "Therapist found successfully !",
        'booking.details.found.successfully' => "Bookings found successfully !",
        'booking.today.found.successfully' => "Today bookings found successfully !",
        'booking.future.found.successfully' => "Future bookings found successfully !",
        'booking.past.found.successfully' => "Past bookings found successfully !",
        'calender.get.successfully' => "Calender get successfully !",
        'profile.update.successfully' => "Therapist profile updated successfully !"
    ];

    public function signIn(int $isFreelancer = Therapist::IS_NOT_FREELANCER, Request $request)
    {
        $model    = new Therapist();
        $data     = $request->all();
        $email    = (!empty($data['email'])) ? $data['email'] : NULL;
        $password = (!empty($data['password'])) ? $data['password'] : NULL;

        if (empty($email)) {
            return $this->returnError($this->errorMsg['loginEmail']);
        } elseif (empty($password)) {
            return $this->returnError($this->errorMsg['loginPass']);
        }

        if (!empty($email) && !empty($password)) {
            $getTherapist = $model->where(['email' => $email, 'is_freelancer' => (string)$isFreelancer])->first();

            if (!empty($getTherapist) && Hash::check($password, $getTherapist->password)) {
                $getTherapist = $getTherapist->first();

                return $this->returnSuccess(__($this->successMsg['login']), $getTherapist);
            } else {
                return $this->returnError($this->errorMsg['loginBoth']);
            }
        }

        return $this->returnNull();
    }

    public function getGlobalResponse(Request $request)
    {
        // $data = Therapist::with('selectedMassages', 'selectedTherapies')->where('id', $id)->first();

        $bookingModel = new Booking();

        $data = $bookingModel->getGlobalQuery($request);

        if (!empty($data)) {
            return $this->returnSuccess(__($this->successMsg['booking.details.found.successfully']), $data);
        }

        return $this->returnNull();
    }

    public function filter(Collection &$return)
    {
        if (!empty($return) && !$return->isEmpty()) {
            $increments = 0;

            $return->map(function(&$data, $index) use(&$return, &$increments) {
                if (empty($data->bookingInfoWithFilters) || $data->bookingInfoWithFilters->isEmpty()) {
                    unset($return[$index]);
                } elseif (request()->get('client_name', false) && empty($data->bookingInfoWithFilters[0]->userPeople)) {
                    unset($return[$index]);
                } elseif (empty($data->bookingInfoWithFilters[0]->therapist) || $data->bookingInfoWithFilters[0]->therapist->shop_id !== $data->shop_id) {
                    unset($return[$index]);
                } else {
                    foreach ($data->bookingInfoWithFilters as &$bookingInfoWithFilter) {
                        if (!empty($bookingInfoWithFilter->userPeople)) {
                            unset($bookingInfoWithFilter->userPeople);
                        }

                        if (!empty($bookingInfoWithFilter->therapist)) {
                            unset($bookingInfoWithFilter->therapist);
                        }
                    }

                    $data->booking_infos = $data->bookingInfoWithFilters;

                    unset($data->bookingInfoWithFilters);
                }
            });
        }

        return $return;
    }

    public function getTodayBooking(Request $request)
    {
        $bookingModel = new Booking();

        $data = $bookingModel->with('bookingInfoWithFilters')->filterDatas()->get();

        $this->filter($data);

        return $this->returns('booking.today.found.successfully', $data);
    }

    public function getFutureBooking(Request $request)
    {
        $bookingModel = new Booking();

        $data = $bookingModel->with('bookingInfoWithFilters')->filterDatas()->get();

        $this->filter($data);

        return $this->returns('booking.future.found.successfully', $data);
    }

    public function getPastBooking(Request $request)
    {
        $bookingModel = new Booking();

        $data = $bookingModel->with('bookingInfoWithFilters')->filterDatas()->get();

        $this->filter($data);

        return $this->returns('booking.past.found.successfully', $data);
    }

    public function getCalender(Request $request)
    {
        $model  = new BookingInfo();
        $id     = (int)$request->get('id', false);

        if (!empty($id)) {
            $return = [];

            $data   = $model::select('massage_date', 'massage_time', 'id as booking_info_id', 'id')
                          ->has('therapistWhereShop')
                          ->has('bookingMassages')
                          ->with(['bookingMassages' => function($query) {
                              $query->select('booking_info_id', 'massage_timing_id')
                                    ->with('massageTiming');
                          }])
                          ->where('therapist_id', $id)
                          ->get();

            if (!empty($data) && !$data->isEmpty()) {
                foreach ($data as $record) {
                    if (!empty($record->bookingMassages) && !$record->bookingMassages->isEmpty()) {
                        foreach ($record->bookingMassages as $bookingMassage) {
                            if (empty($bookingMassage->massageTiming)) {
                                continue;
                            }

                            $return[] = [
                                'massage_date'      => $record->massage_date,
                                'massage_time'      => $record->massage_time,
                                'booking_info_id'   => $record->booking_info_id,
                                'time'              => (int)$bookingMassage->massageTiming->time
                            ];
                        }
                    }
                }
            }

            return $this->returns('calender.get.successfully', collect($return));
        }

        return $this->returns();
    }

    public function returns($message = NULL, $with = NULL, $isError = false)
    {
        if ($isError && !empty($message)) {
            $message = !empty($this->errorMsg[$message]) ? __($this->errorMsg[$message]) : __($message);
        } else {
            $message = !empty($this->successMsg[$message]) ? __($this->successMsg[$message]) : __($this->returnNullMsg);
        }

        if (!$isError && !empty($with)) {
            if ($with instanceof Collection && !$with->isEmpty()) {
                return $this->returnSuccess($message, array_values($with->toArray()));
            } else {
                return $this->returnSuccess($message, $with->toArray());
            }
        } elseif ($isError) {
            return $this->returnError($message);
        }

        return $this->returnNull();
    }

    public function updateProfile(int $isFreelancer = Therapist::IS_NOT_FREELANCER, Request $request)
    {
        $model                          = new Therapist();
        $modelTherapistLanguage         = new TherapistLanguage();
        $modelTherapistDocument         = new TherapistDocument();
        $modelTherapistSelectedMassage  = new TherapistSelectedMassage();

        $data   = $request->all();
        $id     = !empty($data['id']) ? (int)$data['id'] : false;
        $inc    = 0;

        $data['dob'] = !empty($data['dob']) ? date('Y-m-d', ($data['dob'] / 1000)) : $data['dob'];
        $data['is_freelancer'] = $isFreelancer;

        if (empty($id)) {
            return $this->returns('profile.update.error', NULL, true);
        }

        if (!$model::find($id)->where('is_freelancer', (string)$isFreelancer)->exists()) {
            return $this->returns('profile.update.error', NULL, true);
        }

        $checks = $model->validator($data, [], [], $id, true);
        if ($checks->fails()) {
            return $this->returns($checks->errors()->first(), NULL, true);
        }

        if (!empty($data['language_spoken'])) {
            if (is_array($data['language_spoken'])) {
                foreach ($data['language_spoken'] as $languageId => $languageSpoken) {
                    $languageData[] = [
                        'type'          => $languageSpoken,
                        'value'         => $modelTherapistLanguage::THEY_CAN_VALUE,
                        'language_id'   => $languageId,
                        'therapist_id'  => $id
                    ];
                }

                $checks = $modelTherapistLanguage->validators($languageData);
                if ($checks->fails()) {
                    return $this->returns($checks->errors()->first(), NULL, true);
                }
            }
        }

        /* For document uploads. */
        $documents    = array_keys($data);
        $documentData = [];

        $checkDocument = function($request, $key, $format, $inc, $type) use(&$documentData) {
            $getDocument = $this->getDocumentFromRequest($request, $key, $format, $inc, $type);

            if (!empty($getDocument['error'])) {
                return $this->returns($getDocument['error'], NULL, true);
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
                        return $this->returns($checkDocumentError, NULL, true);
                    }

                    break;
                case 'document_id_passport_back':
                    $key = 'document_id_passport_back';

                    $checkDocumentError = $checkDocument($request, $key, 'jpeg,png,jpg', $inc, $modelTherapistDocument::TYPE_IDENTITY_PROOF_BACK);

                    if ($checkDocumentError) {
                        return $this->returns($checkDocumentError, NULL, true);
                    }

                    break;
                case 'document_insurance':
                    $key = 'document_insurance';

                    $checkDocumentError = $checkDocument($request, $key, 'jpeg,png,jpg,pdf', $inc, $modelTherapistDocument::TYPE_INSURANCE);

                    if ($checkDocumentError) {
                        return $this->returns($checkDocumentError, NULL, true);
                    }

                    break;
                case 'document_freelancer_financial_document':
                    $key = 'document_freelancer_financial_document';

                    $checkDocumentError = $checkDocument($request, $key, 'jpeg,png,jpg,pdf', $inc, $modelTherapistDocument::TYPE_FREELANCER_FINANCIAL_DOCUMENT);

                    if ($checkDocumentError) {
                        return $this->returns($checkDocumentError, NULL, true);
                    }

                    break;
                case 'document_certificates':
                    $key = 'document_certificates';

                    $checkDocumentError = $checkDocument($request, $key, 'jpeg,png,jpg,pdf', $inc, $modelTherapistDocument::TYPE_CERTIFICATES);

                    if ($checkDocumentError) {
                        return $this->returns($checkDocumentError, NULL, true);
                    }

                    break;
                case 'document_cv':
                    $key = 'document_cv';

                    $checkDocumentError = $checkDocument($request, $key, 'pdf,doc,docx', $inc, $modelTherapistDocument::TYPE_CV);

                    if ($checkDocumentError) {
                        return $this->returns($checkDocumentError, NULL, true);
                    }

                    break;
                case 'document_reference_letter':
                    $key = 'document_reference_letter';

                    $checkDocumentError = $checkDocument($request, $key, 'jpeg,png,jpg,pdf,doc,docx', $inc, $modelTherapistDocument::TYPE_REFERENCE_LATTER);

                    if ($checkDocumentError) {
                        return $this->returns($checkDocumentError, NULL, true);
                    }

                    break;
                case 'document_others':
                    $key = 'document_others';

                    $checkDocumentError = $checkDocument($request, $key, 'jpeg,png,jpg,pdf,doc,docx', $inc, $modelTherapistDocument::TYPE_OTHERS);

                    if ($checkDocumentError) {
                        return $this->returns($checkDocumentError, NULL, true);
                    }

                    break;
                case 'document_personal_experience':
                    $key = 'document_personal_experience';

                    $checkDocumentError = $checkDocument($request, $key, 'jpeg,png,jpg,pdf', $inc, $modelTherapistDocument::PERSONAL_EXPERIENCES);

                    if ($checkDocumentError) {
                        return $this->returns($checkDocumentError, NULL, true);
                    }

                    break;
                default:
                    break;
            }
        }

        $massageData = [];
        if (!empty($data['my_services']['massages'])) {
            foreach ((array)$data['my_services']['massages'] as $massageId) {
                $massageData[] = [
                    'massage_id'    => $massageId,
                    'therapist_id'  => $id
                ];
            }

            $checks = $modelTherapistSelectedMassage->validators($massageData);
            if ($checks->fails()) {
                return $this->returns($checks->errors()->first(), NULL, true);
            }
        }

        // Insert therapist data.
        $model::find($id)->update($data);

        // Insert language spoken data.
        if (!empty($languageData)) {
            foreach ($languageData as $language) {
                $modelTherapistLanguage::updateOrCreate(['language_id' => $language['language_id'], 'therapist_id' => $language['therapist_id']], $language);
            }
        }

        // Store documents.
        if (!empty($documentData)) {
            foreach ($documentData as $document) {
                if (empty($document['file_name'])) {
                    continue;
                }

                $fileName  = $document['file_name'];

                $storeFile = $document[$document['key']]->storeAs($modelTherapistDocument->directory, $fileName);

                if ($storeFile) {
                    if (in_array($document['type'], [$modelTherapistDocument::TYPE_CERTIFICATES, $modelTherapistDocument::TYPE_OTHERS, $modelTherapistDocument::PERSONAL_EXPERIENCES])) {
                        $modelTherapistDocument::create($document);
                    } else {
                        $modelTherapistDocument::updateOrCreate(['therapist_id' => $id, 'type' => $document['type']], $document);
                    }
                }
            }

            // Check all documents uploaded.
            if ($this->checkAllDocumentsUploaded($id)) {
                $model::isDocumentVerified($id, '1');
            }
        }

        if (!empty($massageData)) {
            foreach ($massageData as $massage) {
                $modelTherapistSelectedMassage::updateOrCreate(['massage_id' => $massage['massage_id'], 'therapist_id' => $massage['therapist_id']], $massage);
            }
        }

        $find = $model::where('id', $id)->first();

        return $this->returns('profile.update.successfully', $find);
    }

    public function getDocumentFromRequest(Request $request, string $key, string $formats = 'jpeg,png,jpg', int &$inc, string $type) : Array
    {
        $data = $request->all();

        $id   = !empty($data['id']) ? (int)$data['id'] : false;

        $documentData = [];

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
                foreach ($data[$key] as $document) {
                    $documentData[$inc] = $createData($document, $key, $formats, $inc, $id, $type);
                }
            } elseif ($data[$key] instanceof UploadedFile) {
                $documentData[$inc] = $createData($data[$key], $key, $formats, $inc, $id, $type);
            }
        }

        return $documentData;
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
