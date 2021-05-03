<?php

namespace App\Http\Controllers\Massage;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use App\Massage;
use App\Shop;
use App\City;
use App\MassagePreference;
use App\SelectedMassagePreference;
use App\MassagePreferenceOption;
use App\SessionType;
use DB;
use Carbon\Carbon;

class MassageController extends BaseController
{
    public $errorMsg = [
        'error.userid' => 'Please provide valid user_id.',
        'error.data' => 'Data should not be empty and it should be valid.'
    ];

    public $successMsg = [
        'success.massage.found' => 'Massage found successfully !',
        'success.massage.preference.found' => 'Massage preferences found successfully !',
        'success.massage.preference.not.found' => 'Massage preferences not found !',
        'success.massage.preference.created' => 'Massage preference created successfully !',
        'success.massage.center.found' => 'Massage center found successfully !',
        'success.massage.session.found' => 'Massage sessions found successfully !'
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

        $getMassages = $model->where("name", "LIKE", "%{$query}%")->with(['timing' => function($qry) {
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
            'msg'  => __($this->successMsg['success.massage.found']),
            'data' => $getMassages,
            'massage_centers' => $massageCenters
        ]);
    }

    public function getMassageCenters(array $data = [], $isApi = true, int $limit = 10)
    {
        // $request   = request();
        // $data      = $request->all();
        $latitude  = (!empty($data['latitude'])) ? $data['latitude'] : NULL;
        $longitude = (!empty($data['longitude'])) ? $data['longitude'] : NULL;
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

    public function getPreference(Request $request, int $limit = 10)
    {
        $model  = new MassagePreference();
        $data   = $request->all();
        $limit  = (!is_numeric($limit)) ? 10 : $limit;
        $userId = (!empty($data['user_id'])) ? (int)$data['user_id'] : false;
        $type   = (!empty($data['type'])) ? (int)$data['type'] : false;

        $getMassagePreferences = $model->with(['preferenceOptions' => function($qry) use($type) {
            $qry->with('selectedPreferences')
                ->when($type, function($query) use($type) {
                    $query->where('massage_preference_id', $type);
                });
        }])->where('is_removed', '=', $model::$notRemoved)->limit($limit)->get();

        if (!empty($getMassagePreferences) && !$getMassagePreferences->isEmpty()) {
            $getMassagePreferences->map(function($preferences, $index) use($type, $getMassagePreferences) {
                if (!empty($preferences->preferenceOptions) && !$preferences->preferenceOptions->isEmpty()) {
                    $preferences->preferenceOptions->map(function($options) use($type) {
                        $options->selected = false;
                        $options->value    = NULL;
                        if (!empty($options->selectedPreferences)) {
                            $options->selected = true;
                            $options->value    = $options->selectedPreferences->value;
                        }

                        unset($options->selectedPreferences);
                    });
                } else {
                    if ($type) {
                        unset($getMassagePreferences[$index]);
                    }
                }
            });

            return $this->returns('success.massage.preference.found', $getMassagePreferences);
        }

        return $this->returns('success.massage.preference.not.found', collect([]));
    }

    public function createPreference(Request $request)
    {
        DB::beginTransaction();

        try {
            $model  = new SelectedMassagePreference();
            $data   = $request->all();
            $userId = (!empty($data['user_id'])) ? (int)$data['user_id'] : false;
            $data   = (!empty($data['data'])) ? (array)$data['data'] : [];
            $data   = (!isMultidimentional($data)) ? [$data] : $data;
            $now    = Carbon::now();

            if (!$userId) {
                return $this->returns('error.userid', NULL, true);
            }

            if (empty($data)) {
                return $this->returns('error.data', NULL, true);
            }

            $insertData = $matchIds = [];

            foreach ($data as $index => $selected) {
                if (!empty($selected['id'])) {
                    $optionId = (int)$selected['id'];

                    // Get selected option value for first two options.
                    $value = (!empty($selected['value'])) ? (string)$selected['value'] : NULL;
                    if (in_array($optionId, $model->radioOptions)) {
                        $getOption = MassagePreferenceOption::where('id', '=', $optionId)->first();
                        $value     = (!empty($getOption)) ? $getOption->name : NULL;
                    }

                    $matchIds[$index] = [
                        'mp_option_id' => $optionId,
                        'user_id'      => $userId
                    ];

                    $insertData[$index] = [
                        'value'        => $value,
                        'mp_option_id' => $optionId,
                        'user_id'      => $userId,
                        'created_at'   => $now,
                        'updated_at'   => $now,
                        'is_removed'   => $model::$notRemoved
                    ];

                    $validator = $model->validator($insertData[$index]);
                    if ($validator->fails()) {
                        return $this->returns($validator->errors()->first(), NULL, true);
                    }

                    // Remove old selections.
                    $this->removeOldSelections($userId, $optionId);

                    $selectedMassagePreference = $model->updateOrCreate($matchIds[$index], $insertData[$index]);
                }
            }
        } catch (Exception $e) {
            DB::rollBack();
        }

        DB::commit();

        return $this->returns('success.massage.preference.created', collect([]));
    }

    public function removeOldSelections(int $userId, int $optionId)
    {
        $model          = new SelectedMassagePreference();

        $optionGroups   = inArrayRecursive($optionId, $model->optionGroups);

        if (!empty($optionGroups)) {
            return $model->where('user_id', '=', $userId)->whereIn('mp_option_id', $optionGroups)->update(['is_removed' => $model::$removed]);
        }

        return false;
    }

    public function getMassageSessions(Request $request)
    {
        $model          = new SessionType();
        $data           = $request->all();
        $bookingType    = '0';

        if (isset($data['booking_type']) && in_array((string)$data['booking_type'], $model::$bookingTypes)) {
            $bookingType = (string)$data['booking_type'];
        }

        $getSessionTypes = $model::where('booking_type', $bookingType)->get();

        return $this->returns('success.massage.session.found', $getSessionTypes);
    }
}
