<?php

namespace App\Libraries;

use App\Therapy;
use App\Massage;

class serviceHelper {

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

}
