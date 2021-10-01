<?php

namespace App\Http\Controllers\Shops\Therapist;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use App\Booking;
use App\Therapist;
use App\TherapistWorkingSchedule;
use App\TherapistWorkingScheduleTime;
use App\TherapistUserRating;
use App\Libraries\CommonHelper;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DB;
use App\TherapistNews;
use App\TherapistReview;

class TherapistController extends BaseController {

    public $successMsg = [
        
        'therapist' => 'Therapist found successfully!',
        'therapist.bookings' => 'Therapist bookings found successfully!',
        'therapist.data.found' => 'Therapist data found successfully!',
        'therapist.info' => 'Therapist personal information found successfully!',
        'therapist.ratings' => 'Therapist ratings found successfully!',
        'therapist.schedule' => 'Therapist schedule added successfully!',
        'profile.update.successfully' => 'Therapist profile updated successfully!',
        'my.availability.found' => 'My availability found successfully !',
        'therapist.attendance' => 'Therapist attendance data found successfully !',
        'no.data.found' => 'No data found',
        'news.read' => 'News read successfully !',
    ];
    
    public function myBookings(Request $request) {
        
        $booking_type = !empty($request->type) ? $request->type : Booking::BOOKING_TYPE_IMC;
        $booking_time = !empty($request->booking_time) ? ($request->booking_time == 0 ? Booking::BOOKING_TODAY : Booking::BOOKING_FUTURE) : Booking::BOOKING_TODAY;
        $booking_status = !empty($request->booking_status) ? ($request->booking_status == 0 ? Booking::BOOKING_WAITING : Booking::BOOKING_COMPLETED) : Booking::BOOKING_WAITING;
        $request->request->add(['type' => $booking_type, 'bookings_filter' => array($booking_time,$booking_status)]);
        $bookingModel = new Booking();
        $myBookings = $bookingModel->getGlobalQuery($request);

        return $this->returnSuccess(__($this->successMsg['therapist.bookings']), $myBookings);
    }
    
    public function getTherapists(Request $request)
    {
        $model = new Therapist();
        $therapists = $model->getTherapist($request);
        return $this->returnSuccess(__($this->successMsg['therapist.data.found']), $therapists);
    }
    
    public function getInfo(Request $request) {
        
        $therapist = Therapist::getGlobalQuery(Therapist::IS_NOT_FREELANCER,$request);
        
        return $this->returnSuccess(__($this->successMsg['therapist.info']), $therapist);
    }
    
    public function updateProfile(int $isFreelancer = Therapist::IS_NOT_FREELANCER, Request $request)
    {
        Therapist::updateProfile($isFreelancer, $request);

        return $this->returnSuccess('profile.update.successfully', $this->getGlobalResponse($isFreelancer, $request, false));
    }
    
    public function getGlobalResponse(int $isFreelancer = Therapist::IS_NOT_FREELANCER, Request $request, $returnResponse = true)
    {
        $data = Therapist::getGlobalQuery($isFreelancer, $request);

        return $returnResponse ? $this->returns('therapist.information.successfully', $data) : $data;
    }
    
    public function myAvailabilities(Request $request)
    {
        $date  = Carbon::createFromTimestampMs($request->date);
        $date  = strtotime($date) > 0 ? $date : Carbon::now();
        $data = TherapistWorkingSchedule::with('shifts')->whereMonth('date', $date->month)->where('therapist_id', $request->therapist_id)->get();
        
        if (!empty($data)) {
            return $this->returnSuccess(__($this->successMsg['my.availability.found']), ['Schedule' => $data]);
        } else {
            return $this->returnSuccess(__($this->successMsg['no.data.found']));
        }
    }
    
    public function addAvailability(Request $request) {
        
        DB::beginTransaction();
        try {
            
            $startDate = date('Y-m-d',$request->from);
            $endDate = date('Y-m-d',$request->to);
            $dateRange = CarbonPeriod::create($startDate, $endDate);
            $start_time = date('H:i:s',$request->start_time);
            $end_time = date('H:i:s',$request->end_time);
            
            foreach ($dateRange as $key => $value) {

                $scheduleData = $data[] = [
                    'date' => $value->format('Y-m-d'),
                    'therapist_id' => $request->therapist_id
                ];
                $schedule = TherapistWorkingSchedule::create($scheduleData);
                $timeData = [
                    'schedule_id' => $schedule->id,
                    'start_time' => $start_time,
                    'end_time' => $end_time
                ];
                TherapistWorkingScheduleTime::create($timeData);
            }
            DB::commit();
            return $this->returnSuccess(__($this->successMsg['therapist.schedule']),$data);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollback();
            throw $e;
        }
        
    }
    
    public function getRatings(Request $request) {
        
        $ratings = TherapistUserRating::where(['model_id' => $request->therapist_id, 'model' => 'App\Therapist'])->get()->groupBy('type');

        $ratingsData = [];
        foreach ($ratings as $key => $rating) {
            
            $cnt = $rates = 0;
            foreach ($rating as $key => $rate) {
                $type = $rate->type;
                $rates += $rate->rating;
                $cnt++;
            }
            $avg = $rates / $cnt;
            $ratingsData[] = [
                "therapist_id" => $request->therapist_id,
                "type" => $type,
                "avg_rating" => $avg
                
            ];
        }
        return $this->returnSuccess(__($this->successMsg['therapist.ratings']),$ratingsData);
        
    }
    
    public function getTherapistRatings(Request $request) {
        
        $filter = !empty($request->filter) ? $request->filter : TherapistReview::TODAY;
        $ratings = TherapistReview::with('question')->where(['therapist_id' => $request->therapist_id]);
        $now = Carbon::now();
        
        if ($filter == TherapistReview::TODAY) {
            $ratings->whereDate('created_at', $now->format('Y-m-d'));
        }
        if ($filter == TherapistReview::YESTERDAY) {
            $ratings->whereDate('created_at', $now->subDays(1));
        }
        if ($filter == TherapistReview::THIS_WEEK) {
            $weekStartDate = $now->startOfWeek()->format('Y-m-d');
            $weekEndDate = $now->endOfWeek()->format('Y-m-d');
            $ratings->whereDate('created_at', '>=', $weekStartDate)->whereDate('created_at', '<=', $weekEndDate);
        }
        if ($filter == TherapistReview::CURRENT_MONTH) {
            $ratings->whereMonth('created_at', $now->month);
        }
        if ($filter == TherapistReview::LAST_7_DAYS) {
            $todayDate = $now->format('Y-m-d');
            $agoDate = $now->subDays(7)->format('Y-m-d');           
            $ratings->whereDate('created_at', '>=', $agoDate)->whereDate('created_at', '<=', $todayDate);
        }
        if ($filter == TherapistReview::LAST_14_DAYS) {
            $todayDate = $now->format('Y-m-d');
            $agoDate = $now->subDays(14)->format('Y-m-d');
            $ratings->whereDate('created_at', '>=', $agoDate)->whereDate('created_at', '<=', $todayDate);
        }
        if ($filter == TherapistReview::LAST_30_DAYS) {
            $todayDate = $now->format('Y-m-d');
            $agoDate = $now->subDays(30)->format('Y-m-d');
            $ratings->whereDate('created_at', '>=', $agoDate)->whereDate('created_at', '<=', $todayDate);
        }
        if ($filter == TherapistReview::CUSTOM) {
            $date = $date = Carbon::createFromTimestampMs($request->date);
            $ratings->whereDate('created_at', $date);
        }
        
        $ratings = $ratings->get()->groupBy('question_id');
        $ratingData = [];
        if(!empty($ratings)) {
            foreach ($ratings as $key => $rate) {
             
                $first = $rate->first();
                $avg = $cnt = 0;
                foreach ($rate as $key => $value) {
                    $avg += $value->rating;
                    $cnt++;
                }
                $ratingData[] = [
                    'question_id' => $first->question_id,
                    'question' => $first->question->question,
                    'rate' => (float) number_format($avg / $cnt, 2)
                ];
            }
            return $this->returnSuccess(__($this->successMsg['therapist.ratings']), $ratingData);
            
        } else {
            return $this->returnSuccess(__($this->successMsg['no.data.found']));
        }
    }
    
    public function myAttendence(Request $request) {
        
        $date = !empty($request->date) ? Carbon::createFromTimestampMs($request->date) : Carbon::now();
        $scheduleData = TherapistWorkingSchedule::with('shifts')->where('therapist_id',$request->therapist_id)
                ->whereMonth('date',$date->month)->get();
        $presentDays = TherapistWorkingSchedule::with('shifts')->whereMonth('date', $date->month)->where(['therapist_id' => $request->therapist_id])->get()->groupBy('date')->count();
        $totalAbsent = TherapistWorkingSchedule::with('shifts')->whereMonth('date', $date->month)->where(['therapist_id' => $request->therapist_id])->get()->groupBy('date')->count();
        
        if(count($scheduleData) > 0) {
            
            $totalHours = [];
            $breakHours = [];        

            foreach ($scheduleData as $key => $value) {

                if(!is_null($value['therapistWorkingScheduleTime'])) {
                    $start_time = Carbon::createFromTimestampMs($value['therapistWorkingScheduleTime']['start_time']);
                    $end_time = Carbon::createFromTimestampMs($value['therapistWorkingScheduleTime']['end_time']);
                    $total = new Carbon($start_time->diff($end_time)->format("%h:%i"));
                }

                $therapist_break = [];
                foreach ($value->therapistBreakTime as $key => $break) {
                    if(!is_null($break)) {
                        $break_start_time = new Carbon(Carbon::createFromTimestampMs($break['from']));
                        $break_end_time = new Carbon(Carbon::createFromTimestampMs($break['to']));
                        $breakHours[] = $therapist_break[] = $break_start_time->diff($break_end_time)->format("%h:%i");
                    }
                }
                $value['break_time'] = CommonHelper::calculateHours($therapist_break);
                if(isset($total) && !empty($total)) {
                    $value['total'] = $totalHours[] = $total->diff(new Carbon($value['break_time']))->format("%h:%i");
                }
                unset($therapist_break); 
            }

            //calculate total hours
            $hours = CommonHelper::calculateHours($totalHours);

            //calculate total break hours
            $break = CommonHelper::calculateHours($breakHours);

            return $this->returnSuccess(__($this->successMsg['therapist.attendance']),['receptionistData' => $scheduleData, 
                'totalWorkingDays' => $presentDays + $totalAbsent, 'presentDays' => $presentDays, 'absentDays' => $totalAbsent,
                'totalHours' => explode(':', $hours)[0], 'totalBreakHours' => explode(':', $break)[0],'totalWorkingHours' => explode(':', $hours)[0]-explode(':', $break)[0]]);
        } else {
             return $this->returnSuccess(__($this->successMsg['no.data.found']));
        }
    }
    
    public function getCalendar(Request $request) {
        
        $date  = Carbon::createFromTimestampMs($request->month_date);
        $date  = strtotime($date) > 0 ? $date : Carbon::now();
        
        $totalPresent = TherapistWorkingSchedule::with('therapistWorkingScheduleTime')->whereMonth('date', $date->month)->where(['therapist_id' => $request->therapist_id])->get()->count();
        $totalAbsent = TherapistWorkingSchedule::with('therapistWorkingScheduleTime')->whereMonth('date', $date->month)->where(['therapist_id' => $request->therapist_id])->get()->count();
        
        $booking_type = !empty($request->type) ? $request->type : Booking::BOOKING_TYPE_IMC;
        $request->request->add(['type' => $booking_type, 'therapist_id' => $request->therapist_id, 'month' => $date]);
        $bookingModel = new Booking();
        $myBookings = $bookingModel->getGlobalQuery($request);

        return $this->returnSuccess(__($this->successMsg['therapist.bookings']), ['Bookings' => $myBookings, 'TotalAbsent' => $totalAbsent, 'TotalPresent' => $totalPresent]);
    }
    
    public function readNews(Request $request) {
        
        $model = new TherapistNews();
        $checks = $model->validator($request->all());
        if ($checks->fails()) {
            return $this->returnError($checks->errors()->first(), NULL, true);
        }
        
        $read = $model->updateOrCreate($request->all(), $request->all());
        return $this->returnSuccess(__($this->successMsg['news.read']), $read);
    }
}
