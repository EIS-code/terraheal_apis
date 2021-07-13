<?php

namespace App\Http\Controllers\SuperAdmin\Therapist;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use App\Therapist;
use App\TherapistUserRating;
use Carbon\Carbon;
use App\TherapistWorkingSchedule;

class TherapistController extends BaseController
{   

    public $successMsg = [
        'therapist.details' => "Therapists found successfully !",
        'therapist.get.details' => "Therapist details found successfully !"
    ];

    public function getTherapists()
    {
        $therapists = Therapist::with('selectedService')->get();

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
}
