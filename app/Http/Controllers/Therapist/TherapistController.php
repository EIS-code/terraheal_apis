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
use App\Receptionist;
use App\TherapistWorkingScheduleBreak;
use App\Therapy;
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
use App\TherapistShift;
use App\TherapistFreeSlot;
use App\TherapistNews;

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
        'error.otp' => 'Please provide OTP properly.',
        'error.otp.wrong' => 'OTP seems wrong.',
        'error.otp.already.verified' => 'OTP already verified.',
        'error.therapist.id' => 'Please provide valid therapist id.',
        'error.email.already.verified' => 'This user email already verified with this ',
        'error.email.id' => ' email id.',
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
        'new.therapist' => 'New therapist created successfully !',
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
                                if (empty($bookingInfo->userPeople)) {
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
                                $returnData[$increments]['massage_date']         = $bookingInfo->massage_date;
                                $returnData[$increments]['massage_time']         = $bookingInfo->massage_time;
                                $returnData[$increments]['user_people_id']       = $bookingInfo->user_people_id;
                                $returnData[$increments]['therapist_id']         = $bookingInfo->therapist_id;
                                $returnData[$increments]['user_name']            = $bookingInfo->userPeople->name;
                                $returnData[$increments]['therapist_name']       = $bookingInfo->therapist->fullName;
                                $returnData[$increments]['massage_name']         = !empty($bookingMassage->massageTiming) ? $bookingMassage->massageTiming->massage->name : NULL;
                                $returnData[$increments]['therapy_name']         = !empty($bookingMassage->therapyTiming) ? $bookingMassage->therapyTiming->therapy->name : NULL;
                                $returnData[$increments]['massage_id']           = !empty($bookingMassage->massageTiming) ? $bookingMassage->massageTiming->massage_id : NULL;
                                $returnData[$increments]['therapy_id']           = !empty($bookingMassage->therapyTiming) ? $bookingMassage->therapyTiming->therapy_id : NULL;
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

        if (!empty($id)) {
            $return = $model::getCalender($id, $month);

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
        if ($request->service == Shop::MASSAGES) {
            $services = Massage::with('timing', 'pricing')->where('shop_id', $request->get('shop_id'));
        } 
        if ($request->service == Shop::THERAPIES) {
            $services = Therapy::with('timing', 'pricing')->where('shop_id', $request->get('shop_id'));
        }

        if (!empty($request->search_val)) {
            $services = $services->where('name', 'like', $request->search_val . '%');
        }

        $services =  $services->get();

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

            $data = [
                'date' => $date,
                'is_approved' => $model::IS_NOT_APPROVED,
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
            $date           = new Carbon($request->get('date', NULL) / 1000);
            $minutes        = $request->get('minutes', 0);
            $breakFor       = $request->get('break_for', TherapistWorkingScheduleBreak::OTHER);
            $breakReason    = $request->get('break_reason', NULL);

            $save           = TherapistWorkingScheduleBreak::takeBreaks($id, $date->format('Y-m-d'), $minutes, $breakFor, $breakReason);

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

        $now = Carbon::now();
        $date = Carbon::createFromTimestampMs($request->date);
        $date = strtotime($date) > 0 ? $date->format('Y-m-d') : $now->format('Y-m-d');
        $search_val = $request->search_val;
        
        $data = DB::table('therapists')
                ->leftJoin('therapist_working_schedules', 'therapists.id', '=', 'therapist_working_schedules.therapist_id')
                ->leftJoin('therapist_shifts', 'therapist_shifts.schedule_id', '=', 'therapist_working_schedules.id')
                ->leftJoin('shop_shifts', 'shop_shifts.id', '=', 'therapist_shifts.shift_id')
                ->select('therapists.*', 'therapist_working_schedules.*', 'therapist_shifts.*', 'shop_shifts.*')
                ->where(['therapist_working_schedules.date' => $date, 'therapist_working_schedules.shop_id' => $request->shop_id,
                    'therapist_shifts.is_absent' => TherapistShift::NOT_ABSENT, 'therapist_shifts.is_working' => TherapistShift::WORKING]);
        
        if(!empty($search_val)) {
            $data->where(function($query) use ($search_val) {
                $query->where('therapists.name', 'like', $search_val . '%')
                        ->orWhere('therapists.surname', 'like', $search_val . '%')
                        ->orWhere('therapists.email', $search_val);
            });
        }
        
        $data = $data->get()->groupBy('therapist_id');
        
        $shiftData = [];
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                $availability['id'] = $value[0]->id;
                $availability['name'] = $value[0]->name;
                $availability['surname'] = $value[0]->surname;

                foreach ($value as $key => $shift) {
                    $availability['shifts'][] = [
                        'schedule_id' => $shift->schedule_id,
                        'shift_id' => $shift->shift_id,
                        'from' => $shift->from,
                        'to' => $shift->to
                    ];
                }
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
    
    public function newTherapist(Request $request) {
        
        $model = new Therapist();
        $data = $request->all();
        $checks = $model->validator($data);
        if ($checks->fails()) {
            return $this->returnError($checks->errors()->first(), NULL, true);
        }
        $data['password'] = Hash::make($data['password']);
        $therapist = $model->create($data);
        
        return $this->returns('new.therapist', $therapist);
    }

}
