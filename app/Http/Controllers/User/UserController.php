<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\User;
use App\Booking;
use App\ServicePricing;
use App\UserSetting;
use App\UserEmailOtp;
use App\UserAddress;
use App\Shop;
use App\UserGenderPreference;
use App\TherapistReview;
use App\UserMenu;
use App\UserGuide;
use App\UserGiftVoucher;
use App\UserGiftVoucherInfo;
use App\UserGiftVoucherTheme;
use App\UserFaq;
use App\UserPack;
use App\UserPackMassage;
use App\UserPackGift;
use App\UserFavoriteService;
use DB;
use Carbon\Carbon;
use App\Libraries\CurrencyHelper;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\ServiceImage;
use App\SessionType;
use App\EventsAndCorporateRequest;
use App\ServiceTiming;
use App\BookingMassage;
use App\PackShop;
use App\UserCardDetail;
use Illuminate\Support\Str;
use App\ForgotOtp;
use App\TherapistUserRating;
use App\BookingPayment;
use Stripe;
use App\UserVoucherPrice;
use App\Voucher;
use App\Pack;
use App\ShopHour;
use App\PackService;

class UserController extends BaseController
{
    protected $currencyHelper;

    public $errorMsg = [
        'error.oauth.id' => 'User oauth id seems wrong.',
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
        'error.user.favorite.serviceid.not.found' => 'User favorite not found.',
        'error.booking.select.therapist' => 'Therapist is mandatory for couple with therapist session.',
        'error.single.users' => 'Please select only one user while you select single session.',
        'error.couple.users' => 'Please select more than one user.',
        'error.group.users' => 'Please select more than one user while you select group session.',
        'error.booking.select.service' => 'Please select at least one service.',
        'error.booking.select.user' => 'Please select at least one user.',
        'service.pricing.not.found' => 'Service pricing not found.',
        'error.booking.massage' => 'Booking massage not found.',
        'error.booking.massage.confirm' => 'Booking massage is confirm.',
        'error.pack.purchased' => 'Pack already purchased.',
        'error.voucher.purchased' => 'Voucher already purchased.',
        'error.card.not.found' => 'User card details not found.',
        'otp.not.found' => 'Otp not found !',
        'voucher.not.found' => 'Voucher not found !',
        'pack.not.found' => 'Pack not found !',
        'card.not.found' => 'Card not found !',
        'error.amount' => 'Please provide amount !',
        'error.pack.id' => 'Please provide pack id !',
        'error.voucher.id' => 'Please provide voucher id !',
        'error.user.id' => 'Please provide user id !',
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
        'success.therapist.review.added' => 'You already rated this therapist !',
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
        'success.user.favorite.not.found' => 'User favorite not found !',
        'success.user.qr.not.found' => 'User QR code not found !',
        'service.timings.found' => 'Service timings found !',
        'success.booking.massage.updated' => 'Booking massage updated successfully !',
        'success.booking.massage.deleted' => 'Booking massage deleted successfully !',
        'success.booking.events.corporate.request.created' => 'Booking events and corporate request created successfully !',
        'success.card.details.added' => 'User card details added successfully !',
        'success.id.uploaded' => 'User Id uploaded successfully !',
        'success.selfie.uploaded' => "User's selfie uploaded successfully !",
        'success.card.found' => "User's card details found successfully !",
        'success.otp' => 'Otp sent successfully !',
        'success.reset.password' => 'Password reset successfully !',
        'success.otp.verified' => 'Otp verified successfully !',
        'success.card.save' => 'User card details save successfully !',
        'success.card.delete' => 'User card delete successfully !',
        'pack.purchase' => 'Pack purchased successfully!',
        'voucher.purchase' => 'Voucher purchased successfully!',
        'success.payment' => 'Payment done successfully!',
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
        $oauthId  = !empty($data['oauth_uid']) ? $data['oauth_uid'] : NULL;
        $email    = (!empty($data['email'])) ? $data['email'] : NULL;
        $password = (!empty($data['password'])) ? $data['password'] : NULL;
        
        // Check username & password.
        $if     = ((empty($email) || empty($password)) && empty($oauthId));
        $elseif = (empty($oauthId) && (empty($email) || empty($password)));

        if ($if) {
            return $this->returnError(__('Username or Password is incorrect.'));
        } elseif ($elseif) {
            return $this->returnError(__('Oauth uid is incorrect.'));
        }

        $isUserNamePasswordLogin = (!empty($email) && !empty($password) || empty($oauthId));
        $isOauthLogin            = (!$isUserNamePasswordLogin && (empty($email) || empty($password)) && !empty($oauthId));

        if ($isUserNamePasswordLogin) {
            $user = $model->where('email', $email)->first();
        } elseif ($isOauthLogin) {
            $user = $model->where('oauth_uid', $oauthId)->first();
        }

        $check = false;

        if (!empty($user)) {
            if ($isUserNamePasswordLogin) {
                $check = ((string)$user->email === (string)$email && Hash::check($password, $user->password));
            } elseif ($isOauthLogin) {
                $check = (string)$user->oauth_uid === (string)$oauthId;
            }
        }

        if ($check === true) {
            return $this->returns('success.user.found', $user);
        } elseif ($isOauthLogin) {
            return $this->returns('error.oauth.id', NULL, true);
        }

        return $this->returns('error.something', NULL, true);
    }

    public function signUp(Request $request)
    {
        $data  = $request->all();
        $model = new User();

        DB::beginTransaction();

        try {
            if (empty($data['gender'])) {
                unset($data['gender']);
            }

            $validator = $model->validator($data);
            if ($validator->fails()) {
                return $this->returns($validator->errors()->first(), NULL, true);
            }

            $data['age'] = !empty($data['dob']) ?  Carbon::createFromTimestampMs($data['dob'])->age : $request->age;
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
            
            if(!empty($request->dob)) {
                $data['age'] = Carbon::createFromTimestampMs($data['dob'])->age;
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
        DB::beginTransaction();

        try {
            $shopModel    = new Shop();
            $bookingModel = new Booking();
            $total_price = 0;
            
            if(!empty($request->pack_id)) {
                $request->session_id = SessionType::SINGLE;
            }

            if ($request->session_id == SessionType::SINGLE && count($request->users) > 1) {
                return $this->returns('error.single.users', NULL, true);
            }

            if (($request->session_id == SessionType::COUPLE || $request->session_id == SessionType::COUPLE_WITH_THERAPIST || $request->session_id == SessionType::COUPLE_BACK_TO_BACK) && count($request->users) < 2) {
                return $this->returns('error.couple.users', NULL, true);
            }

            if ($request->session_id == SessionType::GROUP && count($request->users) < 2) {
                return $this->returns('error.group.users', NULL, true);
            }

            $date = !empty($request->booking_date_time) ? Carbon::createFromTimestampMs($request->booking_date_time) : Carbon::now();
            $bookingData = [
                'booking_type' => !empty($request->booking_type) ? $request->booking_type : Booking::BOOKING_TYPE_IMC,
                'special_notes' => $request->special_notes,
                'user_id' => $request->user_id,
                'shop_id' => $request->shop_id,
                'session_id' => $request->session_id,
                'payment_type' => (string)$request->payment_type,
                'booking_date_time' => $date,
                'book_platform' => !empty($request->book_platform) ? $request->book_platform : NULL,
                'bring_table_futon' => !empty($request->bring_table_futon) ? (string)$request->bring_table_futon : $bookingModel::BRING_TABLE_FUTON_NONE,
                'table_futon_quantity' => !empty($request->table_futon_quantity) ? (int)$request->table_futon_quantity : 0,
                'pack_id' => !empty($request->pack_id) ? $request->pack_id : NULL,
                'voucher_id' => !empty($request->voucher_id) ? $request->voucher_id : NULL
            ];

            $checks = $bookingModel->validator($bookingData);
            if ($checks->fails()) {
                return $this->returns($checks->errors()->first(), NULL, true);
            }

            $newBooking = Booking::create($bookingData);
            
            $request->request->add(['booking_id' => $newBooking->id]);

            if (!empty($request->users)) {
                foreach ($request->users as $key => $user) {
                    if (count($user['services']) <= 0) {
                        return $this->returns('error.booking.select.service', NULL, true);
                    }

                    $bookingInfo = $shopModel->addBookingInfo($request, $newBooking, $user, NULL);

                    if (!empty($bookingInfo['isError']) && !empty($bookingInfo['message'])) {
                        return $this->returns($bookingInfo['message'], NULL, true);
                    }

                    $massageModel = new BookingMassage();
                    foreach ($user['services'] as $key => $value) {
                        $service = $massageModel->addBookingMassages($value, $bookingInfo, $request, $user);

                        if (!empty($service['isError']) && !empty($service['message'])) {
                            return $this->returns($service['message'], NULL, true);
                        }
                        $total_price += $service['price'];
                    }
                }
                $newBooking->update(['total_price' => $total_price]);
            } else {
                return $this->returns('error.booking.select.user', NULL, true);
            }
            
            $request->booking_id = $newBooking->id;
            if(empty($request->pack_id)) {
                $paymentModule = new BookingPayment();
                $payment = $paymentModule->bookingPayment($request);
                if (!empty($payment['isError']) && !empty($payment['message'])) {
                    return $this->returns($payment['message'], NULL, true);
                }
            }
            DB::commit();
            return $this->returns('success.booking.created', $bookingModel->getGlobalQuery($request));
        } catch (\Throwable $e) {
            DB::rollback();
            throw $e;
        }

        return $this->returns('error.something', NULL, true);
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
                    "service_pricing_id"     => 1
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

        $data['stripe_key'] = env('STRIPE_KEY');
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
        $model      = new User();
        $id         = (int)$request->get('user_id', false);

        $userPeople = $model->where(function($query) use($id) {
            $query->where('user_id', $id)
                  ->orWhere('id', $id);
        })->where('is_removed', (string)$model::$notRemoved)->get();

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
        $model = new User();
        $data  = $request->all();

        DB::beginTransaction();

        try {
            $validator = $model->validator($data);
            if ($validator->fails()) {
                return $this->returns($validator->errors()->first(), NULL, true);
            }

            if (!empty($request->profile_photo)) {
                $validate = $model->validatePhoto($request);
                if ($validate->fails()) {
                    return $this->returns($validator->errors()->first(), NULL, true);
                }

                unset($data['profile_photo']);
            }

            $model->fill($data);
            $model->save();

            $userPeopleId = $model->id;

            if (!empty($request->profile_photo)) {
                $fileName      = time() . '_' . $userPeopleId . '.' . $request->profile_photo->getClientOriginalExtension();
                $storeFile     = $request->profile_photo->storeAs($model->profilePhotoPath, $fileName, $model->fileSystem);

                $model->update(['profile_photo' => $fileName]);
            }

        } catch(Exception $e) {
            DB::rollBack();
        }

        DB::commit();

        return $this->returns('success.user.people.created', $model);
    }

    public function updatePeople(Request $request)
    {
        $model = new User();
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

            if (!empty($request->profile_photo)) {
                $validate = $model->validatePhoto($request);
                if ($validate->fails()) {
                    return $this->returns($validate->errors()->first(), NULL, true);
                }

                unset($data['profile_photo']);
            }

            $update = $model->where('id', $id)->update($data);

            if ($update && !empty($request->profile_photo)) {
                $fileName      = time() . '_' . $id . '.' . $request->profile_photo->getClientOriginalExtension();
                $storeFile     = $request->profile_photo->storeAs($model->profilePhotoPath, $fileName, $model->fileSystem);

                $model->where('id', $id)->update(['profile_photo' => $fileName]);
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
        $model = new User();
        $id    = (int)$request->get('id', false);
        $user_id    = (int)$request->get('user_id', false);

        if (!empty($id)) {
            $userPeople = $model->where(['id' => $id, 'user_id' => $user_id])->first();

            if (!empty($userPeople)) {
                $userPeople->is_removed = $model::$removed;

                if ($userPeople->save()) {
                    return $this->returns('success.user.people.removed', collect([]));
                }
            }
        }

        return $this->returns('error.user.people.not.found', NULL, true);
    }

    public function getRatings($id) {
        $ratings = TherapistUserRating::where(['model_id' => $id, 'model' => 'App\Therapist'])->get();

        $cnt = $rates = $avg = 0;
        if ($ratings->count() > 0) {
            foreach ($ratings as $i => $rating) {
                $rates += $rating->rating;
                $cnt++;
            }
            $avg = $rates / $cnt;
        }
        
        return number_format($avg, 2);
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
                                $bookingInfo->therapist->avg = $this->getRatings($therapistId);
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
                        $booking->shop->total_services = !is_null($booking->shop->services) ? $booking->shop->services->count() : 0;
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
    
    public function getPendingBooking(Request $request)
    {
        $userId = (int)$request->get('user_id', false);

        $model  = new Booking();

        $data   = $model->getWherePastFuture($userId, false, false, true);

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

            $is_exist = $model->where($data)->first();
            if(!empty($is_exist)) {
                return $this->returns('success.therapist.review.added', collect([]));
            }
            
            $save = $model->create($data);

            if ($save) {
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
            
            $user = User::find($userId);
            if(empty($user)) {
                return $this->returnError($this->errorMsg['error.user.not.found']);
            }
            $data = $model->with('design')->where('user_id', $userId)->get();

            if (!empty($data) && !$data->isEmpty()) {
                $data->map(function($value) use($model){
                    $value->start_from = $value->created_at;
                    $value->last_date  = date("Y-m-d", strtotime(date("Y-m-d", strtotime($value->created_at)) . " + ".GIFT_VOUCHER_LIMIT." days"));
                    $value->theme = $model->getTheme($value->design->theme_id);
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
            $card = UserCardDetail::where(['user_id' => $request->user_id, 'is_default' => UserCardDetail::CARD_DEFAULT])->first();
            if(empty($card)) {
                return $this->returnError($this->errorMsg['card.not.found']);
            }
            if(empty($data['amount'])) {
                return $this->returnError($this->errorMsg['error.amount']);
            }
            
            try {
                Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
                $charge = \Stripe\Charge::create(array(
                            "amount" => $data['amount'] * 100,
                            "currency" => "usd",
                            "customer" => $card->stripe_id,
                            "description" => "Test payment from evolution.com.")
                );

                if ($charge->status == 'succeeded') {

                    $uniqueId = mt_rand(10000000, 99999999);

                    // Check exists.
                    $check = $model->where('unique_id', $uniqueId)->first();
                    if (!empty($check)) {
                        $uniqueId = mt_rand(10000000, 99999999);
                    }

                    $data['unique_id'] = $uniqueId;

                    if (!empty($data['preference_email_date'])) {
                        $emailDate = $data['preference_email_date'] = date("Y-m-d", ($data['preference_email_date'] / 1000));
                    }

                    if (!empty($data['amount'])) {
                        $data['amount'] = (float) $data['amount'];
                        $data['available_amount'] = (float) $data['amount'];
                    }

                    $now = Carbon::now();
                    $data['payment_id'] = $charge->id;
                    $data['expired_date'] = $now->addYear()->format('Y-m-d');
                    
                    $validator = $model->validator($data);
                    if ($validator->fails()) {
                        return $this->returns($validator->errors()->first(), NULL, true);
                    }

                    $model->fill($data);
                    $save = $model->save();

                    if ($save) {
                        $today = strtotime(date('Y-m-d'));
                        $emailDate = strtotime($emailDate);

                        if ($today == $emailDate) {
                            // Send Email.
                        } else {
                            // Set console command for send email for the future date.
                        }
                    }
                }
                DB::commit();
                return $this->returns('success.user.gift.voucher.created', $model);
                
            } catch (\Stripe\Exception\CardException $e) {
                return ['isError' => true, 'message' => $e->getError()->message];
            } catch (\Stripe\Exception\RateLimitException $e) {
                return ['isError' => true, 'message' => $e->getError()->message];
            } catch (\Stripe\Exception\InvalidRequestException $e) {
                return ['isError' => true, 'message' => $e->getError()->message];
            } catch (\Stripe\Exception\AuthenticationException $e) {
                return ['isError' => true, 'message' => $e->getError()->message];
            } catch (\Stripe\Exception\ApiConnectionException $e) {
                return ['isError' => true, 'message' => $e->getError()->message];
            } catch (\Stripe\Exception\ApiErrorException $e) {
                return ['isError' => true, 'message' => $e->getError()->message];
            } catch (Exception $e) {
                return ['isError' => true, 'message' => $e->getError()->message];
            }            
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollback();
            throw $e;
        }
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
        $packs = PackShop::with('pack')->where('shop_id', $request->shop_id)->get();
                
        if (!empty($packs)) {
            
            $packData = [];
            foreach ($packs as $key => $value) {
             
                $packData[] = [
                    "id" => $value->id,
                    "pack_id" => $value->pack_id,
                    "shop_id" => $value->shop_id,
                    "name" => $value->pack->name,
                    "sub_title" => $value->pack->sub_title,
                    "number" => $value->pack->number,
                    "image" => $value->pack->image,
                    "total_price" => $value->pack->total_price,
                    "pack_price" => $value->pack->pack_price,
                    "expired_date" => $value->pack->expired_date,
                    "receptionist_id" => $value->pack->receptionist_id,
                    "is_personalized" => $value->pack->is_personalized,
                ];
            }
            return $this->returns('success.user.packs.found', collect($packData));
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
                    
                    $pricing = ServicePricing::with('service','timing')->where('id', $getMassage->service_price_id)->first();
                    if (!empty($pricing->service) &&  !empty($pricing->timing)) {
                        $image = ServiceImage::where(['service_id' => $pricing->service->id, 'is_featured' => ServiceImage::IS_FEATURED])->first();
                        $return[] = [
                            'service_english_name'  => $pricing->service->english_name,
                            'service_portugese_name'  => $pricing->service->portugese_name,
                            'time'  => $pricing->timing->time,
                            'image' => $image->image
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
        $model  = new UserPack();
        $data   = $request->all();

        DB::beginTransaction();

        try {
            
            $is_exist = $model->where(['user_id' => $data['user_id'], 'pack_id' => $data['pack_id']])->first();
            if(!empty($is_exist)) {
                return $this->returns('error.pack.purchased', NULL, true);
            }
            $data['purchase_date'] = Carbon::now()->format('Y-m-d');
            $validator = $model->validator($data);
            if ($validator->fails()) {
                return $this->returns($validator->errors()->first(), NULL, true);
            }

            $packData = $model->create($data);
        } catch(Exception $e) {
            DB::rollBack();
        }

        DB::commit();

        return $this->returns('success.user.pack.ordered', $packData);
    }

    public function savePackGifts(Request $request)
    {
        $model  = new UserPackGift();
        $data   = $request->all();        

        DB::beginTransaction();
        try {
            $card = UserCardDetail::where(['user_id' => $request->user_id, 'is_default' => UserCardDetail::CARD_DEFAULT])->first();
            if(empty($card)) {
                return $this->returnError($this->errorMsg['card.not.found']);
            }
            $pack = Pack::find($request->pack_id);
            if(empty($pack)) {
                return $this->returnError($this->errorMsg['pack.not.found']);
            }
            $is_exist = $model->where(['user_id' => $request->user_id, 'pack_id' => $request->pack_id])->first();
            if(!empty($is_exist)) {
                return $this->returnError($this->errorMsg['error.pack.purchased']);
            }
            
            try {
                Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
                $charge = \Stripe\Charge::create(array(
                            "amount" => $pack->pack_price  * 100,
                            "currency" => "usd",
                            "customer" => $card->stripe_id,
                            "description" => "Test payment from evolution.com.")
                );

                if ($charge->status == 'succeeded') {
                    
                    if (!empty($data['preference_email_date'])) {
                        $emailDate = $data['preference_email_date'] = date("Y-m-d", ($data['preference_email_date'] / 1000));
                    }
                    $data['payment_id'] = $charge->id;
                    $validator = $model->validator($data);
                    if ($validator->fails()) {
                        return $this->returns($validator->errors()->first(), NULL, true);
                    }

                    $save = $model->create($data);
                    
                    if ($save) {
                        $today = strtotime(date('Y-m-d'));
                        $emailDate = strtotime($emailDate);

                        if ($today == $emailDate) {
                            // Send Email.
                        } else {
                            // Set console command for send email for the future date.
                        }
                    }
                }
                DB::commit();
                return $this->returns('success.user.pack.gift.created', collect($data));
                
            } catch (\Stripe\Exception\CardException $e) {
                return ['isError' => true, 'message' => $e->getError()->message];
            } catch (\Stripe\Exception\RateLimitException $e) {
                return ['isError' => true, 'message' => $e->getError()->message];
            } catch (\Stripe\Exception\InvalidRequestException $e) {
                return ['isError' => true, 'message' => $e->getError()->message];
            } catch (\Stripe\Exception\AuthenticationException $e) {
                return ['isError' => true, 'message' => $e->getError()->message];
            } catch (\Stripe\Exception\ApiConnectionException $e) {
                return ['isError' => true, 'message' => $e->getError()->message];
            } catch (\Stripe\Exception\ApiErrorException $e) {
                return ['isError' => true, 'message' => $e->getError()->message];
            } catch (Exception $e) {
                return ['isError' => true, 'message' => $e->getError()->message];
            }            
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollback();
            throw $e;
        }
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
            'data' => true // $isChecked
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
                
                $selfie = $request->file('selfie', []);

                if (!empty($selfie) && $selfie instanceof UploadedFile) {
                    $checkMime = $model->checkMimeTypes($request, $selfie, 'jpeg,png,jpg');
                    if ($checkMime->fails()) {
                        return $this->returns($checkMime->errors()->first(), NULL, true);
                    }

                    $fileName                   = time() . '_' . $userId . '.' . $selfie->getClientOriginalExtension();
                    $storeFile                  = $selfie->storeAs($model->selfiePath, $fileName, $model->fileSystem);
                    $user->selfie               = $storeFile ? $fileName : NULL;
                }
                $user->is_document_verified = User::NO_ACTION;
                $this->checkDocument($request);
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
                } elseif ($document == "selfie") {
                    $delete = Storage::disk($model->fileSystem)->delete($model->selfiePath . $user->getAttributes()[$document]);

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

        if ($model->checkServiceIdExists($serviceId)) {
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
            $records = $model->with('services', 'user')->where('user_id', $userId)->get();

            if (!empty($records) && !$records->isEmpty()) {
                $records = $model::mergeResponse($records);

                return $this->returns('success.user.favorite.found', $records);
            }
        }

        return $this->returns('success.user.favorite.not.found', collect([]));
    }

    public function getQRTemp()
    {
        $modal = new User();

        $user  = $modal->find(2);

        if (!empty($user)) {

            echo "<img src='" . $user->qr_code_path . "' />";exit;
        }

        return $this->returns('success.user.qr.not.found', collect([]));
    }

    public function addEventsCorporateRequest(Request $request)
    {
        $modal = new EventsAndCorporateRequest();
        $data  = $request->all();

        $validator = $modal->validator($data);
        if ($validator->fails()) {
            return $this->returns($validator->errors()->first(), NULL, true);
        }

        $create = $modal->updateOrCreate($data);

        if ($create) {
            return $this->returns('success.booking.events.corporate.request.created', $create);
        }
    }
    
    public function getServiceTiming(Request $request) {
        
        $timings = ServiceTiming::with('pricing')->where('service_id', $request->service_id)->get();
        return $this->returns('service.timings.found', $timings);
    }
    
    public function updatePendingBooking(Request $request) {

        $price = ServicePricing::where(['service_id' => $request->service_id, 'service_timing_id' => $request->service_timing_id])->first();
        if (empty($price)) {
            return $this->returns('service.pricing.not.found', NULL, true);
        }

        $booking_massage = BookingMassage::find($request->booking_massage_id);
        if (empty($booking_massage)) {
            return $this->returns('error.booking.massage', NULL, true);
        }

        if ($booking_massage->is_confirm == BookingMassage::IS_CONFIRM) {
            return $this->returns('error.booking.massage.confirm', NULL, true);
        }

        $booking_massage->update(['service_pricing_id' => $price->id]);
        return $this->returns('success.booking.massage.updated', $booking_massage);
    }
    
    public function deletePendingBooking(Request $request) {

        $booking_massage = BookingMassage::find($request->booking_massage_id);
        if (empty($booking_massage)) {
            return $this->returns('error.booking.massage', NULL, true);
        }

        if ($booking_massage->is_confirm == BookingMassage::IS_CONFIRM) {
            return $this->returns('error.booking.massage.confirm', NULL, true);
        }
        $is_delete = $booking_massage->delete();
        if($is_delete) {
            return $this->returns('success.booking.massage.deleted', $booking_massage);
        }
        return $this->returns('error.something', NULL, true);
    }
    
    public function checkDocument(Request $request) {
        
        $card = UserCardDetail::where('user_id', $request->user_id)->first();
        $user = User::find($request->user_id);
        
        if(!empty($card) && !empty($user->id_passport_front) && !empty($user->id_passport_back) && !empty($user->selfie)) {
            $user->update(['is_document_uploaded' => '1']);
        } else {
            $user->update(['is_document_uploaded' => '0']);
        }
        return true;
    }
    
    public function saveCardDetails(Request $request) {
        
        DB::beginTransaction();
        try {

            $data = $request->all();
            $model = new UserCardDetail();
            $user = User::find($request->user_id);

            $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET'));
            $token = $stripe->tokens->retrieve(
                    $request->stripe_token, []);
            $data['card_number'] = $token->card->last4;
            $data['exp_month'] = $token->card->exp_month;
            $data['exp_year'] = $token->card->exp_year;
            
            $validator = $model->validator($data);
            if ($validator->fails()) {
                return $this->returns($validator->errors()->first(), NULL, true);
            }
            
            $create = UserCardDetail::create($data);
            
            $create->is_document_uploaded = $user->is_document_uploaded;
            $this->checkDocument($request);
            unset($create->is_document_uploaded);
            
            if ($create) {

                Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
                // Create a Customer
                $customer = \Stripe\Customer::create(array(
                            "source" => $request->stripe_token,
                            'email' => $user->email,
                            'name' => $user->name));
                $create->update(['stripe_id' => $customer->id]);
                DB::commit();
                return $this->returns('success.card.details.added', $create);
            }
        } catch (Exception $e) {
            DB::rollBack();
        }
        return $this->returns('error.something', NULL, true);
    }
    
    public function saveSelfie(Request $request) {
        
        $model = new User();
        $userId = $request->user_id;
 
        if (!empty($userId)) {
            $user = $model->find($userId);
            if (!empty($user)) {

                $selfie = $request->file('selfie', []);
                if (!empty($selfie) && $selfie instanceof UploadedFile) {
                    $checkMime = $model->checkMimeTypes($request, $selfie, 'jpeg,png,jpg');
                    if ($checkMime->fails()) {
                        return $this->returns($checkMime->errors()->first(), NULL, true);
                    }

                    $selfiePic = Str::random(5) . '_' . $userId . '.' . $selfie->getClientOriginalExtension();
                    $storeFile = $selfie->storeAs($model->selfiePath, $selfiePic, $model->fileSystem);
                    $data['selfie'] = $storeFile ? $selfiePic : NULL;

                    $user->update(['selfie' => $data['selfie']]);
                    $this->checkDocument($request);
                    return $this->returns('success.selfie.uploaded', $user);
                }
                return $this->returns('error.something', NULL, true);
            }
        }
        return $this->returns('error.user.not.found', NULL, true);
    }
    
    public function getUserPacks(Request $request) {
        
        $filter = !empty($request->filter) ? $request->filter : Pack::MY_PACKS;
        
        if($filter == Pack::MY_PACKS) {
            $packs = UserPack::with('pack')->where(['user_id' => $request->user_id])->get();
        } else {
            $packs = UserPackGift::with('pack')->where('user_id', $request->user_id)->get();
        }
        
        if (!empty($packs)) {
            
            $packData = [];
            foreach ($packs as $key => $value) {
             
                $purchased_date = $filter == Pack::MY_PACKS ? $value->purchase_date : $value->created_at;
                $packData[] = [
                    "id" => $value->pack_id,
                    "name" => $value->pack->name,
                    "sub_title" => $value->pack->sub_title,
                    "number" => $value->pack->number,
                    "image" => $value->pack->image,
                    "total_price" => $value->pack->total_price,
                    "pack_price" => $value->pack->pack_price,
                    "expired_date" => $value->pack->expired_date,
                    "receptionist_id" => $value->pack->receptionist_id,
                    "is_personalized" => $value->pack->is_personalized,
                    "purchase_date" => $purchased_date,
                ];
            }
            return $this->returns('success.user.packs.found', collect($packData));
        }

        return $this->returns('success.user.packs.not.found', collect([]));
    }
    
    public function getCardDetails(Request $request) {
        
        $data = UserCardDetail::where('user_id', $request->user_id)->get();
        if(count($data) > 0) {
            return $this->returns('success.card.found', collect($data));
        }
        return $this->returns('error.card.not.found', NULL, TRUE);
    }
    
    public function deleteOtp(Request $request) {
        
        $otps = ForgotOtp::where(['model_id' => $request->user_id, 'model' => User::USER])->get();
        foreach ($otps as $key => $otp) {
            $otp->delete();
        }
        return true;
    }
    
    public function forgotPassword(Request $request) {

        $user = User::where('tel_number', $request->mobile_number)->first();

        if (empty($user)) {
            return $this->returnError($this->errorMsg['error.user.not.found']);
        }

        $request->request->add(['user_id' => $user->id]);
        $this->deleteOtp($request);
        
        $data = [
            'model_id' => $user->id,
            'model' => User::USER,
            'otp' => 1234,
            'mobile_number' => $request->mobile_number,
            'mobile_code' => $request->mobile_code,
        ];

        ForgotOtp::create($data);
        return $this->returnSuccess(__($this->successMsg['success.otp']), ['user_id' => $user->id, 'otp' => 1234]);
    }
    
    public function resetPassword(Request $request) {
        
        $user = User::find($request->user_id);

        if (empty($user)) {
            return $this->returnError($this->errorMsg['error.user.not.found']);
        }
        
        $user->update(['password' => Hash::make($request->password)]);
        $this->deleteOtp($request);
        
        return $this->returnSuccess(__($this->successMsg['success.reset.password']), $user);
    }
    
    public function verifyOtp(Request $request) {
        
        $is_exist = ForgotOtp::where(['model_id' => $request->user_id, 'model' => User::USER, 'otp' => $request->otp])->first();
        
        if(empty($is_exist)) {
            return $this->returnError($this->errorMsg['otp.not.found']);
        }
        return $this->returnSuccess(__($this->successMsg['success.otp.verified']), $is_exist);
    }
    
    public function saveDefaultCard(Request $request) {
        
        DB::beginTransaction();

        try {
            $is_exist = UserCardDetail::where(['id' => $request->card_id, 'user_id' => $request->user_id])->first();
            $all_cards = UserCardDetail::where(['user_id' => $request->user_id])->get();

            if (empty($is_exist)) {
                return $this->returnError($this->errorMsg['error.card.not.found']);
            }

            foreach ($all_cards as $key => $card) {
                $card->update(['is_default' => UserCardDetail::CARD_NOT_DEFAULT]);
            }

            $is_exist->update(['is_default' => UserCardDetail::CARD_DEFAULT]);
            DB::commit();
            return $this->returnSuccess(__($this->successMsg['success.card.save']), $is_exist);
        } catch (Exception $e) {
            DB::rollBack();
        }
    }
    
    public function deleteCard(Request $request) {
        
        $is_exist = UserCardDetail::where(['id' => $request->card_id, 'user_id' => $request->user_id])->first();
        if (empty($is_exist)) {
            return $this->returnError($this->errorMsg['error.card.not.found']);
        }
        
        $is_exist->delete();
        return $this->returnSuccess(__($this->successMsg['success.card.delete']), []);
    }
    
    public function purchaseVoucher(Request $request) {

        DB::beginTransaction();
        try {
            $card = UserCardDetail::where(['user_id' => $request->user_id, 'is_default' => UserCardDetail::CARD_DEFAULT])->first();
            if(empty($card)) {
                return $this->returnError($this->errorMsg['card.not.found']);
            }
            $voucher = Voucher::find($request->voucher_id);
            if(empty($voucher)) {
                return $this->returnError($this->errorMsg['voucher.not.found']);
            }
            $is_exist = UserVoucherPrice::where(['user_id' => $request->user_id, 'voucher_id' => $request->voucher_id])->first();
            if(!empty($is_exist)) {
                return $this->returnError($this->errorMsg['error.voucher.purchased']);
            }
            
            try {
                Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
                $charge = \Stripe\Charge::create(array(
                            "amount" => $voucher->price * 100,
                            "currency" => "usd",
                            "customer" => $card->stripe_id,
                            "description" => "Test payment from evolution.com.")
                );

                if ($charge->status == 'succeeded') {
                    $model = new UserVoucherPrice();
                    $data = $request->all();
                    $data['total_value'] = $voucher->price;
                    $data['purchase_date'] = Carbon::now()->format('Y-m-d');
                    $data['payment_id'] = $charge->id;
                    
                    $checks = $model->validator($data);
                    if ($checks->fails()) {
                        return $this->returnError($checks->errors()->first(), NULL, true);
                    }
                    $purchaseVoucher = $model->create($data);
                }
                DB::commit();
                return $this->returnSuccess(__($this->successMsg['voucher.purchase']), $purchaseVoucher);
            } catch (\Stripe\Exception\CardException $e) {
                return ['isError' => true, 'message' => $e->getError()->message];
            } catch (\Stripe\Exception\RateLimitException $e) {
                return ['isError' => true, 'message' => $e->getError()->message];
            } catch (\Stripe\Exception\InvalidRequestException $e) {
                return ['isError' => true, 'message' => $e->getError()->message];
            } catch (\Stripe\Exception\AuthenticationException $e) {
                return ['isError' => true, 'message' => $e->getError()->message];
            } catch (\Stripe\Exception\ApiConnectionException $e) {
                return ['isError' => true, 'message' => $e->getError()->message];
            } catch (\Stripe\Exception\ApiErrorException $e) {
                return ['isError' => true, 'message' => $e->getError()->message];
            } catch (Exception $e) {
                return ['isError' => true, 'message' => $e->getError()->message];
            }            
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollback();
            throw $e;
        }
    }
    
    public function purchasePack(Request $request) {
        
        DB::beginTransaction();
        try {
            $card = UserCardDetail::where(['user_id' => $request->user_id, 'is_default' => UserCardDetail::CARD_DEFAULT])->first();
            if(empty($card)) {
                return $this->returnError($this->errorMsg['card.not.found']);
            }
            $pack = Pack::find($request->pack_id);
            if(empty($pack)) {
                return $this->returnError($this->errorMsg['pack.not.found']);
            }
            $is_exist = UserPack::where(['user_id' => $request->user_id, 'pack_id' => $request->pack_id])->first();
            if(!empty($is_exist)) {
                return $this->returnError($this->errorMsg['error.pack.purchased']);
            }
            
            try {
                Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
                $charge = \Stripe\Charge::create(array(
                            "amount" => $pack->pack_price  * 100,
                            "currency" => "usd",
                            "customer" => $card->stripe_id,
                            "description" => "Test payment from evolution.com.")
                );

                if ($charge->status == 'succeeded') {
                    $model = new UserPack();
                    $data = $request->all();
                    $data['purchase_date'] = Carbon::now()->format('Y-m-d');
                    $data['payment_id'] = $charge->id;
                    
                    $checks = $model->validator($data);
                    if ($checks->fails()) {
                        return $this->returnError($checks->errors()->first(), NULL, true);
                    }
                    $purchasePack = $model->create($data);
                }
                DB::commit();
                return $this->returnSuccess(__($this->successMsg['pack.purchase']), $purchasePack);
            } catch (\Stripe\Exception\CardException $e) {
                return ['isError' => true, 'message' => $e->getError()->message];
            } catch (\Stripe\Exception\RateLimitException $e) {
                return ['isError' => true, 'message' => $e->getError()->message];
            } catch (\Stripe\Exception\InvalidRequestException $e) {
                return ['isError' => true, 'message' => $e->getError()->message];
            } catch (\Stripe\Exception\AuthenticationException $e) {
                return ['isError' => true, 'message' => $e->getError()->message];
            } catch (\Stripe\Exception\ApiConnectionException $e) {
                return ['isError' => true, 'message' => $e->getError()->message];
            } catch (\Stripe\Exception\ApiErrorException $e) {
                return ['isError' => true, 'message' => $e->getError()->message];
            } catch (Exception $e) {
                return ['isError' => true, 'message' => $e->getError()->message];
            }            
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollback();
            throw $e;
        }                
    }
    
    public function getPackDetails(Request $request) {
        
        if(empty($request->pack_id)){
            return $this->returnError($this->errorMsg['error.pack.id']);
        }
        if(empty($request->user_id)){
            return $this->returnError($this->errorMsg['error.user.id']);
        }
        
        $pack = PackShop::with('pack','shop')->where('pack_id', $request->pack_id)->first();
        if (empty($pack)) {
            return $this->returnError($this->errorMsg['pack.not.found']);
        }
        
        $user = UserPack::where(['user_id' => $request->user_id, 'pack_id' => $request->pack_id])->first();
        if (empty($user)) {
            return $this->returnError($this->errorMsg['pack.not.found']);
        }
        $pack->pack->purchase_date = $user->purchase_date;
        $hours = ShopHour::where('shop_id', $pack->shop->id)->get();
        $service = PackService::with('service')->where('pack_id', $request->pack_id)->get();
        $services = [];
        
        foreach ($service as $key => $value) {
            $price = ServicePricing::with('timing')->where(['service_id' => $value->service_id, 'service_timing_id' => $value->service_timing_id])->first();
            $services[] = [
                'service_id' => $value->service->id,
                'service_name' => $value->service->english_name,
                'service_type' => $value->service->service_type,
                'service_timie' => $price->timing->time,
                'service_timing_id' => $value->service_timing_id,
                'service_pricing_id' => $price->id
            ];
        }
        
        $packData['pack'] = $pack->pack;
        $packData['shop'] = [
            'id' => $pack->shop->id,
            'name' => $pack->shop->name,
            'address' => $pack->shop->address,
            'latitude' => $pack->shop->latitude,
            'longitude' => $pack->shop->longitude,
            'longitude' => $pack->shop->longitude,
            'featuredImage' => $pack->shop->featuredImage->image,
            'shop_hours' => $hours
        ];
        $packData['pack_services'] = $services;
        
        return $this->returnSuccess(__($this->successMsg['success.user.packs.found']), collect($packData));
    }
    
    public function getGiftPackDetails(Request $request) {
        
        if(empty($request->pack_id)){
            return $this->returnError($this->errorMsg['error.pack.id']);
        }
        if(empty($request->user_id)){
            return $this->returnError($this->errorMsg['error.user.id']);
        }
        
        $pack = UserPackGift::with('pack')->where(['user_id' => $request->user_id, 'pack_id' => $request->pack_id])->first();
        if (empty($pack)) {
            return $this->returnError($this->errorMsg['pack.not.found']);
        }
        $service = PackService::with('service')->where('pack_id', $request->pack_id)->get();
        $shop = PackShop::with('shop')->where('pack_id', $request->pack_id)->first();
        $hours = ShopHour::where('shop_id', $shop->shop->id)->get();
        $services = [];
        
        foreach ($service as $key => $value) {
            $price = ServicePricing::with('timing')->where(['service_id' => $value->service_id, 'service_timing_id' => $value->service_timing_id])->first();
            $services[] = [
                'service_id' => $value->service->id,
                'service_name' => $value->service->english_name,
                'service_type' => $value->service->service_type,
                'service_timie' => $price->timing->time,
                'service_timing_id' => $value->service_timing_id,
                'service_pricing_id' => $price->id
            ];
        }
        $shopData = [
            'id' => $shop->shop->id,
            'name' => $shop->shop->name,
            'address' => $shop->shop->address,
            'latitude' => $shop->shop->latitude,
            'longitude' => $shop->shop->longitude,
            'longitude' => $shop->shop->longitude,
            'featuredImage' => $shop->shop->featuredImage->image,
            'shop_hours' => $hours
        ];
        $packData['pack_user_detail'] = [
            'recipient_name' => $pack->recipient_name,
            'recipient_last_name' => $pack->recipient_last_name,
            'recipient_second_name' => $pack->recipient_second_name,
            'recipient_mobile' => $pack->recipient_mobile,
            'recipient_email' => $pack->recipient_email,
            'giver_first_name' => $pack->giver_first_name,
            'giver_last_name' => $pack->giver_last_name,
            'giver_mobile' => $pack->giver_mobile,
            'giver_email' => $pack->giver_email,
            'giver_message_to_recipient' => $pack->giver_message_to_recipient,
            'preference_email' => $pack->preference_email,
            'preference_email_date' => $pack->preference_email_date,
            'pack_id' => $pack->pack_id,
            'user_id' => $pack->user_id,
            'is_removed' => $pack->is_removed,
            'payment_id' => $pack->payment_id,
            'sent_date' => $pack->created_at,
        ];
        $packData['pack'] = $pack->pack;
        $packData['pack_services'] = $services;
        $packData['shop'] = $shopData;
        return $this->returnSuccess(__($this->successMsg['success.user.packs.found']), collect($packData));
    }
    
    public function payRemainingPayment(Request $request) {
        
        $model = new BookingPayment();
        $payment = $model->bookingHalfPayment($request);
        if (!empty($payment['isError']) && !empty($payment['message'])) {
            return $this->returns($payment['message'], NULL, true);
        }
        return $this->returnSuccess(__($this->successMsg['success.payment']), $payment);
    }
    
    public function getVoucherDetail(Request $request) {
        
        if(empty($request->voucher_id)){
            return $this->returnError($this->errorMsg['error.voucher.id']);
        }
        if(empty($request->user_id)){
            return $this->returnError($this->errorMsg['error.user.id']);
        }
        
        $model = new UserGiftVoucher();
        $voucher = $model->with('shop','design')->where(['id' => $request->voucher_id, 'user_id' => $request->user_id])->first();
        if(empty($voucher)){
            return $this->returnError($this->errorMsg['voucher.not.found']);
        }
        $shop_hours = ShopHour::where('shop_id', $voucher->shop->id)->get();
        $shopData = [
            'id' => $voucher->shop->id,
            'name' => $voucher->shop->name,
            'address' => $voucher->shop->address,
            'latitude' => $voucher->shop->latitude,
            'longitude' => $voucher->shop->longitude,
            'longitude' => $voucher->shop->longitude,
            'featured_image' => $voucher->shop->featuredImage->image,
            'total_services' => $voucher->shop->totalServices,
            'shop_hours' => $shop_hours
        ];
        $voucher = [
            'recipient_name' => $voucher->recipient_name,
            'recipient_last_name' => $voucher->recipient_last_name,
            'recipient_second_name' => $voucher->recipient_second_name,
            'recipient_mobile' => $voucher->recipient_mobile,
            'recipient_email' => $voucher->recipient_email,
            'giver_first_name' => $voucher->giver_first_name,
            'giver_last_name' => $voucher->giver_last_name,
            'giver_mobile' => $voucher->giver_mobile,
            'giver_email' => $voucher->giver_email,
            'giver_message_to_recipient' => $voucher->giver_message_to_recipient,
            'preference_email' => $voucher->preference_email,
            'preference_email_date' => $voucher->preference_email_date,
            'amount' => $voucher->amount,
            'available_amount' => $voucher->available_amount,
            'user_id' => $voucher->user_id,
            'design_id' => $voucher->design_id,
            'unique_id' => $voucher->unique_id,
            'payment_id' => $voucher->payment_id,
            'expired_at' => $voucher->expired_at,
            'design' => $voucher->design,
            'theme' => $model->getTheme($voucher->design->theme_id)
        ];
        $voucherData = [
            'user_voucher_detail' => $voucher,
            'shop' => $shopData,
        ];
        return $this->returnSuccess(__($this->successMsg['success.user.gift.voucher.found']), $voucherData);
    }
}
