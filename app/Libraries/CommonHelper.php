<?php

namespace App\Libraries;

use App\Therapy;
use App\Massage;

class CommonHelper {

    public static function getAllService($request) {
        $therapies = Therapy::with('timing', 'pricing')->where('shop_id', $request->get('shop_id'))->get();
        $massages = Massage::with('timing', 'pricing')->where('shop_id', $request->get('shop_id'))->get();
        $services = collect();

        foreach ($therapies as $therapy) {
            $services->push($therapy);
        }

        foreach ($massages as $massage) {
            $services->push($massage);
        }

        return $services;
    }
    
    public static function calculateHours($data) {
     
        $sum = strtotime('00:00:00');
        $totaltime = 0;
        foreach ($data as $element) {

            $timeinsec = strtotime($element) - $sum;
            $totaltime = $totaltime + $timeinsec;
        }
        $h = intval($totaltime / 3600);
        $totaltime = $totaltime - ($h * 3600);
        $m = intval($totaltime / 60);
        return "$h:$m";
    }

}
