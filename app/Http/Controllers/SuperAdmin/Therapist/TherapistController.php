<?php

namespace App\Http\Controllers\SuperAdmin\Therapist;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use App\Therapist;
use App\TherapistUserRating;
use Carbon\Carbon;
use App\TherapistWorkingSchedule;
use App\TherapistReview;
use App\TherapistSelectedService;
use App\Service;
use App\Booking;

class TherapistController extends BaseController
{   

    public $successMsg = [
        'no.data.found' => "Data not found !",
        'therapist.details' => "Therapists found successfully !",
        'therapist.get.details' => "Therapist details found successfully !",
        'therapist.get.ratings' => "Therapist ratings found successfully !"
    ];

    public function getTherapists(Request $request) {
        
        if(!empty($request->shop_id)) {
            $therapists = Therapist::where('shop_id', $request->shop_id)->get();
        } else {
            $therapists = Therapist::all();
        }

        foreach ($therapists as $key => $therapist) {

            $ratings = TherapistUserRating::where(['model_id' => $therapist->id, 'model' => 'App\Therapist'])->get();

            $cnt = $rates = $avg = 0;
            if ($ratings->count() > 0) {
                foreach ($ratings as $i => $rating) {
                    $rates += $rating->rating;
                    $cnt++;
                }
                $avg = $rates / $cnt;
            }
            $therapist['average'] = number_format($avg, 2);
            
            $therapist['selectedMassages'] = TherapistSelectedService::with('service')->where('therapist_id', $therapist->id)
                                    ->whereHas('service', function($q) {
                                        $q->where('service_type', Service::MASSAGE);
                                    })->get()->count();
            $therapist['selectedTherapies'] = TherapistSelectedService::with('service')->where('therapist_id', $therapist->id)
                                    ->whereHas('service', function($q) {
                                        $q->where('service_type', Service::THERAPY);
                                    })->get()->count();
        }
        
        $filter = isset($request->filter) ? $request->filter : Booking::TODAY;
        $earnings = [];
        $bookingModel = new Booking();

        if ($filter == Booking::TODAY) {
            $request->request->add(['date_filter' => Booking::TODAY]);
        } else if ($filter == Booking::YESTERDAY) {
            $request->request->add(['date_filter' => Booking::YESTERDAY]);
        } else if ($filter == Booking::THIS_WEEK) {
            $request->request->add(['date_filter' => Booking::THIS_WEEK]);
        } else if ($filter == Booking::THIS_MONTH) {
            $request->request->add(['date_filter' => Booking::THIS_MONTH]);
        } else {
            $request->request->add(['date_filter' => Booking::TODAY]);
        }

        $data = $bookingModel->getGlobalQuery($request)->whereNotNull('therapist_id')->groupBy(['therapist_id', 'booking_id']);

        $therapist_earnings = 0;
        if(!empty($data)) {
            foreach ($data as $key => $value) {
                foreach($value as $i => $booking) {
                    foreach ($booking as $j => $value) {
                        $therapist_earnings += $value['price'];
                    }
                }
                if($therapist_earnings > 0) {
                    $earnings[] = [
                        'therapist_id' => $key,
                        'therapist_earnings' => $therapist_earnings
                    ];
                }
                $therapist_earnings = 0;
            }
        }
        $earnings = collect($earnings)->sortBy('therapist_earnings')->reverse()->toArray();
        $earnings = array_values(array_slice($earnings, 0,3,true));
        
        
        $therapist_data = [];
        if(!empty($earnings)) {
            foreach ($earnings as $key => $therapist) {
                
                $top_therapist = Therapist::find($therapist['therapist_id']);
                $top_therapist['earnings'] = $therapist['therapist_earnings'];
                
                $ratings = TherapistUserRating::where(['model_id' => $top_therapist->id, 'model' => 'App\Therapist'])->get();

                $cnt = $rates = $avg = 0;
                if ($ratings->count() > 0) {
                    foreach ($ratings as $i => $rating) {
                        $rates += $rating->rating;
                        $cnt++;
                    }
                    $avg = $rates / $cnt;
                }
                $top_therapist['average'] = number_format($avg, 2);

                $top_therapist['selectedMassages'] = TherapistSelectedService::with('service')->where('therapist_id', $top_therapist->id)
                                ->whereHas('service', function($q) {
                                    $q->where('service_type', Service::MASSAGE);
                                })->get()->count();
                $top_therapist['selectedTherapies'] = TherapistSelectedService::with('service')->where('therapist_id', $top_therapist->id)
                                ->whereHas('service', function($q) {
                                    $q->where('service_type', Service::THERAPY);
                                })->get()->count();
                                
                if($key == 0) {
                    $top_therapist['rank'] = Therapist::GOLD;
                }
                if($key == 1) {
                    $top_therapist['rank'] = Therapist::SILVER;
                }
                if($key == 2) {
                    $top_therapist['rank'] = Therapist::BRONZE;
                }
                $therapist_data[] = $top_therapist;
            }
        }
        
        return $this->returnSuccess(__($this->successMsg['therapist.details']), ['therapists' => $therapists, 'top_therapists' => $therapist_data]);
    }
    
    public function getInfo(Request $request) {
        
        $data = Therapist::getGlobalQuery(Therapist::IS_NOT_FREELANCER, $request);
        
        $now  = Carbon::now()->timestamp * 1000;
        $date = Carbon::createFromTimestampMs($request->get('date', $now));
        $id   = $request->get('id', false);

        $data['availability'] = TherapistWorkingSchedule::getScheduleByMonth($id, $date->format('Y-m-d'));

        return $this->returnSuccess(__($this->successMsg['therapist.get.details']), $data);
    }
    
    public function getRatings(Request $request) {
        
        $ratings = TherapistReview::with('question')->where(['therapist_id' => $request->therapist_id])
                ->get()->groupBy('question_id');
        
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
            return $this->returnSuccess(__($this->successMsg['therapist.get.ratings']), $ratingData);
            
        } else {
            return $this->returnSuccess(__($this->successMsg['no.data.found']));
        }
    }
}
