<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use App\Shop;
use App\City;
use App\MassagePreference;
use App\SelectedMassagePreference;
use App\MassagePreferenceOption;
use App\SessionType;
use DB;
use Carbon\Carbon;
use App\Libraries\CommonHelper;
use App\Service;

class ServiceController extends BaseController
{
    public $errorMsg = [
        
    ];

    public $successMsg = [
        'success.massage.found' => 'Massage found successfully !',
        'success.massage.center.found' => 'Massage center found successfully !'
    ];

    public function returns($message = NULL, $with = NULL, $isError = false)
    {
        if ($isError && !empty($message)) {
            $message = !empty($this->errorMsg[$message]) ? __($this->errorMsg[$message]) : __($message);
        } else {
            $message = !empty($this->successMsg[$message]) ? __($this->successMsg[$message]) : __($this->returnNullMsg);
        }

        if (!$isError && !empty($with)) {
            if ($with instanceof Collection && !$with->isEmpty()) {
                return $this->returnSuccess($message, array_values($with->toArray()));
            } else {
                return $this->returnSuccess($message, $with->toArray());
            }
        } elseif ($isError) {
            return $this->returnError($message);
        }

        return $this->returnNull();
    }

    public function get(Request $request, int $limit = 10)
    {
        $shopId = (!empty($data['shop_id'])) ? (int)$data['shop_id'] : NULL;
        $getMassages = CommonHelper::getAllService($request);

        // Get shop details.
        $massageCenters = [];
        if (!empty($shopId)) {
            $shops = Shop::where('id', $shopId)->first();

            if (!empty($shops)) {
                $data['latitude']  = $shops->latitude;
                $data['longitude'] = $shops->longitude;

                $massageCenters = $this->getMassageCenters($data, false);
            }
        }

        return response()->json([
            'code' => 200,
            'msg'  => __($this->successMsg['success.massage.found']),
            'data' => $getMassages,
            'massage_centers' => $massageCenters
        ]);
    }

    public function getMassageCenters(array $data = [], $isApi = true, int $limit = 10)
    {
        $request   = request();
        // $data      = $request->all();
        $latitude  = (!empty($data['latitude'])) ? $data['latitude'] : $request->get('latitude', NULL);
        $longitude = (!empty($data['longitude'])) ? $data['longitude'] : $request->get('longitude', NULL);
        $limit     = (!is_numeric($limit)) ? 10 : $limit;
        $distance  = 45;

        $shopTableName = Shop::getTableName();
        $cityTableName = City::getTableName();
        $query         = Shop::query();

        $selectStatements = "{$shopTableName}.id, {$shopTableName}.name, {$shopTableName}.address, {$shopTableName}.latitude, {$shopTableName}.longitude, (6371 * 2 * ASIN(SQRT(POWER(SIN((37.38714000 - latitude)* PI() / 180 / 2), 2) + COS(37.38714000* PI() / 180) * COS(latitude* PI() / 180) * POWER(SIN((-122.08323500 - longitude) * PI() / 180 / 2) ,2)))) AS distance";
        $whereStatements  = "longitude BETWEEN ({$longitude} - {$distance} / COS(RADIANS({$latitude})) * 69) AND ({$longitude} + {$distance} / COS(RADIANS({$latitude})) *69) AND latitude BETWEEN ({$latitude} - ({$distance} / 69)) AND ({$latitude} + ({$distance} / 69))";

        $query = $query->select(DB::raw($selectStatements))
                       ->leftJoin($cityTableName, $shopTableName . '.city_id', '=', $cityTableName . '.id')
                       ->whereRaw($whereStatements)
                       ->with('centerHours')
                       ->get();

        $returnData = [];

        if (!empty($query) && !$query->isEmpty()) {
            $query->map(function($data, $key) use(&$returnData) {
                $returnData[$key]['id']               = $data->id;
                $returnData[$key]['name']             = $data->name;
                $returnData[$key]['address']          = $data->address;
                $returnData[$key]['latitude']         = $data->latitude;
                $returnData[$key]['longitude']        = $data->longitude;
                $returnData[$key]['total_services']   = $data->totalServices;
                $returnData[$key]['center_hours']     = $data->centerHours;
            });
        }

        if ($isApi) {
            return $this->returns('success.massage.center.found', collect($returnData));
        }

        return $returnData;
    }
}
