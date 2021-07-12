<?php

namespace App\Http\Controllers\Location;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use App\Country;
use App\Province;
use App\City;

class LocationController extends BaseController
{
    public $errorMsg = [
        
    ];

    public $successMsg = [
        'success.country.get' => 'Countries get successfully !',
        'no.data.found' => 'No data found !',
        'success.province.get' => 'Province get successfully !',
        'success.city.get' => 'City get successfully !'
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

    public function getCountry(Request $request)
    {
        $model = new Country();

        /* TODO : To set limit. */

        $data = $model->all();

        if (!empty($data) && !$data->isEmpty()) {
            return $this->returns('success.country.get', $data);
        }

        return $this->returns('no.data.found', collect([]));
    }

    public function getProvince(Request $request)
    {
        $model      = new Province();

        $countryId  = (int)$request->get('country_id', false);

        if ($countryId) {
            $data = $model->where('country_id', $countryId)->get();
        } else {
            $data = $model->all();
        }

        if (!empty($data) && !$data->isEmpty()) {
            return $this->returns('success.province.get', $data);
        }

        return $this->returns('no.data.found', collect([]));
    }

    public function getCity(Request $request)
    {
        $modelCountry   = new Country();
        $modelProvince  = new Province();
        $modelCity      = new City();
        $data           = $request->all();
        
        $countryId  = $request->get('country_id', false);
        $provinceId    = $request->get('province_id', false);
        $search     = $request->has('search') ? $request->search : '';
        $pageNumber = $request->has('page_number') ? $request->page_number : 1;
        $paginate = $request->has('is_paginate') ? true : false;
        $returnData = NULL;

        if(!empty($provinceId)) {
            $returnData = $modelCity->where('province_id', $provinceId);
        } else if(!empty($countryId)) {
            $modelCity->setMysqlStrictFalse();

            $returnData = $modelCity->with('province')->whereHas('province', function($q) use ($countryId) {
                $q->where('country_id', $countryId);
            });
            $modelCity->setMysqlStrictTrue();
        }

        if (!empty($search)) {
            $returnData = $returnData->where('name', 'LIKE', $search . '%');
        }

        if($paginate) {
            $returnData = $returnData->paginate(10, ['*'], 'page', $pageNumber);
        } else {
            $returnData = $returnData->get();
        }

        if (!empty($returnData) && !$returnData->isEmpty()) {
            return $this->returns('success.city.get', $returnData);
        }

        return $this->returns('no.data.found', collect([]));
    }
}
