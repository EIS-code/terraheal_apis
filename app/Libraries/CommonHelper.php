<?php

namespace App\Libraries;

use App\Therapy;
use App\Massage;
use App\Shop;

class CommonHelper {

    public static function getAllService($request)
    {
        $pageNumber = isset($request->page_number) ? $request->page_number : 1;
        $type       = (in_array($request->type, [Shop::MASSAGES, Shop::THERAPIES])) ? $request->type : Shop::MASSAGES;

        if ($type == Shop::MASSAGES) {
            $services = Massage::with('timing', 'pricing')
                               ->select('id','name','image','icon','shop_id')
                               ->where('shop_id', $request->get('shop_id'));
        } else {
            $services = Therapy::with('timing', 'pricing')->where('shop_id', $request->get('shop_id'));
        }

        if (!empty($request->q)) {
            $query      = $request->q;

            $services   = $services->where("name", "LIKE", "%{$query}%");
        }

        if (!empty($request->isGetAll)) {
            $services = $services->get();
        } else {
            $services = $services->paginate(18, ['*'], 'page', $pageNumber);
        }

        return $services;
    }

    public static function calculateHours($data)
    {
        $sum        = strtotime('00:00:00');
        $totaltime  = 0;

        foreach ($data as $element) {
            $timeinsec = strtotime($element) - $sum;
            $totaltime = $totaltime + $timeinsec;
        }

        $h          = intval($totaltime / 3600);
        $totaltime  = $totaltime - ($h * 3600);
        $m          = intval($totaltime / 60);

        return "$h:$m";
    }

    public static function uploadImage($data ,$storagePath, $fileSystem, $centerId) {
        
        $name = $data->getClientOriginalExtension();
        $fileName = time() . '_' . $centerId . '.' . $name;
        $storeFile = $data->storeAs($storagePath, $fileName, $fileSystem);

        if ($storeFile) {
            return $fileName;
        }
        return false;
    }
}
