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
use App\UserSetting;
use App\UserEmailOtp;
use App\UserAddress;
use App\UserPeople;
use App\UserGenderPreference;
use App\TherapistReview;
use App\UserMenu;
use App\UserGuide;
use App\UserGiftVoucher;
use App\UserGiftVoucherInfo;
use App\UserGiftVoucherTheme;
use App\UserFaq;
use App\UserPack;
use App\UserPackOrder;
use App\UserPackMassage;
use App\UserPackGift;
use App\UserFavoriteService;
use DB;
use Carbon\Carbon;
use App\Libraries\CurrencyHelper;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UserController extends BaseController
{
    protected $currencyHelper;

    public $errorMsg = [
        'error.email'    => 'Please provide email properly.',
        'error.password' => 'Please provide password properly.',
        'error.email.password' => 'User email or password seems wrong.',
        'error.something' => 'Something went wrong.',
        'error.user.id' => 'Please provide valid user id.',
        'error.user.not.found' => 'User not found.',
        'error.old.password' => 'Please provide valid old password.',
        'error.new.password' => 'Please provide valid new password.',
        'error.old.new.same' => 'You can\'t use old password. Please insert new one.',
        'error.old.password.wrong' => 'Old password seems wrong.',
        'error.email.already.verified' => 'This user email already verified with this ',
        'error.email.id' => ' email id.',
        'error.otp' => 'Please provide OTP properly.',
        'error.otp.wrong' => 'OTP seems wrong.',
        'error.otp.already.verified' => 'OTP already verified.',
        'error.user.address.not.found' => 'User address not found.',
        'error.user.people.not.found' => 'User people not found.',
        'error.proper.id' => 'Please provide valid id.',
        'error.booking.therapists.not.found' => 'Booking therapists not found.',
        'error.booking.places.not.found' => 'Booking places not found.',
        'error.booking.not.found' => 'Booking not found.',
        'error.provide.menu.id' => 'Please provide menu id.',
        'error.user.document.found' => 'Document not found.',
        'error.user.favorite.provide.serviceid' => 'Please provide proper service id.',
        'error.user.favorite.serviceid.not.found' => 'User favorite not found.'
    ];

    public $successMsg = [
        'success.user.found' => 'User found successfully !',
        'success.user.created' => 'User created successfully !',
        'success.user.profile.update' => 'User profile updated successfully !',
        'success.user.loggedout' => 'User logged out successfully !',
        'success.booking.created' => 'Booking created successfully !',
        'success.password.updated' => 'User password updated successfully !',
        'success.user.setting.found' => 'User setting found successfully !',
        'success.user.setting.not.found' => 'User setting not found.',
        'success.user.setting.created' => 'User setting created successfully !',
        'success.sms.sent' => 'SMS sent successfully !',
        'success.email.sent' => 'Email sent successfully !',
        'success.email.otp.compare' => 'OTP matched successfully !',
        'success.user.address.found' => 'User address found successfully.',
        'success.user.address.created' => 'User address created successfully !',
        'success.user.address.updated' => 'User address updated successfully !',
        'success.user.address.removed' => 'User address removed successfully.',
        'success.user.people.found' => 'User people found successfully.',
        'success.user.people.created' => 'User people created successfully !',
        'success.user.people.updated' => 'User people updated successfully !',
        'success.user.people.removed' => 'User people removed successfully !',
        'success.booking.therapists.found' => 'Booking therapists found successfully !',
        'success.booking.places.found' => 'Booking places found successfully !',
        'success.booking.found' => 'Booking found successfully !',
        'success.therapist.review.created' => 'Therapist review created successfully !',
        'success.user.menu.found' => 'User menu found successfully !',
        'success.user.menu.not.found' => 'User menu not found !',
        'success.user.menu.item.found' => 'User menu item found successfully !',
        'success.user.menu.item.not.found' => 'User menu item found !',
        'success.user.gift.voucher.found' => 'User gift voucher found successfully !',
        'success.user.gift.voucher.not.found' => 'User gift voucher not found !',
        'success.user.gift.voucher.info.found' => 'User gift voucher info found successfully !',
        'success.user.gift.voucher.info.not.found' => 'User gift voucher info not found !',
        'success.user.gift.voucher.created' => 'User gift voucher created successfully !',
        'success.user.gift.voucher.design.found' => 'User gift voucher designs found successfully !',
        'success.user.gift.voucher.design.not.found' => 'User gift voucher design not found !',
        'success.user.faq.found' => 'User FAQ found successfully !',
        'success.user.faq.not.found' => 'User FAQ not found !',
        'success.user.packs.found' => 'User packs found successfully !',
        'success.user.packs.not.found' => 'User pack not found !',
        'success.user.packs.services.found' => 'User pack services found successfully !',
        'success.user.packs.service.not.found' => 'User pack service not found !',
        'success.user.pack.ordered' => 'User pack ordered successfully !',
        'success.user.pack.gift.created' => 'User pack gift created successfully !',
        'success.user.qr.matched' => 'User QR code matched !',
        'success.user.qr.matched.not.matched' => 'User QR code does not matched !',
        'success.user.document.updated' => 'User document updated successfully !',
        'success.user.document.removed' => 'User document removed successfully !',
        'success.user.favorite.created' => 'User favorite created successfully !',
        'success.user.favorite.removed' => 'User favorite removed successfully !',
        'success.user.favorite.found' => 'User favorite found successfully !',
        'success.user.favorite.not.found' => 'User favorite not found !'
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

    public function updatePassword(Request $request)
    {
        $data   = $request->all();
        $model  = new User();

        DB::beginTransaction();

        try {
            $userId      = (!empty($data['user_id'])) ? (int)$data['user_id'] : false;
            $oldPassword = (!empty($data['old_password'])) ? $data['old_password'] : NULL;
            $newPassword = (!empty($data['new_password'])) ? $data['new_password'] : NULL;

            if (!$userId) {
                return $this->returns('error.user.id', NULL, true);
            }

            if (empty($oldPassword)) {
                return $this->returns('error.old.password', NULL, true);
            }

            if (empty($newPassword)) {
                return $this->returns('error.new.password', NULL, true);
            }

            if ($oldPassword === $newPassword) {
                return $this->returns('error.old.new.same', NULL, true);
            }

            $getUser = $model->where('id', '=', $userId)->where('is_removed', '=', $model::$notRemoved)->first();

            if (empty($getUser)) {
                return $this->returns('error.user.not.found', NULL, true);
            }

            if (Hash::check($oldPassword, $getUser['password'])) {
                $validator = $model->validator(['password' => $newPassword], $userId, true);
                if ($validator->fails()) {
                    return $this->returns($validator->errors()->first(), NULL, true);
                }

                $update = $model->where('id', '=', $userId)->where('is_removed', '=', $model::$notRemoved)->update(['password' => Hash::make($newPassword)]);
            } else {
                return $this->returns('error.old.password.wrong', NULL, true);
            }

        } catch (Exception $e) {
            DB::rollBack();
        }

        DB::commit();

        return $this->returns('success.password.updated', collect([]));
    }

    public function getUserSettings(Request $request)
    {
        $data   = $request->all();
        $model  = new UserSetting();
        $userId = $request->get('user_id', false);

        $data   = $model->getGlobalResponse($userId);

        if (!empty($data)) {
            return $this->returns('success.user.setting.found', $data);
        } else {
            return $this->returns('success.user.setting.not.found', collect([]));
        }
    }

    public function saveUserSettings(Request $request)
    {
        $data   = $request->all();
        $model  = new UserSetting();

        DB::beginTransaction();

        try {
            $userId      = (!empty($data['user_id'])) ? (int)$data['user_id'] : false;
            $settingData = [];

            if (!$userId) {
                return $this->returns('error.user.id', NULL, true);
            }

            $validator = $model->validator($data);
            if ($validator->fails()) {
                return $this->returns($validator->errors()->first(), NULL, true);
            }

            $settingData = $model->where('user_id', $userId)->where('is_removed', '=', $model::$notRemoved)->get();

            if (!empty($settingData) && !$settingData->isEmpty()) {
                $model->where('user_id', $userId)->where('is_removed', '=', $model::$notRemoved)->update($data);
            } else {
                $model->fill($data);
                $model->save();
            }
        } catch(Exception $e) {
            DB::rollBack();
        }

        DB::commit();

        return $this->returns('success.user.setting.created', $model->getGlobalResponse($userId));
    }

    public function verifyMobile(Request $request)
    {
        $data   = $request->all();
        $model  = new UserSetting();

        /* TODO all things like email otp after get sms gateway. */

        return $this->returns('success.sms.sent', collect([]));
    }

    public function verifyEmail(Request $request)
    {
        $data           = $request->all();
        $model          = new User();
        $modelEmailOtp  = new UserEmailOtp();

        $id      = (!empty($data['user_id'])) ? $data['user_id'] : 0;
        $getUser = $model->find($id);

        if (!empty($getUser)) {
            $emailId = (!empty($data['email'])) ? $data['email'] : NULL;

            // Validate
            $data = [
                'user_id'      => $id,
                'otp'          => 1434,
                'email'        => $emailId,
                'is_send'      => '0'
            ];

            $validator = $modelEmailOtp->validate($data);
            if ($validator['is_validate'] == '0') {
                return $this->returns($validator['msg'], NULL, true);
            }

            if ($emailId == $getUser->email && $getUser->is_email_verified == '1') {
                $this->errorMsg['error.email.already.verified'] = $this->errorMsg['error.email.already.verified'] . $emailId . $this->errorMsg['error.email.id'];

                return $this->returns('error.email.already.verified', NULL, true);
            }

            /* $validate = (filter_var($emailId, FILTER_VALIDATE_EMAIL) && preg_match('/@.+\./', $emailId));
            if (!$validate) {
                $this->errorMsg[] = "Please provide valid email id.";
            } */

            $sendOtp         = $this->sendOtp($emailId);
            $data['otp']     = NULL;
            $data['is_send'] = '0';

            if ($this->getJsonResponseCode($sendOtp) == '200') {
                $data['is_send']     = '1';
                $data['is_verified'] = '0';
                $data['otp']         = $this->getJsonResponseOtp($sendOtp);
            } else {
                return $this->returns($this->getJsonResponseMsg($sendOtp), NULL, true);
            }

            $getData = $modelEmailOtp->where(['user_id' => $id])->get();

            if (!empty($getData) && !$getData->isEmpty()) {
                $updateOtp = $modelEmailOtp->updateOtp($id, $data);

                if (!empty($updateOtp['isError']) && !empty($updateOtp['message'])) {
                    return $this->returns($updateOtp['message'], NULL, true);
                }
            } else {
                $create = $modelEmailOtp->create($data);

                if (!$create) {
                    return $this->returns('error.something', NULL, true);
                }
            }
        } else {
            return $this->returns('error.user.id', NULL, true);
        }

        return $this->returns('success.email.sent', collect([]));
    }

    public function compareOtpEmail(Request $request)
    {
        $data       = $request->all();
        $model      = new UserEmailOtp();
        $modelUser  = new User();

        $userId = (!empty($data['user_id'])) ? $data['user_id'] : 0;
        $otp    = (!empty($data['otp'])) ? $data['otp'] : NULL;

        if (empty($otp)) {
            return $this->returns('error.otp', NULL, true);
        }

        if (strtolower(env('APP_ENV') != 'live') && $otp == '1234') {
            $getUser = $model->where(['user_id' => $userId])->get();
        } else {
            $getUser = $model->where(['user_id' => $userId, 'otp' => $otp])->get();
        }

        if (!empty($getUser) && !$getUser->isEmpty()) {
            $getUser = $getUser->first();

            if ($getUser->is_verified == '1') {
                return $this->returns('error.otp.already.verified', NULL, true);
            } else {
                $modelUser->where(['id' => $userId])->update(['email' => $getUser->email, 'is_email_verified' => '1']);

                $model->setIsVerified($getUser->id, '1');
            }
        } else {
            return $this->returns('error.otp.wrong', NULL, true);
        }

        return $this->returns('success.email.otp.compare', collect([]));
    }

    public function compareOtpSms(Request $request)
    {
        $data   = $request->all();
        $model  = new User();

        /* TODO all things like email otp compare after get sms gateway. */
        $userId = (!empty($data['user_id'])) ? $data['user_id'] : 0;
        $otp    = (!empty($data['otp'])) ? $data['otp'] : NULL;

        if (strtolower(env('APP_ENV') != 'live') && $otp == '1234') {
            $model->where(['id' => $userId])->update(['is_mobile_verified' => '1']);
        } else {
            return $this->returns('error.otp.wrong', NULL, true);
        }

        return $this->returns('success.email.otp.compare', collect([]));
    }

    public function getDetails(Request $request)
    {
        $data   = $request->all();
        $model  = new User();
        $userId = $request->get('user_id', false);

        $data   = $model->getGlobalResponse($userId);

        if (!empty($data)) {
            return $this->returns('success.user.found', $data);
        }

        return $this->returns('error.user.not.found', NULL, true);
    }

    public function getAddress(Request $request)
    {
        $model       = new UserAddress();
        $id          = (int)$request->get('user_id', false);

        $userAddress = $model->where('user_id', $id)->where('is_removed', $model::$notRemoved)->get();

        if (!empty($userAddress) && !$userAddress->isEmpty()) {
            return $this->returns('success.user.address.found', $userAddress);
        }

        return $this->returns('error.user.address.not.found', NULL, true);
    }

    public function createAddress(Request $request)
    {
        $model = new UserAddress();
        $data  = $request->all();

        DB::beginTransaction();

        try {
            $validator = $model->validator($data);
            if ($validator->fails()) {
                return $this->returns($validator->errors()->first(), NULL, true);
            }

            $model->fill($data);
            $model->save();
        } catch(Exception $e) {
            DB::rollBack();
        }

        DB::commit();

        return $this->returns('success.user.address.created', $model);
    }

    public function updateAddress(Request $request)
    {
        $model = new UserAddress();
        $data  = $request->all();

        if (isset($data['user_id'])) {
            unset($data['user_id']);
        }

        $id              = (!empty($data['id'])) ? (int)$data['id'] : false;
        $update          = false;
        $findUserAddress = $model->find($id);

        if (!empty($findUserAddress)) {
            DB::beginTransaction();

            try {
                $validator = $model->validator($data, true);
                if ($validator->fails()) {
                    return $this->returns($validator->errors()->first(), NULL, true);
                }

                $update = $model->where('id', $id)->update($data);
            } catch(Exception $e) {
                DB::rollBack();
            }

            if ($update) {
                DB::commit();
                return $this->returns('success.user.address.updated', $findUserAddress->refresh());
            } else {
                return $this->returns('error.something', NULL, true);
            }
        }

        return $this->returns('error.user.address.not.found', NULL, true);
    }

    public function removeAddress(Request $request)
    {
        $model = new UserAddress();
        $id    = (int)$request->get('id', false);

        if (!empty($id)) {
            $userAddress = $model->find($id);

            if (!empty($userAddress)) {
                $userAddress->is_removed = $model::$removed;

                if ($userAddress->save()) {
                    return $this->returns('success.user.address.removed', collect([]));
                }
            }
        }

        return $this->returns('error.user.address.not.found', NULL, true);
    }

    public function getPeople(Request $request)
    {
        $model      = new UserPeople();
        $id         = (int)$request->get('user_id', false);

        $userPeople = $model->where('user_id', $id)->where('is_removed', (string)$model::$notRemoved)->get();

        // Get user gender preference.
        $userGenderPreference = UserGenderPreference::where('is_removed', UserGenderPreference::$notRemoved)->get();

        if (!empty($userPeople) && !$userPeople->isEmpty()) {
            return response()->json([
                'code'               => 200,
                'msg'                => __($this->successMsg['success.user.people.found']),
                'data'               => $userPeople,
                'gender_preferences' => $userGenderPreference
            ]);
        }

        $code = 401;
        if (!empty($userGenderPreference) && !$userGenderPreference->isEmpty()) {
            $code = 200;
        }

        return response()->json([
            'code'               => $code,
            'msg'                => __($this->errorMsg['error.user.people.not.found']),
            'data'               => $userPeople,
            'gender_preferences' => $userGenderPreference
        ]);
    }

    public function createPeople(Request $request)
    {
        $model = new UserPeople();
        $data  = $request->all();

        DB::beginTransaction();

        try {
            $validator = $model->validator($data);
            if ($validator->fails()) {
                return $this->returns($validator->errors()->first(), NULL, true);
            }

            if (!empty($request->photo)) {
                $validate = $model->validatePhoto($request);
                if ($validate->fails()) {
                    return $this->returns($validator->errors()->first(), NULL, true);
                }

                unset($data['photo']);
            }

            $model->fill($data);
            $model->save();

            $userPeopleId = $model->id;

            if (!empty($request->photo)) {
                $fileName      = time() . '_' . $userPeopleId . '.' . $request->photo->getClientOriginalExtension();
                $storeFile     = $request->photo->storeAs($model->photoPath, $fileName, $model->fileSystem);

                $model->update(['photo' => $fileName]);
            }

        } catch(Exception $e) {
            DB::rollBack();
        }

        DB::commit();

        return $this->returns('success.user.people.created', $model);
    }

    public function updatePeople(Request $request)
    {
        $model = new UserPeople();
        $data  = $request->all();
        $id    = (int)$request->get('id', false);

        DB::beginTransaction();

        try {
            if (isset($data['user_id'])) {
                unset($data['user_id']);
            }

            $validator = $model->validator($data, true);
            if ($validator->fails()) {
                return $this->returns($validator->errors()->first(), NULL, true);
            }

            if (empty($id)) {
                return $this->returns('error.proper.id', NULL, true);
            }

            $getUserPeople = $model->find($id);
            if (empty($getUserPeople)) {
                return $this->returns('error.proper.id', NULL, true);
            }

            if (!empty($request->photo)) {
                $validate = $model->validatePhoto($request);
                if ($validate->fails()) {
                    return $this->returns($validate->errors()->first(), NULL, true);
                }

                unset($data['photo']);
            }

            $update = $model->where('id', $id)->update($data);

            if ($update && !empty($request->photo)) {
                $fileName      = time() . '_' . $id . '.' . $request->photo->getClientOriginalExtension();
                $storeFile     = $request->photo->storeAs($model->photoPath, $fileName, $model->fileSystem);

                $model->where('id', $id)->update(['photo' => $fileName]);
            }

        } catch (Exception $e) {
            DB::rollBack();
        }

        if ($update) {
            DB::commit();

            $userPeople = $model->find($id);

            return $this->returns('success.user.people.updated', $userPeople);
        } else {
            return $this->returns('error.something', NULL, true);
        }

        return $this->returns('error.user.people.not.found', NULL, true);
    }

    public function removePeople(Request $request)
    {
        $model = new UserPeople();
        $id    = (int)$request->get('id', false);

        if (!empty($id)) {
            $userPeople = $model->find($id);

            if (!empty($userPeople)) {
                $userPeople->is_removed = $model::$removed;

                if ($userPeople->save()) {
                    return $this->returns('success.user.people.removed', collect([]));
                }
            }
        }

        return $this->returns('error.user.people.not.found', NULL, true);
    }

    public function getBookingTherapists(Request $request)
    {
        $model  = new Booking();
        $userId = (int)$request->get('user_id', false);

        if (!empty($userId)) {
            $response = [];

            $bookings = $model->where('user_id', (int)$userId)->get();

            if (!empty($bookings) && !$bookings->isEmpty()) {
                foreach ($bookings as $key => $booking) {
                    if (!empty($booking->bookingInfo)) {
                        foreach ($booking->bookingInfo as $bookingInfo) {
                            if (!empty($bookingInfo->therapist)) {
                                $therapistId = $bookingInfo->therapist->id;

                                $response[$therapistId] = $bookingInfo->therapist;
                            }
                        }
                    }
                }
            }

            if (!empty($response)) {
                return $this->returns('success.booking.therapists.found', collect(array_values($response)));
            }
        }

        return $this->returns('error.booking.therapists.not.found', NULL, true);
    }

    public function getBookingPlaces(Request $request)
    {
        $model  = new Booking();
        $userId = (int)$request->get('user_id', false);

        if (!empty($userId)) {
            $model->setMysqlStrictFalse();

            $response = [];
            $bookings = $model->where('user_id', (int)$userId)->groupBy('shop_id')->get();

            if (!empty($bookings) && !$bookings->isEmpty()) {
                foreach ($bookings as $key => $booking) {
                    if (!empty($booking->shop)) {
                        $booking->shop->total_services = $booking->shop->massages->count();

                        $response[] = $booking->shop;
                    }
                }
            }

            $model->setMysqlStrictTrue();

            if (!empty($response)) {
                return $this->returns('success.booking.places.found', collect($response));
            }
        }

        return $this->returns('error.booking.places.not.found', NULL, true);
    }

    public function getPastBooking(Request $request)
    {
        $userId = (int)$request->get('user_id', false);

        $model  = new Booking();

        $data   = $model->getWherePastFuture($userId, true, false, false);

        if (!empty($data)) {
            return $this->returns('success.booking.found', collect($data));
        }

        return $this->returns('error.booking.not.found', NULL, true);
    }

    public function getFutureBooking(Request $request)
    {
        $userId = (int)$request->get('user_id', false);

        $model  = new Booking();

        $data   = $model->getWherePastFuture($userId, false, true, false);

        if (!empty($data)) {
            return $this->returns('success.booking.found', collect($data));
        }

        return $this->returns('error.booking.not.found', NULL, true);
    }

    public function setTherapistReviews(Request $request)
    {
        $model = new TherapistReview();
        $data  = $request->all();

        if (!empty($data)) {
            $validator = $model->validator($data);
            if ($validator->fails()) {
                return $this->returns($validator->errors()->first(), NULL, true);
            }

            $data['rating'] = (float)$data['rating'];

            $model->fill($data);

            if ($model->save()) {
                return $this->returns('success.therapist.review.created', collect([]));
            }
        }

        return $this->returns('error.something', NULL, true);
    }

    public function getMenus(Request $request)
    {
        $data = UserMenu::all();

        if (!empty($data) && !$data->isEmpty()) {
            return $this->returns('success.user.menu.found', $data);
        }

        return $this->returns('success.user.menu.not.found', collect([]));
    }

    public function getMenuItem(Request $request)
    {
        $data = $request->all();

        if (empty($data['menu_id'])) {
            return $this->returns('error.provide.menu.id', NULL, true);
        }

        $menuId = (int)$data['menu_id'];


        if ($menuId == 1) {
            $data = UserGuide::all();

            if (!empty($data) && !$data->isEmpty()) {
                return $this->returns('success.user.menu.item.found', $data);
            }

            return $this->returns('success.user.menu.item.not.found', collect([]));
        }
    }

    public function getGiftVouchers(Request $request)
    {
        $model  = new UserGiftVoucher();
        $userId = $request->get('user_id', false);

        if (!empty($userId)) {
            $data = $model->where('user_id', $userId)->get();

            if (!empty($data) && !$data->isEmpty()) {
                $data->map(function($value) {
                    $value->start_from = $value->created_at;
                    $value->last_date  = date("Y-m-d", strtotime(date("Y-m-d", strtotime($value->created_at)) . " + ".GIFT_VOUCHER_LIMIT." days"));
                });

                return $this->returns('success.user.gift.voucher.found', $data);
            }
        }

        return $this->returns('success.user.gift.voucher.not.found', collect([]));
    }

    public function getGiftVoucherInfos()
    {
        $model      = new UserGiftVoucherInfo();

        $getInfos   = $model->all();

        if (!empty($getInfos) && !$getInfos->isEmpty()) {
            return $this->returns('success.user.gift.voucher.info.found', $getInfos);
        }

        return $this->returns('success.user.gift.voucher.info.not.found', collect([]));
    }

    public function saveGiftVouchers(Request $request)
    {
        $model  = new UserGiftVoucher();
        $data   = $request->all();

        DB::beginTransaction();

        try {
            $uniqueId = mt_rand(10000000,99999999);

            // Check exists.
            $check = $model->where('unique_id', $uniqueId)->first();
            if (!empty($check)) {
                $uniqueId = mt_rand(10000000,99999999);
            }

            $data['unique_id'] = $uniqueId;

            if (!empty($data['preference_email_date'])) {
                $emailDate = $data['preference_email_date'] = date("Y-m-d", ($data['preference_email_date'] / 1000));
            }

            if (!empty($data['amount'])) {
                $data['amount'] = (float)$data['amount'];
            }

            $validator = $model->validator($data);
            if ($validator->fails()) {
                return $this->returns($validator->errors()->first(), NULL, true);
            }

            $model->fill($data);
            $save = $model->save();

            if ($save) {
                $today     = strtotime(date('Y-m-d'));
                $emailDate = strtotime($emailDate);

                if ($today == $emailDate) {
                    // Send Email.
                } else {
                    // Set console command for send email for the future date.
                }
            }
        } catch(Exception $e) {
            DB::rollBack();
        }

        DB::commit();

        return $this->returns('success.user.gift.voucher.created', $model);
    }

    public function getGiftVoucherDesigns()
    {
        $model      = new UserGiftVoucherTheme();

        $getDesigns = $model->with('designs')->get();

        if (!empty($getDesigns) && !$getDesigns->isEmpty()) {
            return $this->returns('success.user.gift.voucher.design.found', $getDesigns);
        }

        return $this->returns('success.user.gift.voucher.design.not.found', collect([]));
    }

    public function getFaqs()
    {
        $model  = new UserFaq();

        $data   = $model->all();

        if (!empty($data) && !$data->isEmpty()) {
            return $this->returns('success.user.faq.found', $data);
        }

        return $this->returns('success.user.faq.not.found', collect([]));
    }

    public function getPacks(Request $request)
    {
        $model              = new UserPack();
        $modelUserPackOrder = new UserPackOrder();
        $data               = $request->all();

        if (!empty($data['shop_id'])) {
            $shopId = (int)$data['shop_id'];

            return $model->where('shop_id', $shopId)->get();
        } elseif (!empty($data['user_id'])) {
            $return = [];
            $userId = (int)$data['user_id'];

            $getPacks = $modelUserPackOrder::with('userPack')->where('user_id', $userId)->get();

            if (!empty($getPacks) && !$getPacks->isEmpty()) {
                $getPacks->map(function($userPack) use(&$return) {
                    $return[] = $userPack->userPack;
                });
            }

            if (!empty($return)) {
                return $this->returns('success.user.packs.found', collect($return));
            }
        }

        return $this->returns('success.user.packs.not.found', collect([]));
    }

    public function getPackServices(Request $request)
    {
        $model      = new UserPackMassage();
        $data       = $request->all();
        $userPackId = (!empty($data['user_pack_id'])) ? (int)$data['user_pack_id'] : false;

        if (!empty($userPackId)) {
            $return      = [];
            $getMassages = $model->where('user_pack_id', $userPackId)->get();

            if (!empty($getMassages) && !$getMassages->isEmpty()) {
                $getMassages->map(function($getMassage) use(&$return) {
                    if (!empty($getMassage->massagePrice) && !empty($getMassage->massagePrice->massage) && !empty($getMassage->massagePrice->timing)) {
                        $massage = $getMassage->massagePrice->massage;
                        $timing  = $getMassage->massagePrice->timing;

                        $return[] = [
                            'name'  => $massage->name,
                            'time'  => $timing->time,
                            'image' => $massage->image
                        ];
                    }
                });
            }

            if (!empty($return)) {
                return $this->returns('success.user.packs.services.found', collect($return));
            }
        }

        return $this->returns('success.user.packs.service.not.found', collect([]));
    }

    public function savePackOrders(Request $request)
    {
        $model  = new UserPackOrder();
        $data   = $request->all();

        DB::beginTransaction();

        try {
            $validator = $model->validator($data);
            if ($validator->fails()) {
                return $this->returns($validator->errors()->first(), NULL, true);
            }

            $model->fill($data);
            $model->save();
        } catch(Exception $e) {
            DB::rollBack();
        }

        DB::commit();

        return $this->returns('success.user.pack.ordered', $userPackOrder);
    }

    public function savePackGifts(Request $request)
    {
        $model  = new UserPackGift();
        $data   = $request->all();

        DB::beginTransaction();

        try {
            if (!empty($data['preference_email_date'])) {
                $emailDate = $data['preference_email_date'] = date("Y-m-d", ($data['preference_email_date'] / 1000));
            }

            $validator = $model->validator($data);
            if ($validator->fails()) {
                return $this->returns($validator->errors()->first(), NULL, true);
            }

            $model->fill($data);
            $save = $model->save();

            if ($save) {
                $today     = strtotime(date('Y-m-d'));
                $emailDate = strtotime($emailDate);

                if ($today == $emailDate) {
                    // Send Email.
                } else {
                    // Set console command for send email for the future date.
                }
            }
        } catch(Exception $e) {
            DB::rollBack();
        }

        DB::commit();

        return $this->returns('success.user.pack.gift.created', $userPackGift);
    }

    public function isOurQRCode(Request $request)
    {
        $json = [];

        if ($request->has('id')) {
            $json['id'] = $request->get('id');
        }

        if ($request->has('dob')) {
            $json['dob'] = $request->get('dob');
        }

        if ($request->has('email')) {
            $json['email'] = $request->get('email');
        }

        if ($request->has('shop_id')) {
            $json['shop_id'] = $request->get('shop_id');
        }

        if ($request->has('terraheal_flag')) {
            $json['terraheal_flag'] = $request->get('terraheal_flag');
        }

        $json       = json_encode($json);

        $isMatched  = User::isOurQRCode($json);

        if ($isMatched) {
            $message = __($this->successMsg['success.user.qr.matched']);
        } else {
            $message = __($this->successMsg['success.user.qr.matched.not.matched']);
        }

        $code = $isMatched ? $this->successCode : $this->errorCode;

        return response()->json([
            'code' => $code,
            'msg'  => $message,
            'data' => $isMatched
        ]);
    }

    public function checkQRCode(Request $request)
    {
        $json = [];

        if ($request->has('id')) {
            $json['id'] = $request->get('id');
        }

        if ($request->has('dob')) {
            $json['dob'] = $request->get('dob');
        }

        if ($request->has('email')) {
            $json['email'] = $request->get('email');
        }

        if ($request->has('shop_id')) {
            $json['shop_id'] = $request->get('shop_id');
        }

        if ($request->has('terraheal_flag')) {
            $json['terraheal_flag'] = $request->get('terraheal_flag');
        }

        if ($request->has('booking_id')) {
            $json['booking_id'] = $request->get('booking_id');
        }

        $json       = json_encode($json);

        $isChecked  = User::checkQRCode($json);

        if ($isChecked) {
            $message = __($this->successMsg['success.user.qr.matched']);
        } else {
            $message = __($this->successMsg['success.user.qr.matched.not.matched']);
        }

        $code = $isChecked ? $this->successCode : $this->errorCode;

        return response()->json([
            'code' => $code,
            'msg'  => $message,
            'data' => $isChecked
        ]);
    }

    public function updateDocument(Request $request)
    {
        $data   = $request->all();
        $model  = new User();
        $userId = (int)$request->get('user_id', false);

        if (!empty($userId)) {
            $user = $model->find($userId);

            if (!empty($user)) {
                $idPassportFront = $request->file('id_passport_front', []);

                if (!empty($idPassportFront) && $idPassportFront instanceof UploadedFile) {
                    $checkMime = $model->checkMimeTypes($request, $idPassportFront, 'jpeg,png,jpg');
                    if ($checkMime->fails()) {
                        return $this->returns($checkMime->errors()->first(), NULL, true);
                    }

                    $fileName                   = time() . '_' . $userId . '.' . $idPassportFront->getClientOriginalExtension();
                    $storeFile                  = $idPassportFront->storeAs($model->idPassportPath, $fileName, $model->fileSystem);
                    $user->id_passport_front    = $storeFile ? $fileName : NULL;
                }

                $idPassportBack = $request->file('id_passport_back', []);

                if (!empty($idPassportBack) && $idPassportBack instanceof UploadedFile) {
                    $checkMime = $model->checkMimeTypes($request, $idPassportBack, 'jpeg,png,jpg');
                    if ($checkMime->fails()) {
                        return $this->returns($checkMime->errors()->first(), NULL, true);
                    }

                    $fileName                   = time() . '_' . $userId . '.' . $idPassportBack->getClientOriginalExtension();
                    $storeFile                  = $idPassportBack->storeAs($model->idPassportPath, $fileName, $model->fileSystem);
                    $user->id_passport_back     = $storeFile ? $fileName : NULL;
                }

                if ($user->save()) {
                    return $this->returns('success.user.document.updated', $model->getGlobalResponse($userId));
                }

                return $this->returns('error.something', NULL, true);
            }
        }

        return $this->returns('error.user.not.found', NULL, true);
    }

    public function removeDocument(Request $request)
    {
        $model  = new User();
        $userId = (int)$request->get('user_id', false);

        if (!empty($userId)) {
            $user = $model->find($userId);

            if (!empty($user)) {
                $document = $request->get('document', NULL);

                if ($document == "id_passport_front") {
                    $delete = Storage::disk($model->fileSystem)->delete($model->idPassportPath . $user->getAttributes()[$document]);

                    if ($delete) {
                        $user->{$document} = NULL;
                    }
                } elseif ($document == "id_passport_back") {
                    $delete = Storage::disk($model->fileSystem)->delete($model->idPassportPath . $user->getAttributes()[$document]);

                    if ($delete) {
                        $user->{$document} = NULL;
                    }
                } else {
                    return $this->returns('error.user.document.found', NULL, true);
                }

                if ($user->save()) {
                    return $this->returns('success.user.document.removed', $model->getGlobalResponse($userId));
                }
            }
        }

        return $this->returns('error.user.not.found', NULL, true);
    }

    public function saveFavorite(Request $request)
    {
        $model      = new UserFavoriteService();
        $modelUser  = new User();
        $serviceId  = (int)$request->get('service_id', false);
        $type       = (string)$request->get('type', $model::TYPE_MASSAGE);
        $userId     = (int)$request->get('user_id', false);

        $data       =   [
                            'service_id' => $serviceId,
                            'type'       => $type,
                            'user_id'    => $userId
                        ];

        $validator = $model->validator($data);
        if ($validator->fails()) {
            return $this->returns($validator->errors()->first(), NULL, true);
        }

        if ($model->checkServiceIdExists($serviceId, $type)) {
            $create = $model->updateOrCreate($data);

            if ($create) {
                return $this->returns('success.user.favorite.created', $modelUser->getGlobalResponse($userId));
            }
        } else {
            return $this->returns('error.user.favorite.provide.serviceid', NULL, true);
        }

        return $this->returns('error.something', NULL, true);
    }

    public function removeFavorite(Request $request)
    {
        $model      = new UserFavoriteService();
        $modelUser  = new User();
        $serviceId  = (int)$request->get('service_id', false);
        $type       = (string)$request->get('type', false);
        $userId     = (int)$request->get('user_id', false);

        if (!empty($serviceId)) {
            $record = $model->where('service_id', $serviceId)->where('type', $type)->where('user_id', $userId)->first();

            if (!empty($record)) {
                $remove = $record->delete();

                if ($remove) {
                    return $this->returns('success.user.favorite.removed', $modelUser->getGlobalResponse($userId));
                }
            }
        }

        return $this->returns('error.user.favorite.serviceid.not.found', NULL, true);
    }

    public function getFavorite(Request $request)
    {
        $model  = new UserFavoriteService();
        $userId = (int)$request->get('user_id', false);

        if (!empty($userId)) {
            $records = $model->where('user_id', $userId)->get();

            if (!empty($records) && !$records->isEmpty()) {
                $records = $model::mergeResponse($records);

                return $this->returns('success.user.favorite.found', $records);
            }
        }

        return $this->returns('success.user.favorite.not.found', collect([]));
    }
}
