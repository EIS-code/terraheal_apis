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
use DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\UploadedFile;

class TherapistController extends BaseController
{

    public $errorMsg = [
        'loginEmail' => "Please provide email properly.",
        'loginPass'  => "Please provide password properly.",
        'loginBoth'  => "Therapist email or password seems wrong."
    ];

    public $successMsg = [
        'login' => "Therapist found successfully !",
        'booking.details.found.successfully' => "Bookings found successfully !",
        'booking.today.found.successfully' => "Today bookings found successfully !",
        'booking.future.found.successfully' => "Future bookings found successfully !",
        'booking.past.found.successfully' => "Past bookings found successfully !",
        'calender.get.successfully' => "Calender get successfully !"
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
            $message = __($message);
        } else {
            $message = !empty($this->successMsg[$message]) ? __($this->successMsg[$message]) : __($this->returnNullMsg);
        }

        if (!$isError && !empty($with) && !$with->isEmpty()) {
            return $this->returnSuccess($message, array_values($with->toArray()));
        } elseif ($isError) {
            return $this->returnError($message);
        }

        return $this->returnNull();
    }

    public function updateProfile(int $isFreelancer = Therapist::IS_NOT_FREELANCER, Request $request)
    {
        $model                  = new Therapist();
        $modelTherapistLanguage = new TherapistLanguage();
        $modelTherapistDocument = new TherapistDocument();

        $data   = $request->all();
        $id     = !empty($data['id']) ? (int)$data['id'] : false;
        $inc    = 0;

        $data['dob'] = !empty($data['dob']) ? date('Y-m-d', ($data['dob'] / 1000)) : $data['dob'];
        $data['is_freelancer'] = $isFreelancer;

        $checks = $model->validator($data, [], [], $id, true);
        if ($checks->fails()) {
            return $this->returns($checks->errors()->first(), NULL, true);
        }

        if (!empty($data['language_spoken'])) {
            if (is_array($data['language_spoken'])) {
                foreach ($data['language_spoken'] as $languageId => $languageSpoken) {
                    $languageData[] = [
                        'type'          => $languageSpoken,
                        'value'         => TherapistLanguage::THEY_CAN_VALUE,
                        'language_id'   => $languageId,
                        'therapist_id'  => $id
                    ];
                }
            }
        }

        $checks = $modelTherapistLanguage->validators($languageData);
        if ($checks->fails()) {
            return $this->returns($checks->errors()->first(), NULL, true);
        }

        /* For document uploads. */
        $documents    = array_keys($data);
        $documentData = [];

        foreach ($documents as $document) {
            switch ($document) {
                case 'document_id_passport_front':
                    $key      = 'document_id_passport_front';
                    $file     = $data[$key];
                    $formats  = 'jpeg,png,jpg';
                    $pathInfo = pathinfo($file->getClientOriginalName());

                    if (!empty($pathInfo['extension'])) {
                        $fileName = $pathInfo['basename'];

                        $documentData[$inc] = [
                            'type'          => $modelTherapistDocument::TYPE_IDENTITY_PROOF_FRONT,
                            'file_name'     => $fileName,
                            'therapist_id'  => $id,
                            $key            => $file
                        ];

                        $checks = $modelTherapistDocument->validator($documentData[$inc], $key, $formats);
                        if ($checks->fails()) {
                            return $this->returns($checks->errors()->first(), NULL, true);
                        }

                        $inc++;
                    }
                case 'document_id_passport_back':
                    $key      = 'document_id_passport_back';
                    $file     = $data[$key];
                    $formats  = 'jpeg,png,jpg';
                    $pathInfo = pathinfo($file->getClientOriginalName());

                    if (!empty($pathInfo['extension'])) {
                        $fileName = $pathInfo['basename'];

                        $documentData[$inc] = [
                            'type'          => $modelTherapistDocument::TYPE_IDENTITY_PROOF_BACK,
                            'file_name'     => $fileName,
                            'therapist_id'  => $id,
                            $key            => $file
                        ];

                        $checks = $modelTherapistDocument->validator($documentData[$inc], $key, $formats);
                        if ($checks->fails()) {
                            return $this->returns($checks->errors()->first(), NULL, true);
                        }

                        $inc++;
                    }
                case 'document_insurance':
                    $key      = 'document_insurance';
                    $file     = $data[$key];
                    $formats  = 'jpeg,png,jpg,pdf';
                    $pathInfo = pathinfo($file->getClientOriginalName());

                    if (!empty($pathInfo['extension'])) {
                        $fileName = $pathInfo['basename'];

                        $documentData[$inc] = [
                            'type'          => $modelTherapistDocument::TYPE_INSURANCE,
                            'file_name'     => $fileName,
                            'therapist_id'  => $id,
                            $key            => $file
                        ];

                        $checks = $modelTherapistDocument->validator($documentData[$inc], $key, $formats);
                        if ($checks->fails()) {
                            return $this->returns($checks->errors()->first(), NULL, true);
                        }

                        $inc++;
                    }
                case 'document_freelancer_financial_document':
                    $key      = 'document_freelancer_financial_document';
                    $file     = $data[$key];
                    $formats  = 'jpeg,png,jpg,pdf';
                    $pathInfo = pathinfo($file->getClientOriginalName());

                    if (!empty($pathInfo['extension'])) {
                        $fileName = $pathInfo['basename'];

                        $documentData[$inc] = [
                            'type'          => $modelTherapistDocument::TYPE_FREELANCER_FINANCIAL_DOCUMENT,
                            'file_name'     => $fileName,
                            'therapist_id'  => $id,
                            $key            => $file
                        ];

                        $checks = $modelTherapistDocument->validator($documentData[$inc], $key, $formats);
                        if ($checks->fails()) {
                            return $this->returns($checks->errors()->first(), NULL, true);
                        }

                        $inc++;
                    }
                case 'document_certificates':
                    $key      = 'document_certificates';
                    $files    = $data[$key];
                    $formats  = 'jpeg,png,jpg,pdf';

                    foreach ($files as $file) {
                        $pathInfo = pathinfo($file->getClientOriginalName());

                        if (!empty($pathInfo['extension'])) {
                            $fileName = $pathInfo['basename'];

                            $documentData[$inc] = [
                                'type'          => $modelTherapistDocument::TYPE_CERTIFICATES,
                                'file_name'     => $fileName,
                                'therapist_id'  => $id,
                                $key            => $file
                            ];

                            $checks = $modelTherapistDocument->validator($documentData[$inc], $key, $formats);
                            if ($checks->fails()) {
                                return $this->returns($checks->errors()->first(), NULL, true);
                            }

                            $inc++;
                        }
                    }
                case 'document_cv':
                    $key      = 'document_cv';
                    $file     = $data[$key];
                    $formats  = 'pdf,doc,docx';
                    $pathInfo = pathinfo($file->getClientOriginalName());

                    if (!empty($pathInfo['extension'])) {
                        $fileName = $pathInfo['basename'];

                        $documentData[$inc] = [
                            'type'          => $modelTherapistDocument::TYPE_CV,
                            'file_name'     => $fileName,
                            'therapist_id'  => $id,
                            $key            => $file
                        ];

                        $checks = $modelTherapistDocument->validator($documentData[$inc], $key, $formats);
                        if ($checks->fails()) {
                            return $this->returns($checks->errors()->first(), NULL, true);
                        }

                        $inc++;
                    }
                case 'document_reference_letter':
                    $key      = 'document_reference_letter';
                    $file     = $data[$key];
                    $formats  = 'jpeg,png,jpg,pdf,doc,docx';
                    $pathInfo = pathinfo($file->getClientOriginalName());

                    if (!empty($pathInfo['extension'])) {
                        $fileName = $pathInfo['basename'];

                        $documentData[$inc] = [
                            'type'          => $modelTherapistDocument::TYPE_REFERENCE_LATTER,
                            'file_name'     => $fileName,
                            'therapist_id'  => $id,
                            $key            => $file
                        ];

                        $checks = $modelTherapistDocument->validator($documentData[$inc], $key, $formats);
                        if ($checks->fails()) {
                            return $this->returns($checks->errors()->first(), NULL, true);
                        }

                        $inc++;
                    }
                case 'document_others':
                    $key      = 'document_others';
                    $file     = $data[$key];
                    $formats  = 'jpeg,png,jpg,pdf,doc,docx';
                    $pathInfo = pathinfo($file->getClientOriginalName());

                    if (!empty($pathInfo['extension'])) {
                        $fileName = $pathInfo['basename'];

                        $documentData[$inc] = [
                            'type'          => $modelTherapistDocument::TYPE_OTHERS,
                            'file_name'     => $fileName,
                            'therapist_id'  => $id,
                            $key            => $file
                        ];

                        $checks = $modelTherapistDocument->validator($documentData[$inc], $key, $formats);
                        if ($checks->fails()) {
                            return $this->returns($checks->errors()->first(), NULL, true);
                        }

                        $inc++;
                    }
                case 'document_personal_experience':
                    $key      = 'document_personal_experience';
                    $files    = $data[$key];
                    $formats  = 'jpeg,png,jpg,pdf';

                    foreach ($files as $file) {
                        $pathInfo = pathinfo($file->getClientOriginalName());

                        if (!empty($pathInfo['extension'])) {
                            $fileName = $pathInfo['basename'];

                            $documentData[$inc] = [
                                'type'          => $modelTherapistDocument::PERSONAL_EXPERIENCE,
                                'file_name'     => $fileName,
                                'therapist_id'  => $id,
                                $key            => $file
                            ];

                            $checks = $modelTherapistDocument->validator($documentData[$inc], $key, $formats);
                            if ($checks->fails()) {
                                return $this->returns($checks->errors()->first(), NULL, true);
                            }

                            $inc++;
                        }
                    }
            }
        }

        dd($documentData);
    }
}
