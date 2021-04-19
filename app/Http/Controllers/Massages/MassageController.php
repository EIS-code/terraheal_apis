<?php

namespace App\Http\Controllers\Massages;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use App\Massage;
use App\Shop;

class MassageController extends BaseController
{
    public $errorMsg = [
        
    ];

    public $successMsg = [
        
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
        $model  = new Massage();
        $data   = $request->all();
        $query  = (!empty($data['q'])) ? $data['q'] : NULL;
        $limit  = (!is_numeric($limit)) ? 10 : $limit;
        $shopId = (!empty($data['shop_id'])) ? (int)$data['shop_id'] : NULL;

        $getMassages = $massage->where("name", "LIKE", "%{$query}%")->with(['timing' => function($qry) {
            $qry->with('pricing');
        }])->limit($limit)->get();

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
            'msg'  => 'Massage found successfully !',
            'data' => $getMassages,
            'massage_centers' => $massageCenters
        ]);
    }
}
