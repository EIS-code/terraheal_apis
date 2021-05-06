<?php

namespace App\Http\Controllers\SuperAdmin\Therapist;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use App\Therapist;
use App\TherapistUserRating;

class TherapistController extends BaseController
{   

    public $successMsg = [
        'therapist.details' => "Therapists found successfully !"
    ];

    public function getTherapists()
    {
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
        }

        return $this->returnSuccess(__($this->successMsg['therapist.details']), $therapists);
    }
}
