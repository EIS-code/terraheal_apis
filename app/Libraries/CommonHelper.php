<?php

namespace App\Libraries;

use App\ShopService;
use App\ServiceImage;
use App\ServiceTiming;
use App\ServicePricing;
use App\Service;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class CommonHelper {

    public static function getAllService($request)
    {
        $pageNumber = isset($request->page_number) ? $request->page_number : 1;
        $services =  ShopService::with('service')->where('shop_id', $request->get('shop_id'))
                    ->whereHas('service', function($q) use($request) {
                        $q->where(function($query) use ($request) {
                            if (!empty($request->search_val)) {
                                $query->where("english_name", "LIKE", "%{$request->search_val}%")
                                ->orWhere("portugese_name", "LIKE", "%{$request->search_val}%");
                            }
                        });
                        if ($request->type == Service::MASSAGE || $request->type == Service::THERAPY) {
                            $q->where('service_type', (string)$request->type);
                        }
                    })->get();
        $allServices = [];
        foreach ($services as $key => $massage) {
            $pricingData = [];
            $image = ServiceImage::where(['service_id' => $massage->service_id, 'is_featured' => ServiceImage::IS_FEATURED])->first();
            $service = [
                'id' => $massage->service_id,
                'name' => $massage->service->english_name,
                'english_name' => $massage->service->english_name,
                'portugese_name' => $massage->service->portugese_name,
                'short_description' => $massage->service->short_description,
                'priority' => $massage->service->priority,
                'expenses' => $massage->service->expenses,
                'service_type' => $massage->service->service_type,
                'image' => !empty($image) ? $image->image : NULL
            ];
            $timings = ServiceTiming::where('service_id', $massage->service_id)->get();
            foreach ($timings as $i => $timing) {
                $pricing = ServicePricing::where(['service_id' => $massage->service_id, 'service_timing_id' => $timing->id])->first();
                array_push($pricingData, $pricing);
            }
            $service['timing'] = $timings->toArray();
            $service['pricing'] = $pricingData;
            array_push($allServices, $service);
            unset($service);
            unset($pricingData);
        }
        if (!empty($request->isGetAll)) {
            return $allServices;
        } else {
            $allServices = collect($allServices);
            $paginate = new LengthAwarePaginator($allServices, count($allServices), 18, 1, []);
            return $paginate;
        }
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
        $fileName = mt_rand(). time() . '_' . $centerId . '.' . $name;
        $storeFile = $data->storeAs($storagePath, $fileName, $fileSystem);

        if ($storeFile) {
            return $fileName;
        }
        return false;
    }
}
