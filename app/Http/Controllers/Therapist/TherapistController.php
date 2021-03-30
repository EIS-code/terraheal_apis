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
use App\Language;
use App\Country;
use App\City;
use App\TherapistUserRating;
use App\TherapistComplaint;
use App\TherapistSuggestion;
use App\TherapistReview;
use App\BookingMassageStart;
use App\TherapistWorkingSchedule;
use App\TherapistWorkingScheduleTime;
use App\TherapistQuitCollaboration;
use App\TherapistSuspendCollaboration;
use App\TherapistExchange;
use DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\UploadedFile;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Storage;
use App\Libraries\serviceHelper;

class TherapistController extends BaseController
{

    public $errorMsg = [
        'loginEmail' => "Please provide email properly.",
        'loginPass'  => "Please provide password properly.",
        'loginBoth'  => "Therapist email or password seems wrong.",
        'notFound' => "Therapist not found.",
        'notFoundBookingMassage' => "Booking massage not found.",
        'notFoundEndTime' => "End time not found.",
        'endTimeIsNotProper' => "End time always greater than start time.",
        'dateRequired' => "Date is required.",
        'somethingWrong' => "Something went wrong."
    ];

    public $successMsg = [
        'login' => "Therapist found successfully !",
        'booking.details.found.successfully' => "Bookings found successfully !",
        'booking.today.found.successfully' => "Today bookings found successfully !",
        'booking.future.found.successfully' => "Future bookings found successfully !",
        'booking.past.found.successfully' => "Past bookings found successfully !",
        'calender.get.successfully' => "Calender get successfully !",
        'profile.update.successfully' => "Therapist profile updated successfully !",
        'my.working.schedules.successfully' => "Therapist working schedules get successfully !",
        'therapist.information.successfully' => "Therapist informations get successfully !",
        'therapist.user.rating' => "User rating given successfully !",
        'therapist.suggestion' => "Suggestion saved successfully !",
        'therapist.complaint' => "Complaint registered successfully !",
        'therapist.ratings' => "Therapist ratings get successfully !",
        'booking.start' => "Massage started successfully !",
        'other.therapist.found' => 'Other therapist found successfully !',
        'my.availability.found' => 'My availability found successfully !',
        'therapist.absent.successfully' => 'Therapist absent successfully !',
        'therapist.quit.collaboration' => 'Quit collaboration submitted successfully !',
        'therapist.suspend.collaboration' => 'Suspend collaboration submitted successfully !',
        'services.found.successfully' => 'services found successfully',
        'no.data.found' => 'No data found',
        'therapist.data.found' => 'Therapist data found successfully',
        'therapist.exchange.shift' => 'Therapist exchange with others request sent successfully !',
        'my.missing.days.successfully' => 'My missing days found successfully !',
        'therapist.languages' => 'Languages found successfully !',
        'therapist.countries' => 'Countries found successfully !',
        'therapist.cities' => 'Cities found successfully !'
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

    public function getBookingDetails(Request $request)
    {
        $bookingModel = new Booking();

        $data = $bookingModel->getGlobalQuery($request);

        if (!empty($data)) {
            return $this->returnSuccess(__($this->successMsg['booking.details.found.successfully']), $data);
        }

        return $this->returnNull();
    }

    public function filter(Collection &$return)
    {
        $returnData = [];

        if (!empty($return) && !$return->isEmpty()) {
            $increments = 0;

            /*$return->map(function(&$data, $index) use(&$return, &$increments) {
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
            });*/

            $return->map(function($data, $index) use(&$returnData, &$increments) {
                if (!empty($data->bookingInfoWithFilters) && !$data->bookingInfoWithFilters->isEmpty()) {
                    foreach ($data->bookingInfoWithFilters as $bookingInfo) {
                        if (!empty($bookingInfo->bookingMassages) && !$bookingInfo->bookingMassages->isEmpty()) {
                            foreach ($bookingInfo->bookingMassages as $bookingMassage) {
                                if (empty($bookingInfo->userPeople)) {
                                    continue;
                                }

                                $returnData[$increments]['booking_id']           = $data->id;
                                $returnData[$increments]['booking_type']         = $data->booking_type;
                                $returnData[$increments]['special_notes']        = $data->special_notes;
                                $returnData[$increments]['bring_table_futon']    = $data->bring_table_futon;
                                $returnData[$increments]['table_futon_quantity'] = $data->table_futon_quantity;
                                $returnData[$increments]['session_id']           = $data->session_id;
                                $returnData[$increments]['copy_with_id']         = $data->copy_with_id;
                                $returnData[$increments]['booking_date_time']    = $data->booking_date_time;
                                $returnData[$increments]['user_id']              = $data->user_id;
                                $returnData[$increments]['shop_id']              = $data->shop_id;
                                $returnData[$increments]['booking_info_id']      = $bookingInfo->booking_info_id;
                                $returnData[$increments]['massage_date']         = $bookingInfo->massage_date;
                                $returnData[$increments]['massage_time']         = $bookingInfo->massage_time;
                                $returnData[$increments]['user_people_id']       = $bookingInfo->user_people_id;
                                $returnData[$increments]['therapist_id']         = $bookingInfo->therapist_id;
                                $returnData[$increments]['user_name']            = $bookingInfo->userPeople->name;
                                $returnData[$increments]['therapist_name']       = $bookingInfo->therapist->fullName;
                                $returnData[$increments]['massage_name']         = $bookingMassage->massageTiming->massage->name;

                                $increments++;
                            }
                        }
                    }
                }
            });

            $returnData = collect($returnData);
        }

        return $returnData;
    }

    public function getTodayBooking(Request $request)
    {
        $bookingModel = new Booking();

        $data = $bookingModel->with('bookingInfoWithFilters')->filterDatas()->get();

        $data = $this->filter($data);

        return $this->returns('booking.today.found.successfully', $data);
    }

    public function getFutureBooking(Request $request)
    {
        $bookingModel = new Booking();

        $data = $bookingModel->with('bookingInfoWithFilters')->filterDatas()->get();

        $data = $this->filter($data);

        return $this->returns('booking.future.found.successfully', $data);
    }

    public function getPastBooking(Request $request)
    {
        $bookingModel = new Booking();

        $data = $bookingModel->with('bookingInfoWithFilters')->filterDatas()->get();

        $data = $this->filter($data);

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

        if (!empty($data['dob'])) {
            $data['dob'] = date('Y-m-d', ($data['dob'] / 1000));
        } else {
            unset($data['dob']);
        }

        $data['is_freelancer'] = $isFreelancer;

        if (empty($id)) {
            return $this->returns('notFound', NULL, true);
        }

        if (!$model::find($id)->where('is_freelancer', (string)$isFreelancer)->exists()) {
            return $this->returns('notFound', NULL, true);
        }

        $checks = $model->validator($data, [], [], $id, true);
        if ($checks->fails()) {
            return $this->returns($checks->errors()->first(), NULL, true);
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
                    return $this->returns($checks->errors()->first(), NULL, true);
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

        /* For profile Image */
        if (!empty($data['profile_photo']) && $data['profile_photo'] instanceof UploadedFile) {
            $checkImage = $model->validateProfilePhoto($data);

            if ($checkImage->fails()) {
                unset($data['profile_photo']);

                return $this->returns($checkImage->errors()->first(), NULL, true);
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

        return $this->returns('profile.update.successfully', $this->getGlobalResponse($isFreelancer, $request, false));
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

    /* My working schedules by bookings. */
    public function myWorkingSchedulesByBookings(Request $request)
    {
        $model              = new Therapist();
        $modelBookingInfo   = new BookingInfo();
        $data               = $request->all();
        $id                 = !empty($data['id']) ? (int)$data['id'] : false;

        if (empty($id)) {
            return $this->returns('notFound', NULL, true);
        }

        $find = $model::find($id);

        if (empty($find)) {
            return $this->returns('notFound', NULL, true);
        }

        $return       = [];
        $currentDate  = !empty($data['date']) ? $data['date'] : NULL;
        // date('m')
        $currentMonth = !empty($currentDate) ? Carbon::createFromTimestampMs($currentDate)->firstOfMonth() : new Carbon('first day of this month');
        $currentMonth = !empty($currentMonth) ? $currentMonth : new Carbon('first day of this month');
        $carbonClone  = clone $currentMonth;

        $firstDayOfCurrentMonth = $currentMonth->firstOfMonth();
        $lastDayOfCurrentMonth  = $carbonClone->lastOfMonth();
        $totalDaysInMonth       = CarbonPeriod::create($firstDayOfCurrentMonth, $lastDayOfCurrentMonth);

        if (!empty($totalDaysInMonth)) {
            // Get data between two dates (whole month).
            $getData = $modelBookingInfo::whereBetween('massage_date', [$firstDayOfCurrentMonth, $lastDayOfCurrentMonth])->get();

            foreach ($totalDaysInMonth as $date) {
                if (!empty($getData) && !$getData->isEmpty()) {
                    $date   = strtotime($date->format('Y-m-d')) * 1000;
                    $isDone = [];

                    foreach ($getData as $bookingInfo) {
                        if ($bookingInfo->massage_date === $date) {
                            $isDone[] = $bookingInfo;
                        }
                    }

                    if (!empty($isDone)) {
                        foreach ($isDone as $done) {
                            if ($done->is_done == $modelBookingInfo::IS_NOT_DONE) {
                                $return[$date] = [
                                    'booking_id'        => $done->booking_id,
                                    'booking_info_id'   => $done->id,
                                    'massage_date'      => $done->massage_date,
                                    'status'            => false,
                                    'date'              => $date
                                ];

                                break;
                            } else {
                                $return[$date] = [
                                    'booking_id'        => $done->booking_id,
                                    'booking_info_id'   => $done->id,
                                    'massage_date'      => $done->massage_date,
                                    'status'            => true,
                                    'date'              => $date
                                ];
                            }
                        }
                    } else {
                        $return[$date] = [
                            'booking_id'        => NULL,
                            'booking_info_id'   => NULL,
                            'massage_date'      => NULL,
                            'status'            => false,
                            'date'              => $date
                        ];
                    }
                } else {
                    foreach ($totalDaysInMonth as $date) {
                        $date = strtotime($date->format('Y-m-d')) * 1000;

                        $return[$date] = [
                            'booking_id'        => NULL,
                            'booking_info_id'   => NULL,
                            'massage_date'      => NULL,
                            'status'            => true,
                            'date'              => $date
                        ];
                    }
                }
            }
        }

        return $this->returns('my.working.schedules.successfully', collect($return));
    }

    /* My working schedules as Shop defined. */
    public function myWorkingSchedules(Request $request)
    {
        $now  = Carbon::now()->timestamp * 1000;
        $date = Carbon::createFromTimestampMs($request->get('date', $now));
        $id   = $request->get('id', false);

        $data = TherapistWorkingSchedule::getScheduleByMonth($id, $date->format('Y-m-d'));

        return $this->returns('my.working.schedules.successfully', $data);
    }

    public function getGlobalResponse(int $isFreelancer = Therapist::IS_NOT_FREELANCER, Request $request, $returnResponse = true)
    {
        $data = Therapist::getGlobalQuery($isFreelancer, $request);

        return $returnResponse ? $this->returns('therapist.information.successfully', $data) : $data;
    }

    public function rateUser(Request $request)
    {
        $model      = new TherapistUserRating();
        $data       = $request->all();
        $id         = !empty($data['id']) ? $data['id'] : false;
        $isCreate   = collect();

        $data['therapist_id'] = $id;

        $data['type']         = !empty($data['rating']) && is_array($data['rating']) ? array_keys($data['rating']) : [];

        $checks = $model->validators($data);
        if ($checks->fails()) {
            return $this->returns($checks->errors()->first(), NULL, true);
        }

        foreach ($data['type'] as $index => $type) {
            if (!empty($data['rating'][$type])) {
                $create[$index] = [
                    'rating'        => $data['rating'][$type],
                    'type'          => $type,
                    'user_id'       => $data['user_id'],
                    'therapist_id'  => $id
                ];

                $isCreate->push(collect($model::updateOrCreate($create[$index], $create[$index])));
            }
        }

        return $this->returns('therapist.user.rating', $isCreate);
    }

    public function suggestion(Request $request)
    {
        $model                  = new TherapistSuggestion();
        $data                   = $request->all();
        $data['therapist_id']   = $request->get('id', false);

        $checks = $model->validator($data);
        if ($checks->fails()) {
            return $this->returns($checks->errors()->first(), NULL, true);
        }

        $create = $model::create($data);

        return $this->returns('therapist.suggestion', $create);
    }

    public function complaint(Request $request)
    {
        $model                  = new TherapistComplaint();
        $data                   = $request->all();
        $data['therapist_id']   = $request->get('id', false);

        $checks = $model->validator($data);
        if ($checks->fails()) {
            return $this->returns($checks->errors()->first(), NULL, true);
        }

        $create = $model::create($data);

        return $this->returns('therapist.complaint', $create);
    }

    public function myRatings(Request $request)
    {
        $model = new TherapistReview();
        $id    = $request->get('id', false);

        if (empty($id)) {
            return $this->returns('notFound', NULL, true);
        }

        $data = $model::getAverageRatings($id);

        return $this->returns('therapist.ratings', $data);
    }

    public function startMassageTime(Request $request)
    {
        $model              = new BookingMassageStart();
        $data               = $request->all();
        $bookingMassageId   = $request->get('booking_massage_id', false);

        if (empty($bookingMassageId)) {
            return $this->returns('notFoundBookingMassage', NULL, true);
        }

        $data['actual_total_time']  = BookingMassage::getMassageTime($bookingMassageId);

        $data['start_time']         = !empty($data['start_time']) ? Carbon::createFromTimestampMs($data['start_time']) : false;
        $startTime                  = clone $data['start_time'];
        $data['end_time']           = !empty($startTime) ? $startTime->addMinutes($data['actual_total_time'])->format('H:i:s') : false;
        $data['start_time']         = !empty($data['start_time']) ? $data['start_time']->format('H:i:s') : false;

        $checks = $model->validator($data);
        if ($checks->fails()) {
            return $this->returns($checks->errors()->first(), NULL, true);
        }

        $create = $model::updateOrCreate(['booking_massage_id' => $bookingMassageId], $data);

        return $this->returns('booking.start', $create);
    }

    public function endMassageTime(Request $request)
    {
        $model              = new BookingMassageStart();
        $data               = $request->all();
        $bookingMassageId   = $request->get('booking_massage_id', false);

        $currentTime        = Carbon::now();

        $data['end_time']   = !empty($data['end_time']) ? Carbon::createFromTimestampMs($data['end_time'])->format('H:i:s') : false;

        if (empty($data['end_time'])) {
            return $this->returns('notFoundEndTime', NULL, true);
        }

        $find = $model::where('booking_massage_id', $bookingMassageId)->first();

        if (empty($find)) {
            return $this->returns('notFoundBookingMassage', NULL, true);
        }

        if ($find->start_time > $data['end_time']) {
            return $this->returns('endTimeIsNotProper', NULL, true);
        }

        $data['taken_total_time'] = (new Carbon($find->start_time))->diffInMinutes($currentTime);

        $find->end_time         = $data['end_time'];

        $find->taken_total_time = $data['taken_total_time'];

        $find->update();

        return $this->returns('booking.start', $find);
    }

    public function getAllServices(Request $request)
    {
        $services = serviceHelper::getAllService($request);

        if (count($services) > 0) {
            return $this->returns('services.found.successfully', $services);
        } else {
            return $this->returns('no.data.found');
        }
    }

    public function getOthers(Request $request)
    {
        $id     = $request->get('id', false);
        $name   = $request->get('name', null);
        $model  = new Therapist();

        if ($id) {
            $data = $model::where('id', '!=', $id);

            if (!empty($name)) {
                $data->where(function($query) use($name) {
                    $query->where('name', $name)
                          ->orWhere('surname', $name)
                          ->orWhereRaw("CONCAT(`name`, ' ', `surname`) LIKE '{$name}'");
                });
            }

            $data = $data->get();
        }

        if (!empty($data) && !$data->isEmpty()) {
            $data->each->append('massage_count');
            $data->each->append('therapy_count');

            return $this->returns('other.therapist.found', $data);
        } else {
            return $this->returns('no.data.found');
        }
    }

    public function myAvailabilities(Request $request)
    {
        $id    = $request->get('id', false);
        $date  = $request->get('date', NULL);
        $model = new TherapistWorkingSchedule();

        $data = $model::getAvailabilities($id, $date);

        if (!empty($data)) {
            return $this->returns('my.availability.found', $data);
        } else {
            return $this->returns('no.data.found');
        }
    }

    public function myFreeSpots(Request $request)
    {
        $id    = $request->get('id', false);
        $now   = Carbon::now();
        $date  = $request->get('date', NULL);
        $date  = Carbon::createFromTimestampMs($date);
        $date  = strtotime($date) > 0 ? $date->format('Y-m-d') : $now->format('Y-m-d');

        $availableTimes = $model::getAvailabilities($id, $date);
    }

    public function absent(Request $request)
    {
        $id             = $request->get('id', false);
        $date           = $request->get('date', NULL);
        $date           = $date > 0 ? Carbon::createFromTimestampMs($date)->format('Y-m-d') : NULL;
        $absentReason   = $request->get('absent_reason', NULL);
        $model          = new TherapistWorkingSchedule();

        if (empty($date)) {
            return $this->returns('dateRequired', NULL, true);
        }

        if ($id && !empty($date)) {
            $row = $model->where('therapist_id', $id)->whereDate('date', $date)->update(['is_absent' => $model::ABSENT, 'is_working' => $model::NOT_WORKING, 'absent_reason' => $absentReason]);

            if ($row) {
                return $this->returns('therapist.absent.successfully', collect([]));
            }
        }

        return $this->returns('no.data.found');
    }

    public function quitCollaboration(Request $request)
    {
        $id     = $request->get('id', false);
        $reason = $request->get('reason', NULL);
        $data   = ['reason' => $reason, 'therapist_id' => $id];
        $model  = new TherapistQuitCollaboration();

        $checks = $model->validator($data);
        if ($checks->fails()) {
            return $this->returns($checks->errors()->first(), NULL, true);
        }

        $create = $model::create(['reason' => $reason, 'therapist_id' => $id]);

        if ($create) {
            return $this->returns('therapist.quit.collaboration', $create);
        }

        return $this->returns('somethingWrong', null, true);
    }

    public function suspendCollaboration(Request $request)
    {
        $id     = $request->get('id', false);
        $reason = $request->get('reason', NULL);
        $data   = ['reason' => $reason, 'therapist_id' => $id];
        $model  = new TherapistSuspendCollaboration();

        $checks = $model->validator($data);
        if ($checks->fails()) {
            return $this->returns($checks->errors()->first(), NULL, true);
        }

        $create = $model::create(['reason' => $reason, 'therapist_id' => $id]);

        if ($create) {
            return $this->returns('therapist.suspend.collaboration', $create);
        }

        return $this->returns('somethingWrong', null, true);
    }
    
    public function getTherapists(Request $request)
    {
        $query = DB::table('booking_massages')
                        ->join('booking_infos', 'booking_infos.id', '=', 'booking_massages.booking_info_id')
                        ->join('bookings', 'bookings.id', '=', 'booking_infos.booking_id')
                        ->join('therapists', 'therapists.id', '=', 'booking_infos.therapist_id')
                        ->select('booking_infos.massage_time as massageStartTime','booking_infos.massage_date as massageDate',
                                DB::raw('CONCAT(COALESCE(therapists.name,"")," ",COALESCE(therapists.surname,"")) AS therapistName'))
                        ->where('bookings.shop_id',$request->shop_id);
        
        if (isset($request->filter) && $request->filter == 1) {
            $therapists = $query->where('booking_infos.massage_date', Carbon::now()->format('Y-m-d'))->get();
        } else {
            $therapists = $query->get();
        }
        return $this->returnSuccess(__($this->successMsg['therapist.data.found']), $therapists);
    }

    public function exchangeWithOthers(Request $request)
    {
        $id     = $request->get('id', false);
        $date   = $request->get('date', false);
        $model  = new TherapistExchange();

        if (!empty($date) && $date > 0) {
            $date = Carbon::createFromTimestampMs($date)->format('Y-m-d H:i:s');

            $data = ['date' => $date, 'is_approved' => $model::IS_NOT_APPROVED, 'therapist_id' => $id];

            $checks = $model->validator($data);
            if ($checks->fails()) {
                return $this->returns($checks->errors()->first(), NULL, true);
            }

            $create = $model::updateOrCreate($data, $data);

            if ($create) {
                return $this->returns('therapist.exchange.shift', $create);
            }
        }

        return $this->returns('somethingWrong', null, true);
    }

    public function myMissingDays(Request $request)
    {
        $id     = $request->get('id', false);
        $now    = Carbon::now()->timestamp * 1000;
        $date   = Carbon::createFromTimestampMs($request->get('date', $now));

        $data   = TherapistWorkingSchedule::getMissingDays($id, $date->format('Y-m-d'));

        return $this->returns('my.missing.days.successfully', $data);
    }
    
    public function getLanguages() {
        
        $languages = Language::all();
        return $this->returnSuccess(__($this->successMsg['therapist.languages']), $languages);
    }
    
    public function getCountries() {
        
        $countries = Country::all();
        return $this->returnSuccess(__($this->successMsg['therapist.countries']), $countries);
    }
    
    public function getCities(Request $request) {
        
        $cities = City::whereHas('province', function($q) use($request) {
                    $q->where('country_id', $request->country_id);
                })->get();
        return $this->returnSuccess(__($this->successMsg['therapist.cities']), $cities);
    }
}
