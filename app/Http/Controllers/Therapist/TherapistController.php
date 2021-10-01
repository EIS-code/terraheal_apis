<?php

namespace App\Http\Controllers\Therapist;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use App\Therapist;
use App\SessionType;
use App\Booking;
use App\BookingInfo;
use App\BookingMassage;
use App\MassagePreferenceOption;
use App\TherapistLanguage;
use App\TherapistDocument;
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
use App\Receptionist;
use App\TherapistWorkingScheduleBreak;
use DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\UploadedFile;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Storage;
use App\User;
use App\Shop;
use App\TherapistEmailOtp;
use App\TherapistShop;
use App\TherapistFreeSlot;
use App\TherapistNews;
use App\ShopShift;
use App\Libraries\CommonHelper;
use App\Manager;
use App\News;
use App\ForgotOtp;

class TherapistController extends BaseController
{

    public $errorMsg = [
        'loginEmail' => "Please provide email properly.",
        'loginPass'  => "Please provide password properly.",
        'loginBoth'  => "Therapist email or password seems wrong.",
        'notFound' => "Therapist not found.",
        'notFoundBookingMassage' => "Booking massage not found.",
        'notFoundEndTime' => "End time not found.",
        'notFoundData' => "No data found.",
        'endTimeIsNotProper' => "End time always greater than start time.",
        'dateRequired' => "Date is required.",
        'somethingWrong' => "Something went wrong.",
        'no.schedule.found' => "No schedule found.",
        'no.document.found' => "No document found.",
        'no.booking.found' => "No booking found.",
        'error.otp' => 'Please provide OTP properly.',
        'error.otp.wrong' => 'OTP seems wrong.',
        'error.otp.already.verified' => 'OTP already verified.',
        'error.therapist.id' => 'Please provide valid therapist id.',
        'error.email.already.verified' => 'This user email already verified with this ',
        'error.email.id' => ' email id.',
        'shift.not.found' => 'Shift not found.',
        'not.belong' => 'This therapist is not belong to this shop.',
        'shift.approved' => 'Shift already approved.',
        'shift.exchanger.error' => 'Your shift is not found, please select proper shift.',
        'shift.receiver.error' => 'Your selected therapist is not available during your shift, please select another therapist shift.',
        'otp.not.found' => 'Otp not found !',
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
        'booking.end' => "Massage ended successfully !",
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
        'therapist.cities' => 'Cities found successfully !',
        'client.data.found' => 'Client data found successfully !',
        'therapist.complaints.suggestions' => 'Therapist complaints and suggestions found successfully !',
        'session.types' => 'Session types found successfully !',
        'therapist.break' => 'Break done successfully !',
        'booking.pending.found.successfully' => 'Pending bookings found successfully !',
        'booking.upcoming.found.successfully' => 'Upcoming bookings found successfully !',
        'therapist.document.delete' => 'Therapist document removed successfully !',
        'success.email.otp.compare' => 'OTP matched successfully !',
        'success.sms.sent' => 'SMS sent successfully !',
        'success.email.sent' => 'Email sent successfully !',        
        'therapist.freeslot' => 'Therapist freeslot added successfully !',
        'all.therapist.shifts' => 'All therapist shifts found successfully !',
        'news.read' => 'News read successfully !',        
        'exchange.list' => 'Therapist exchange shifts list found successfully !',
        'shift.approve' => 'Shift approve successfully !',
        'shift.reject' => 'Shift reject successfully !',
        'observation.add' => 'Observation added successfully !',
        'news.get' => 'News found successfully !',
        'success.otp' => 'Otp sent successfully !',
        'success.reset.password' => 'Password reset successfully !',
        'success.otp.verified' => 'Otp verified successfully !',
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
            $getTherapist = $model->where(['email' => $email, 'is_freelancer' => (string)$isFreelancer, 'active_app' => Therapist::IS_ACTIVE])->first();
            if (!empty($getTherapist) && Hash::check($password, $getTherapist->password)) {

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

        $data = $bookingModel->getGlobalQuery($request)->first();

        if (!empty($data)) {
            return $this->returnSuccess(__($this->successMsg['booking.details.found.successfully']), $data);
        }

        return $this->returnNull();
    }

    public function getBookings(Request $request)
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
                                if (empty($bookingInfo->user)) {
                                    continue;
                                }

                                $returnData[$increments]['booking_id']           = $data->id;
                                $returnData[$increments]['booking_type']         = $data->booking_type;
                                $returnData[$increments]['booking_type_value']   = $data->getAttributes()['booking_type'];
                                $returnData[$increments]['special_notes']        = $data->special_notes;
                                $returnData[$increments]['bring_table_futon']    = $data->bring_table_futon;
                                $returnData[$increments]['table_futon_quantity'] = $data->table_futon_quantity;
                                $returnData[$increments]['session_id']           = $data->session_id;
                                $returnData[$increments]['copy_with_id']         = $data->copy_with_id;
                                $returnData[$increments]['booking_date_time']    = $data->booking_date_time;
                                $returnData[$increments]['user_id']              = $data->user_id;
                                $returnData[$increments]['shop_id']              = $data->shop_id;
                                $returnData[$increments]['booking_info_id']      = $bookingInfo->booking_info_id;
                                $returnData[$increments]['massage_date_time']    = $bookingMassage->massage_date_time;
                                $returnData[$increments]['user_people_id']       = $bookingInfo->user_people_id;
                                $returnData[$increments]['therapist_id']         = $bookingInfo->therapist_id;
                                $returnData[$increments]['user_name']            = $bookingInfo->user->name;
                                $returnData[$increments]['therapist_name']       = $bookingInfo->therapist->fullName;
                                $returnData[$increments]['service_pricing_id']   = $bookingInfo->service_pricing_id;
                                $returnData[$increments]['service_name'] = !empty($bookingMassage->servicePrices->service) ? $bookingMassage->servicePrices->service->english_name : NULL;
                                $returnData[$increments]['service_english_name'] = !empty($bookingMassage->servicePrices->service) ? $bookingMassage->servicePrices->service->english_name : NULL;
                                $returnData[$increments]['service_portugese_name'] = !empty($bookingMassage->servicePrices->service) ? $bookingMassage->servicePrices->service->portugese_name : NULL;
                                $returnData[$increments]['service_id']           = !empty($bookingMassage->servicePrices) ? $bookingMassage->servicePrices->service_id : NULL;
                                $returnData[$increments]['is_done']              = $bookingInfo->is_done;
                                $returnData[$increments]['service_status']       = $bookingMassage->getServiceStatus();
                                $returnData[$increments]['booking_massage_id']   = $bookingMassage->id;

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

        $request->request->add(['bookings_filter' => [$bookingModel::BOOKING_TODAY]]);

        $data = $bookingModel->getGlobalQuery($request);

        return $this->returns('booking.today.found.successfully', $data);
    }

    public function getFutureBooking(Request $request)
    {
        $bookingModel = new Booking();

        $request->request->add(['bookings_filter' => [$bookingModel::BOOKING_FUTURE]]);

        $data = $bookingModel->getGlobalQuery($request);

        return $this->returns('booking.future.found.successfully', $data);
    }

//    public function getPastBooking(Request $request)
//    {
//        $bookingModel = new Booking();
//
//        $data = $bookingModel->with('bookingInfoWithFilters')->filterDatas()->get();
//
//        $data = $this->filter($data);
//
//        return $this->returns('booking.past.found.successfully', $data);
//    }

    public function getPendingBooking(Request $request)
    {
        $bookingModel = new Booking();

        $request->request->add(['bookings_filter' => [$bookingModel::BOOKING_TODAY]]);

        $data = $bookingModel->getGlobalQuery($request);

        return $this->returns('booking.pending.found.successfully', $data);
    }

    public function getUpcomingBooking(Request $request)
    {
        $bookingModel = new Booking();

        $request->request->add(['bookings_filter' => [$bookingModel::BOOKING_FUTURE]]);

        $data = $bookingModel->getGlobalQuery($request);

        return $this->returns('booking.upcoming.found.successfully', $data);
    }

    public function getPastBookings(Request $request)
    {
        $bookingModel = new Booking();

        $request->request->add(['bookings_filter' => [$bookingModel::BOOKING_PAST]]);

        $data = $bookingModel->getGlobalQuery($request);

        return $this->returns('booking.past.found.successfully', $data);
    }

    public function getCalender(Request $request)
    {
        $model  = new BookingInfo();
        $id     = (int)$request->get('id', false);
        $month  = $request->get('date', 0);
        $type   = !empty($request->type) ? $request->type : Booking::BOOKING_TYPE_IMC;

        if (!empty($id)) {
            $return = $model::getCalender($id, $month, $type);

            return $this->returns('calender.get.successfully', $return);
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
        $store = Therapist::updateProfile($isFreelancer, $request);

        if (!empty($store['isError']) && !empty($store['message'])) {
            return $this->returns($store['message'], NULL, true);
        }

        return $this->returns('profile.update.successfully', $this->getGlobalResponse($isFreelancer, $request, false));
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
            $getData = BookingMassage::with('bookingInfo')->whereBetween('massage_date_time', [$firstDayOfCurrentMonth, $lastDayOfCurrentMonth])->get();

            foreach ($totalDaysInMonth as $date) {
                if (!empty($getData) && !$getData->isEmpty()) {
                    $date   = strtotime($date->format('Y-m-d')) * 1000;
                    $isDone = [];

                    foreach ($getData as $bookingMassage) {
                        if ($bookingMassage->massage_date_time === $date) {
                            $isDone[] = $bookingMassage;
                        }
                    }

                    if (!empty($isDone)) {
                        foreach ($isDone as $done) {
                            if ($done->bookingInfo->is_done == $modelBookingInfo::IS_NOT_DONE) {
                                $return[$date] = [
                                    'booking_id'        => $done->bookingInfo->booking_id,
                                    'booking_info_id'   => $done->bookingInfo->id,
                                    'massage_date_time' => $done->massage_date_time,
                                    'status'            => false,
                                    'date'              => $date
                                ];

                                break;
                            } else {
                                $return[$date] = [
                                    'booking_id'        => $done->bookingInfo->booking_id,
                                    'booking_info_id'   => $done->bookingInfo->id,
                                    'massage_date_time' => $done->massage_date_time,
                                    'status'            => true,
                                    'date'              => $date
                                ];
                            }
                        }
                    } else {
                        $return[$date] = [
                            'booking_id'        => NULL,
                            'booking_info_id'   => NULL,
                            'massage_date_time' => NULL,
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
                            'massage_date_time' => NULL,
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

        return $this->returns('my.working.schedules.successfully', collect($data));
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
        $model  = new Therapist();

        $create = $model->serviceStart($request);

        if (!empty($create['isError']) && !empty($create['message'])) {
            return $this->returns($create['message'], NULL, true);
        }

        return $this->returns('booking.start', $create);
    }

    public function endMassageTime(Request $request)
    {
        $model = new Therapist();

        $find  = $model->serviceEnd($request);

        if (!empty($find['isError']) && !empty($find['message'])) {
            return $this->returns($find['message'], NULL, true);
        }

        return $this->returns('booking.end', $find);
    }

    public function getAllServices(Request $request)
    {
        $request->request->add(['type' => $request->service, 'isGetAll' => true]);
        $services = CommonHelper::getAllService($request);

        if (count($services) > 0) {
            return $this->returns('services.found.successfully', collect($services));
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

        if ($data->count()) {
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
        $shiftId        = $request->get('shift_id', NULL);
        $date           = $request->get('date', NULL);
        $date           = $date > 0 ? Carbon::createFromTimestampMs($date)->format('Y-m-d') : NULL;
        $absentReason   = $request->get('absent_reason', NULL);
        $scheduleModel       = new TherapistWorkingSchedule();
        $shiftModel          = new TherapistShift();

        if (empty($date)) {
            return $this->returns('dateRequired', NULL, true);
        }

        if ($id && !empty($date)) {
            $schedule = $scheduleModel->where('therapist_id', $id)->whereDate('date', $date)->first();
            if (!empty($schedule)) {
                $shift = $shiftModel->where(['schedule_id' => $schedule->id, 'shift_id' => $shiftId])->first();
                $row = $shift->update(['is_absent' => $scheduleModel::ABSENT, 'is_working' => $scheduleModel::NOT_WORKING, 'absent_reason' => $absentReason]);

                if ($row) {
                    return $this->returns('therapist.absent.successfully', $shift);
                }
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
        $model = new Therapist();
        $therapists = $model->getTherapist($request);
        return $this->returnSuccess(__($this->successMsg['therapist.data.found']), $therapists);
    }

    public function exchangeWithOthers(Request $request)
    {
        $date   = $request->get('date', false);
        $data   = $request->all();
        $model  = new TherapistExchange();

        if (!empty($date) && $date > 0) {
            $date = Carbon::createFromTimestampMs($date)->format('Y-m-d H:i:s');

            $therapist_shift = TherapistWorkingSchedule::where(['therapist_id' => $data['therapist_id'], 
                'date' => $date, "shift_id" => $data['shift_id'], "shop_id" => $data['shop_id']])->first();

            if(empty($therapist_shift)) {
                return $this->returnError($this->errorMsg['shift.exchanger.error']);
            }

            $check = TherapistShop::where(['therapist_id' => $data['with_therapist_id'], "shop_id" => $data['shop_id']])->first();
            if(empty($check)) {
                return $this->returnError($this->errorMsg['not.belong']);
            }
            $therapist_with_shift = TherapistWorkingSchedule::where(['therapist_id' => $data['with_therapist_id'], 
                'date' => $date, "shift_id" => $data['with_shift_id'], "shop_id" => $data['shop_id']])->first();

            if(!empty($therapist_with_shift)) {
                return $this->returnError($this->errorMsg['shift.receiver.error']);
            }
            
            $data = [
                'date' => $date,
                'therapist_id' => $data['therapist_id'],
                "with_therapist_id" => $data['with_therapist_id'],
                "shift_id" => $data['shift_id'],
                "with_shift_id" => $data['with_shift_id'],
                "shop_id" => $data['shop_id']
            ];

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
    
    public function searchClients(Request $request) {
        
        $search_val = $request->search_val;        
        $clients = User::where(['shop_id' => $request->shop_id, 'is_removed' => User::$notRemoved])
                ->where(function($query) use ($search_val) {
                    $query->where('name', 'like', $search_val .'%')
                    ->orWhere('surname', 'like', $search_val .'%')
                    ->orWhere('email', 'like', $search_val .'%');
                })->get();
                
        return $this->returnSuccess(__($this->successMsg['client.data.found']), $clients);
    }
    
    public function getComplaintsSuggestion(Request $request) {
        $id = $request->get('id', false);

        $complaints     = TherapistComplaint::select(TherapistComplaint::getTableName() . '.id', TherapistComplaint::getTableName() . '.therapist_id', TherapistComplaint::getTableName() . '.receptionist_id', TherapistComplaint::getTableName() . '.complaint as text', Therapist::getTableName() . '.profile_photo as therapist_photo', Receptionist::getTableName() . '.photo as receptionist_photo', DB::raw("CONCAT(" . Therapist::getTableName() . ".name, ' ', " . Therapist::getTableName() . ".surname) as therapist_name"), DB::raw("UNIX_TIMESTAMP(" . TherapistComplaint::getTableName() . ".created_at) * 1000 as created_time"), DB::raw("'complaint' as type"), Receptionist::getTableName() . '.name as receptionist_name')
                                            ->leftJoin(Therapist::getTableName(), TherapistComplaint::getTableName() . '.therapist_id', '=', Therapist::getTableName() . '.id')
                                            ->leftJoin(Receptionist::getTableName(), TherapistComplaint::getTableName() . '.receptionist_id', '=', Receptionist::getTableName() . '.id')
                                            ->orderBy(TherapistComplaint::getTableName() . '.created_at', 'DESC');

        $suggestions    = TherapistSuggestion::select(TherapistSuggestion::getTableName() . '.id', TherapistSuggestion::getTableName() . '.therapist_id', TherapistSuggestion::getTableName() . '.receptionist_id', TherapistSuggestion::getTableName() . '.suggestion as text', Therapist::getTableName() . '.profile_photo as therapist_photo', Receptionist::getTableName() . '.photo as receptionist_photo', DB::raw("CONCAT(" . Therapist::getTableName() . ".name, ' ', " . Therapist::getTableName() . ".surname) as therapist_name"), DB::raw("UNIX_TIMESTAMP(" . TherapistSuggestion::getTableName() .".created_at) * 1000 as created_time"), DB::raw("'suggestion' as type"), Receptionist::getTableName() . '.name as receptionist_name')
                                            ->leftJoin(Therapist::getTableName(), TherapistSuggestion::getTableName() . '.therapist_id', '=', Therapist::getTableName() . '.id')
                                            ->leftJoin(Receptionist::getTableName(), TherapistSuggestion::getTableName() . '.receptionist_id', '=', Receptionist::getTableName() . '.id')
                                            ->orderBy(TherapistSuggestion::getTableName() . '.created_at', 'DESC');

        $data           = $complaints->union($suggestions)->get();

        if (!empty($data) && !$data->isEmpty()) {
            $therapistModel     = new Therapist();
            $receptionistModel  = new Receptionist();

            $data->each(function($record) use($therapistModel, $receptionistModel) {
                if (!empty($record->therapist_photo)) {
                    $record->therapist_photo = $therapistModel->getProfilePhotoAttribute($record->therapist_photo);
                }

                if (!empty($record->receptionist_photo)) {
                    $record->receptionist_photo = $receptionistModel->getPhotoAttribute($record->receptionist_photo);
                }
            });

            return $this->returns('therapist.complaints.suggestions', $data);
        }

        return $this->returns('notFoundData', NULL, true);
    }
    
    public function getSessionTypes() {
        
        $sessionTypes = SessionType::all()->groupBy('booking_type');
                
        return $this->returnSuccess(__($this->successMsg['session.types']), $sessionTypes);
    }

    public function takeBreaks(Request $request)
    {
        $id = $request->get('id', false);

        if ($id) {
            $date      = new Carbon($request->get('date', NULL) / 1000);
            $from      = new Carbon($request->get('from', NULL) / 1000);
            $to        = new Carbon($request->get('to', NULL) / 1000);
            
            $scheduleData = [
                'date' => $date,       
                'therapist_id' => $id,
                'shift_id' => $request->shift_id,
                'shop_id' => $request->shop_id,
                'is_working' => TherapistWorkingSchedule::WORKING,
                'is_exchange' => TherapistWorkingSchedule::NOT_EXCHANGE
            ];
            
            $schedule = TherapistWorkingSchedule::where($scheduleData)->first();
            if(empty($schedule)) {
                return $this->returnError(__($this->errorMsg['no.schedule.found']));
            }

            $save = TherapistWorkingScheduleBreak::takeBreaks($from->format('H:i'), $to->format('H:i'), $schedule);

            if (!empty($save['isError']) && !empty($save['msg'])) {
                return $this->returns($save['msg'], NULL, true);
            }

            return $this->returns('therapist.break', $save);
        }

        return $this->returns('no.schedule.found', NULL, true);
    }

    public function removeDocument(Request $request)
    {
        $documentId = (int)$request->get('document_id', false);

        $model      = new TherapistDocument();

        if (!empty($documentId)) {
            $find = $model::find($documentId);

            if (!empty($find)) {
                // $documentName = $find->getAttributes()['file_name'];

                $remove       = $find->delete();

                /*if ($remove && !empty($documentName)) {
                    $model->removeDocument($documentName);
                }*/

                return $this->returns('therapist.document.delete', collect([]));
            }
        }

        return $this->returns('no.document.found', NULL, true);
    }
    
    public function verifyEmail(Request $request)
    {
        $data           = $request->all();
        $model          = new Therapist();
        $modelEmailOtp  = new TherapistEmailOtp();

        $id      = (!empty($data['therapist_id'])) ? $data['therapist_id'] : 0;
        $getTherapist = $model->find($id);

        if (!empty($getTherapist)) {
            $emailId = (!empty($data['email'])) ? $data['email'] : NULL;

            // Validate
            $data = [
                'therapist_id' => $id,
                'otp'          => 1434,
                'email'        => $emailId,
                'is_send'      => '0'
            ];

            $validator = $modelEmailOtp->validate($data);
            if ($validator['is_validate'] == '0') {
                return $this->returns($validator['msg'], NULL, true);
            }

            if ($emailId == $getTherapist->email && $getTherapist->is_email_verified == '1') {
                $this->errorMsg['error.email.already.verified'] = $this->errorMsg['error.email.already.verified'] . $emailId . $this->errorMsg['error.email.id'];

                return $this->returns('error.email.already.verified', NULL, true);
            }

            $sendOtp         = $this->sendOtp($emailId);
            $data['otp']     = NULL;
            $data['is_send'] = '0';

            if ($this->getJsonResponseCode($sendOtp) == '200') {
                $data['is_send']     = '1';
                $data['is_verified'] = '0';
                $data['otp']         = $this->getJsonResponseOtp($sendOtp);
            } else {
                return $this->returns($this->getJsonResponseMsg($sendOtp), NULL, true);
            }

            $getData = $modelEmailOtp->where(['therapist_id' => $id])->get();

            if (!empty($getData) && !$getData->isEmpty()) {
                $updateOtp = $modelEmailOtp->updateOtp($id, $data);
                
                if (!empty($updateOtp['isError']) && !empty($updateOtp['message'])) {
                    return $this->returns($updateOtp['message'], NULL, true);
                }
            } else {
                $create = $modelEmailOtp->create($data);

                if (!$create) {
                    return $this->returns('error.something', NULL, true);
                }
            }
        } else {
            return $this->returns('error.therapist.id', NULL, true);
        }

        return $this->returns('success.email.sent', collect([]));
    }

    public function compareOtpEmail(Request $request)
    {
        $data       = $request->all();
        $model      = new TherapistEmailOtp();
        $modelUser  = new Therapist();

        $therapistId = (!empty($data['therapist_id'])) ? $data['therapist_id'] : 0;
        $otp    = (!empty($data['otp'])) ? $data['otp'] : NULL;

        if (empty($otp)) {
            return $this->returns('error.otp', NULL, true);
        }

        if (strtolower(env('APP_ENV') != 'live') && $otp == '1234') {
            $getTherapist = $model->where(['therapist_id' => $therapistId])->get();
        } else {
            $getTherapist = $model->where(['therapist_id' => $therapistId, 'otp' => $otp])->get();
        }

        if (!empty($getTherapist) && !$getTherapist->isEmpty()) {
            $getTherapist = $getTherapist->first();

            if ($getTherapist->is_verified == '1') {
                return $this->returns('error.otp.already.verified', NULL, true);
            } else {
                $modelUser->where(['id' => $therapistId])->update(['email' => $getTherapist->email, 'is_email_verified' => '1']);

                $model->setIsVerified($getTherapist->id, '1');
            }
        } else {
            return $this->returns('error.otp.wrong', NULL, true);
        }

        return $this->returns('success.email.otp.compare', collect([]));
    }

    public function verifyMobile(Request $request)
    {
        $data   = $request->all();

        /* TODO all things like email otp after get sms gateway. */

        return $this->returns('success.sms.sent', collect([]));
    }
    
    public function compareOtpSms(Request $request)
    {
        $data   = $request->all();
        $model  = new Therapist();

        /* TODO all things like email otp compare after get sms gateway. */
        $therapistId = (!empty($data['therapist_id'])) ? $data['therapist_id'] : 0;
        $otp    = (!empty($data['otp'])) ? $data['otp'] : NULL;

        if (strtolower(env('APP_ENV') != 'live') && $otp == '1234') {
            $model->where(['id' => $therapistId])->update(['is_mobile_verified' => '1']);
        } else {
            return $this->returns('error.otp.wrong', NULL, true);
        }

        return $this->returns('success.email.otp.compare', collect([]));
    }
    
    public function addFreeSlots(Request $request) {
                
        $freeSlots = [];
        if(!empty($request->startTime) && !empty($request->endTime)) {
            
            foreach ($request->startTime as $key => $value) {
                
                $startTime = Carbon::createFromTimestampMs($value);
                $endTime = Carbon::createFromTimestampMs($request->endTime[$key]);
                $data = [
                    'startTime' => $startTime->format('H:i:s'),
                    'endTime' => $endTime->format('H:i:s'),
                    'therapist_id' => $request->therapist_id,
                ];

                $slotModel = new TherapistFreeSlot();
                $checks = $slotModel->validator($data);
                if ($checks->fails()) {
                    return $this->returnError($checks->errors()->first(), NULL, true);
                }
                TherapistFreeSlot::updateOrCreate($data, $data);
                $freeSlots[] = $data;
            }
        }
        
        return $this->returns('therapist.freeslot', collect($freeSlots));
    }
    
    public function getTherapistShifts(Request $request) {

        $therapist = new Therapist();
        $default = asset('images/therapists/therapist.png');
        $now = Carbon::now();
        $date = Carbon::createFromTimestampMs($request->date);
        $date = strtotime($date) > 0 ? $date->format('Y-m-d') : $now->format('Y-m-d');
        
        $data = DB::table('therapists')
                ->leftJoin('therapist_working_schedules', 'therapists.id', '=', 'therapist_working_schedules.therapist_id')
                ->leftJoin('shop_shifts', 'shop_shifts.id', '=', 'therapist_working_schedules.shift_id')
                ->select('therapists.id', 'therapists.name', 'therapists.surname', 'therapists.email', 'therapists.profile_photo',
                        'therapist_working_schedules.*', 'shop_shifts.from', 'shop_shifts.to')
                ->where(['therapist_working_schedules.date' => $date, 
                    'therapist_working_schedules.is_working' => TherapistWorkingSchedule::WORKING,
                    'therapist_working_schedules.shop_id' => $request->shop_id])->get()->groupBy('therapist_id');
        
        $shiftData = [];
        if (!empty($data)) {
            foreach ($data as $key => $shifts) {

                $profile_photo = $shifts[0]->profile_photo;
                if (empty($profile_photo)) {
                    $profile_photo = $default;
                }
                $profilePhotoPath = (str_ireplace("\\", "/", $therapist->profilePhotoPath));
                if (Storage::disk($therapist->fileSystem)->exists($profilePhotoPath . $profile_photo)) {
                    $profile_photo = Storage::disk($therapist->fileSystem)->url($profilePhotoPath . $profile_photo);
                } else {
                    $profile_photo = $default;
                }
                
                $availability['therapist_id'] = $shifts[0]->therapist_id;
                $availability['name'] = $shifts[0]->name;
                $availability['surname'] = $shifts[0]->surname;
                $availability['profile_photo'] = $profile_photo;
                $availability['date'] = strtotime($shifts[0]->date) * 1000;
                foreach ($shifts as $key => $shift) {
                    $therapist_shifts[] = [
                        'shop_id' => $shift->shop_id,
                        'shift_id' => $shift->shift_id,
                        'from' => strtotime($shift->from) * 1000,
                        'to' => strtotime($shift->to) * 1000,
                    ];
                }
                $availability['shifts'] = $therapist_shifts;
                unset($therapist_shifts);
                array_push($shiftData, $availability);
                unset($availability);
            }
        }
        return $this->returns('all.therapist.shifts', collect($shiftData));
    }
    
    public function readNews(Request $request) {
        
        $model = new TherapistNews();
        $checks = $model->validator($request->all());
        if ($checks->fails()) {
            return $this->returnError($checks->errors()->first(), NULL, true);
        }
        
        $read = $model->updateOrCreate($request->all(), $request->all());
        return $this->returns('news.read', collect($read));
    }        

    public function getList(Request $request) {

        $lists = TherapistExchange::with('therapist', 'withTherapist', 'shifts', 'withShifts', 'shop')
                        ->where(['therapist_id' => $request->therapist_id, 'shop_id' => $request->shop_id,
                                'status' => TherapistExchange::NO_ACTION])->get();

        $shiftList = [];
        foreach ($lists as $key => $value) {

            $your_shift = [
                'therapist_id' => $value->therapist_id,
                'therapist_name' => $value->therapist->name,
                'therapist_surname' => $value->therapist->surname,
                'shift_id' => $value->shift_id,
                'from' => $value->shifts->from,
                'to' => $value->shifts->to,
            ];
            $with_shift = [
                'therapist_id' => $value->with_therapist_id,
                'therapist_name' => $value->withTherapist->name,
                'therapist_surname' => $value->withTherapist->surname,
                'shift_id' => $value->with_shift_id,
                'from' => $value->withShifts->from,
                'to' => $value->withShifts->to,
            ];
            
            $list = [
                'date' => $value->date,
                'status' => $value->status,
                'shop_id' => $value->shop->id,
                'your_shift' => $your_shift,
                'with_shift' => $with_shift
            ];
            array_push($shiftList, $list);
        }
        return $this->returns('exchange.list', collect($shiftList));
    }
    
    public function approveShift(Request $request) {
        
        DB::beginTransaction();
        try {
            
            $shift = TherapistExchange::where('id', $request->exchange_shift_id)->where('status', '!=', TherapistExchange::REJECT)->first();
            if(empty($shift)) {
                return $this->returnError($this->errorMsg['shift.not.found']);
            }
            if($shift->status == TherapistExchange::$status[TherapistExchange::APPROVED]) {
                return $this->returnError($this->errorMsg['shift.approved']);
            }
            
            $shift->update(['status' => TherapistExchange::APPROVED]);
            
            $schedule = TherapistWorkingSchedule::where(['therapist_id' => $shift->therapist_id, 'shift_id' => $shift->shift_id])->first();
            $schedule->update(['is_exchange' => TherapistWorkingSchedule::IS_EXCHANGE]);
            
            $date = Carbon::createFromTimestampMs($schedule->date);
            $exchange_schedule = [
                'therapist_id' => $shift->with_therapist_id,
                'shift_id' => $shift->with_shift_id,
                'shop_id' => $shift->shop_id,
                'date' => $date->format('Y-m-d'),
                'is_working' => TherapistWorkingSchedule::WORKING
            ];
            TherapistWorkingSchedule::create($exchange_schedule);
            DB::commit();
            return $this->returnSuccess(__($this->successMsg['shift.approve']), $shift);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollback();
            throw $e;
        }
    }
    
    public function rejectShift(Request $request) {
        
        $shift = TherapistExchange::where('id', $request->exchange_shift_id)->first();
        if(empty($shift)) {
            return $this->returnError($this->errorMsg['shift.not.found']);
        }
        $shift->update(['status' => TherapistExchange::REJECT]);
        
        return $this->returnSuccess(__($this->successMsg['shift.reject']), $shift);
    }
    
    public function addObservation(Request $request) {
        
        $booking = BookingMassage::find($request->booking_massage_id);
        if(empty($booking)) {
            return $this->returnError($this->errorMsg['no.booking.found']);
        }
        
        $booking->update(['observation' => $request->observation]);
        return $this->returnSuccess(__($this->successMsg['observation.add']), $booking);
    }
    
    public function getNews(Request $request) {
        
        $manager = Manager::where('shop_id', $request->shop_id)->first();
        
        $data = News::where('manager_id', $manager->id)->get();
        $readNews = TherapistNews::where('therapist_id', $request->id)->pluck('news_id')->toArray();
        
        $allNews = [];
        if (!empty($data)) {
            foreach ($data as $key => $news) {

                $newsData = [
                    'id' => $news['id'],
                    'title' => $news['title'],
                    'sub_title' => $news['sub_title'],
                    'description' => $news['description'],
                    'created_at' => strtotime($news['created_at']) * 1000              
                ];
                if(in_array($news['id'], $readNews))
                {
                    $newsData['is_read'] = true;
                } else {
                    $newsData['is_read'] = false;
                }
                array_push($allNews, $newsData);
                unset($newsData);
            }
        }
        return $this->returnSuccess(__($this->successMsg['news.get']), $allNews);
    }
    
    public function deleteOtp(Request $request) {
        
        $otps = ForgotOtp::where(['model_id' => $request->user_id, 'model' => Therapist::THERAPIST])->get();
        foreach ($otps as $key => $otp) {
            $otp->delete();
        }
        return true;
    }
    
    public function forgotPassword(Request $request) {

        $therapist = Therapist::where('mobile_number', $request->mobile_number)->first();

        if (empty($therapist)) {
            return $this->returnError($this->errorMsg['notFound']);
        }

        $request->request->add(['user_id' => $therapist->id]);
        $this->deleteOtp($request);
        
        $data = [
            'model_id' => $therapist->id,
            'model' => Therapist::THERAPIST,
            'otp' => 1234,
            'mobile_number' => $request->mobile_number,
            'mobile_code' => $request->mobile_code,
        ];

        ForgotOtp::create($data);
        return $this->returnSuccess(__($this->successMsg['success.otp']), ['user_id' => $therapist->id, 'otp' => 1234]);
    }
    
    public function resetPassword(Request $request) {
        
        $therapist = Therapist::find($request->user_id);

        if (empty($therapist)) {
            return $this->returnError($this->errorMsg['notFound']);
        }
        
        $therapist->update(['password' => Hash::make($request->password)]);
        $this->deleteOtp($request);
        
        return $this->returnSuccess(__($this->successMsg['success.reset.password']), $therapist);
    }
    
    public function verifyOtp(Request $request) {
        
        $is_exist = ForgotOtp::where(['model_id' => $request->user_id, 'model' => Therapist::THERAPIST, 'otp' => $request->otp])->first();
        
        if(empty($is_exist)) {
            return $this->returnError($this->errorMsg['otp.not.found']);
        }
        return $this->returnSuccess(__($this->successMsg['success.otp.verified']), $is_exist);
    }
}
