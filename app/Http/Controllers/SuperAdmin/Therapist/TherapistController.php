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

class TherapistController extends BaseController
{   

    public $successMsg = [
        'no.data.found' => "Data not found !",
        'therapist.details' => "Therapists found successfully !",
        'therapist.get.details' => "Therapist details found successfully !",
        'therapist.get.ratings' => "Therapist ratings found successfully !"
    ];

    public function getTherapists() {
        $therapists = Therapist::all();

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

        return $this->returnSuccess(__($this->successMsg['therapist.details']), $therapists);
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
                dd($first);
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
