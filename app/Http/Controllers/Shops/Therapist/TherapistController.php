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
        'no.data.found' => 'No data found'
    ];
    
    public function myBookings(Request $request) {
        
        $booking_type = isset($request->type) ? $request->type : Booking::BOOKING_TYPE_IMC;
        $booking_time = isset($request->booking_time) ? ($request->booking_time == 0 ? Booking::BOOKING_TODAY : Booking::BOOKING_FUTURE) : Booking::BOOKING_TODAY;
        $booking_status = isset($request->booking_status) ? ($request->booking_status == 0 ? Booking::BOOKING_WAITING : Booking::BOOKING_COMPLETED) : Booking::BOOKING_WAITING;
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
        $data = TherapistWorkingSchedule::with('therapistWorkingScheduleTime')->whereMonth('date', $date->month)->where('therapist_id', $request->therapist_id)->get();
        
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
            $working = $request->is_working; 
            if($working == TherapistWorkingSchedule::WORKING) {
                $absent = TherapistWorkingSchedule::NOT_ABSENT;
            } else {
                $absent = TherapistWorkingSchedule::ABSENT;
            }
            
            foreach ($dateRange as $key => $value) {

                $scheduleData = $data[] = [
                    'date' => $value->format('Y-m-d'),
                    'is_working' => $working,
                    'is_absent' => $absent,
                    'absent_reason' => $request->absent_reason ? $request->absent_reason : NULL,
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
    
    public function myAttendence(Request $request) {
        
        $date = isset($request->date) ? Carbon::createFromTimestampMs($request->date) : Carbon::now();
        $scheduleData = TherapistWorkingSchedule::with('therapistBreakTime','therapistWorkingScheduleTime')->where('therapist_id',$request->therapist_id)
                ->whereMonth('date',$date->month)->get();
        $presentDays = TherapistWorkingSchedule::with('therapistWorkingScheduleTime')->whereMonth('date', $date->month)->where(['therapist_id' => $request->therapist_id, 'is_working' => TherapistWorkingSchedule::WORKING])->get()->count();
        $totalAbsent = TherapistWorkingSchedule::with('therapistWorkingScheduleTime')->whereMonth('date', $date->month)->where(['therapist_id' => $request->therapist_id, 'is_absent' => TherapistWorkingSchedule::ABSENT])->get()->count();
        
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
        
        $totalPresent = TherapistWorkingSchedule::with('therapistWorkingScheduleTime')->whereMonth('date', $date->month)->where(['therapist_id' => $request->therapist_id, 'is_working' => TherapistWorkingSchedule::WORKING])->get()->count();
        $totalAbsent = TherapistWorkingSchedule::with('therapistWorkingScheduleTime')->whereMonth('date', $date->month)->where(['therapist_id' => $request->therapist_id, 'is_absent' => TherapistWorkingSchedule::ABSENT])->get()->count();
        
        $booking_type = isset($request->type) ? $request->type : Booking::BOOKING_TYPE_IMC;
        $request->request->add(['type' => $booking_type, 'therapist_id' => $request->therapist_id, 'month' => $date]);
        $bookingModel = new Booking();
        $myBookings = $bookingModel->getGlobalQuery($request);

        return $this->returnSuccess(__($this->successMsg['therapist.bookings']), ['Bookings' => $myBookings, 'TotalAbsent' => $totalAbsent, 'TotalPresent' => $totalPresent]);
    }
}
