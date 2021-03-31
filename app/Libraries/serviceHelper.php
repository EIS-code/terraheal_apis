<?php

namespace App\Libraries;

use App\Therapy;
use App\Massage;

class serviceHelper {

    public static function getAllService($request) {
        // 1 for massages , 2 for therapies
        if ($request->service == 1) {
            $services = Massage::with('timing', 'pricing')->where('shop_id', $request->get('shop_id'));
        } else {
            $services = Therapy::with('timing', 'pricing')->where('shop_id', $request->get('shop_id'));
        }

        if (isset($request->search_val)) {
            $services = $services->where('name', 'like', $request->search_val . '%');
        }

        return $services->get();
    }

}
