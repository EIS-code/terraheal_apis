<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\BookingInfo;
use App\BookingMassage;
use App\Booking;
use App\MassagePrice;
use DB;
use Carbon\Carbon;
use App\Libraries\CurrencyHelper;

class UserController extends BaseController
{
    protected $currencyHelper;

    public $errorMsg = [
        'error.email'    => 'Please provide email properly.',
        'error.password' => 'Please provide password properly.',
        'error.email.password' => 'User email or password seems wrong.',
        'error.something' => 'Something went wrong.',
        'error.user.id' => 'Please provide valid user id.',
        'error.user.not.found' => 'User not found.'
    ];

    public $successMsg = [
        'success.user.found' => 'User found successfully !',
        'success.user.created' => 'User created successfully !',
        'success.user.profile.update' => 'User profile updated successfully !',
        'success.user.loggedout' => 'User logged out successfully !',
        'success.booking.created' => 'Booking created successfully !'
    ];

    public function __construct()
    {
        $this->currencyHelper = new CurrencyHelper();
    }

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

    public function signIn(Request $request)
    {
        $data     = $request->all();
        $model    = new User();
        $email    = (!empty($data['email'])) ? $data['email'] : NULL;
        $password = (!empty($data['password'])) ? $data['password'] : NULL;

        if (empty($email)) {
            return $this->returns('error.email', NULL, true);
        } elseif (empty($password)) {
            return $this->returns('error.password', NULL, true);
        }

        if (!empty($email) && !empty($password)) {
            $getUser = $model::where('email', $email)->first();

            if (!empty($getUser) && Hash::check($password, $getUser->password)) {
                return $this->returns('success.user.found', $getUser);
            } else {
                return $this->returns('error.email.password', NULL, true);
            }
        }

        return $this->returns('error.something', NULL, true);
    }

    public function signUp(Request $request)
    {
        $data  = $request->all();
        $model = new User();

        DB::beginTransaction();

        try {
            $validator = $model->validator($data);
            if ($validator->fails()) {
                return $this->returns($validator->errors()->first(), NULL, true);
            }

            $data['password'] = (!empty($data['password']) ? Hash::make($data['password']) : NULL);
            $model->fill($data);
            $model->save();

        } catch(Exception $e) {
            DB::rollBack();
        }

        DB::commit();

        return $this->returns('success.user.created', $model);
    }

    public function updateProfile(Request $request)
    {
        $data  = $request->all();
        $model = new User();

        DB::beginTransaction();

        try {
            $now    = Carbon::now();
            $userId = $request->get('user_id', false);

            if (isset($data['password'])) {
                unset($data['password']);
            }

            if (isset($data['user_id'])) {
                unset($data['user_id']);
            }

            $validator = $model->validator($data, $userId, true);
            if ($validator->fails()) {
                return $this->returns($validator->errors()->first(), NULL, true);
            }

            if (empty($userId)) {
                return $this->returns('error.user.id', NULL, true);
            }

            $getUser = $model->find($userId);
            if (empty($getUser)) {
                return $this->returns('error.user.id', NULL, true);
            }

            if (!empty($request->profile_photo)) {
                $validate = $model->validateProfilePhoto($request);
                if ($validate->fails()) {
                    return $this->returns($validator->errors()->first(), NULL, true);
                }
            }

            unset($data['profile_photo']);

            if (!empty($request->profile_photo)) {
                $fileName               = time() . '_' . $userId . '.' . $request->profile_photo->getClientOriginalExtension();
                $storeFile              = $request->profile_photo->storeAs($model->profilePhotoPath, $fileName, $model->fileSystem);
                $data['profile_photo']  = $fileName;
            }

            // Update is_email_verified flag if email got changed.
            if (!empty($data['email']) && ($getUser->email != $data['email'])) {
                $data['is_email_verified'] = '0';
            }

            // Update is_mobile_verified flag if email got changed.
            if ((!empty($data['tel_number']) && $getUser->tel_number != $data['tel_number']) || (!empty($data['tel_number_code']) && $getUser->tel_number_code != $data['tel_number_code'])) {
                $data['is_mobile_verified'] = '0';
            }

            $isUpdate = $model->where('id', $userId)->update($data);
            if ($isUpdate) {
                $user = $model->getGlobalResponse($userId);
            }
        } catch (Exception $e) {
            DB::rollBack();
        }

        DB::commit();

        return $this->returns('success.user.profile.update', $user);
    }

    public function logout(Request $request)
    {
        /* TODO: For complete token. */

        $data       = $request->all();
        $model      = new User();
        $userId     = (!empty($data['user_id'])) ? (int)$data['user_id'] : false;

        $getUser = $model->where('id', '=', $userId)->where('is_removed', '=', $model::$notRemoved)->first();
        if (empty($getUser)) {
            return $this->returns('error.user.not.found', NULL, true);
        }

        return $this->returns('success.user.loggedout', $model->getGlobalResponse($userId));
    }

    public function bookingCreate(Request $request)
    {
        $data                   = $request->all();
        $model                  = new User();
        $modelBookingInfo       = new BookingInfo();
        $modelBookingMassage    = new BookingMassage();
        $modelBooking           = new Booking();
        $modelMassagePrice      = new MassagePrice();
        $now                    = Carbon::now();

        DB::beginTransaction();

        try {
            $data = $this->buildPack($data);

            $validator = $modelBooking->validator($data);
            if ($validator->fails()) {
                return $this->returns($validator->errors()->first(), NULL, true);
            }

            $bookingType = $data['booking_type'];

            $bookingInfos = $data['booking_info'];
            $validator    = $modelBookingInfo->validator($bookingInfos);
            if ($validator->fails()) {
                return $this->returns($validator->errors()->first(), NULL, true);
            }

            $validator = $modelBookingMassage->validator($bookingInfos, true, $bookingType);
            if ($validator->fails()) {
                return $this->returns($validator->errors()->first(), NULL, true);
            }

            if (isset($data['booking_type'])) {
                $data['booking_type'] = (string)$data['booking_type'];
            }
            if (isset($data['bring_table_futon'])) {
                $data['bring_table_futon'] = (string)$data['bring_table_futon'];
            }

            $data['booking_date_time']       = Carbon::createFromTimestampMs($data['booking_date_time'])->format('Y-m-d H:i:s');
            $modelBooking->booking_type      = $data['booking_type'];
            $modelBooking->special_notes     = (!empty($data['special_notes']) ? $data['special_notes'] : NULL);
            $modelBooking->bring_table_futon = (isset($data['bring_table_futon']) ? (string)$data['bring_table_futon'] : $modelBooking::$defaultTableFutons);
            $modelBooking->user_id           = $data['user_id'];
            $modelBooking->shop_id           = $data['shop_id'];

            $modelBooking->fill($data);
            $modelBooking->save();

            $bookingId         = $modelBooking->id;
            $userId            = $data['user_id'];
            $shopId            = $data['shop_id'];
            $massageDate       = Carbon::createFromTimestampMs($data['booking_date_time'])->toDate();
            $massageTime       = Carbon::createFromTimestampMs($data['booking_date_time'])->toDateTime();
            $bookingInfos      = [];
            $shopCurrencyId    = $this->currencyHelper->getDefaultShopCurrency($shopId, true);
            $shopCurrency      = $this->currencyHelper->getCodeFromId($shopCurrencyId);
            $bookingCurrencyId = (!empty($data['currency_id'])) ? $data['currency_id'] : $shopCurrencyId;
            $bookingCurrency   = (!empty($bookingCurrencyId)) ? $this->currencyHelper->getCodeFromId($bookingCurrencyId) : NULL;
            $bookingCurrency   = (empty($bookingCurrency)) ? $shopCurrency : $bookingCurrency;
            $exchangeRate      = $this->currencyHelper->getRate($bookingCurrencyId);
            $bookingMassages   = [];

            foreach ((array)$data['booking_info'] as $index => $infos) {
                if (empty($infos['massage_info'])) {
                    continue;
                }

                $bookingInfos[$index] = [
                    'location'              => $infos['location'],
                    'massage_date'          => $massageDate,
                    'massage_time'          => $massageTime,
                    'imc_type'              => $infos['imc_type'],
                    'booking_currency_id'   => $bookingCurrencyId,
                    'shop_currency_id'      => $shopCurrencyId,
                    'therapist_id'          => $infos['therapist_id'],
                    'booking_id'            => $bookingId,
                    'user_people_id'        => $infos['user_people_id'],
                    'created_at'            => $now
                ];

                $modelBookingInfo->fill($bookingInfos[$index]);
                $modelBookingInfo->save();

                if ($modelBookingInfo) {
                    $bookingInfoId = $modelBookingInfo->id;
                }

                foreach ($infos['massage_info'] as $indexBookingMassage => $massageInfo) {
                    $getMassagePrice = $modelMassagePrice->find($massageInfo['massage_prices_id']);

                    $bookingMassages[$indexBookingMassage] = [
                        'price'                 => $this->currencyHelper->convert($getMassagePrice->price, $exchangeRate, $bookingCurrencyId),
                        'cost'                  => $this->currencyHelper->convert($getMassagePrice->cost, $exchangeRate, $bookingCurrencyId),
                        'origional_price'       => $getMassagePrice->price,
                        'origional_cost'        => $getMassagePrice->cost,
                        'exchange_rate'         => $exchangeRate,
                        'notes_of_injuries'     => $massageInfo['notes_of_injuries'],
                        'pressure_preference'   => $massageInfo['pressure_preference'],
                        'gender_preference'     => $massageInfo['gender_preference'],
                        'focus_area_preference' => $massageInfo['focus_area_preference'],
                        'massage_timing_id'     => $getMassagePrice->massage_timing_id,
                        'massage_prices_id'     => $massageInfo['massage_prices_id'],
                        'booking_info_id'       => $bookingInfoId,
                        'room_id'               => $infos['room_id'],
                        'created_at'            => $now
                    ];
                }

                $modelBookingMassage->insert($bookingMassages);
            }
            
        } catch(Exception $e) {
            DB::rollBack();
        }

        DB::commit();

        return $this->returns('success.booking.created', collect(['booking_id' => $bookingId]));
    }

    public function buildPack(array $data)
    {
        if (isset($data['is_pack']) && $data['is_pack'] == 1) {
            $data['shop_id'] = "";

            $datya['booking_info'] = [
                "user_people_id" => 2,
                "location"       => "Test Location",
                "imc_type"       => 1,
                "therapist_id"   => 2,
                "room_id"        => 1,
                "massage_info"   => [
                    "pressure_preference"   => 3,
                    "gender_preference"     => 5,
                    "focus_area_preference" => 31,
                    "notes_of_injuries"     => "No any injury.",
                    "massage_prices_id"     => 1
                ]
            ];
        }

        return $data;
    }
}
