<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use App\Voucher;
use App\VoucherShop;
use App\UserVoucher;
use App\UserVoucherPrice;
use Carbon\Carbon;
use App\Pack;
use App\PackShop;
use App\PackService;
use App\UserPack;
use DB;
use App\Superadmin;
use Illuminate\Support\Facades\Hash;
use App\SuperAdminEmailOtp;
use App\Service;
use App\ServiceTiming;
use App\ServicePricing;
use App\ServiceRequirement;
use App\ServiceImage;
use Illuminate\Http\UploadedFile;
use App\ForgotOtp;
use App\Booking;
use App\Shop;

class SuperAdminController extends BaseController {

    public $errorMsg = [
        'loginEmail' => "Please provide email.",
        'loginPass' => "Please provide password.",
        'loginBoth' => "Shop email or password seems wrong.",
        'admin.not.found' => "Admin not found.",
        'error.otp' => 'Please provide OTP properly.',
        'error.otp.wrong' => 'OTP seems wrong.',
        'error.otp.already.verified' => 'OTP already verified.',
        'error.admin.id' => 'Please provide valid admin id.',
        'error.email.already.verified' => 'This user email already verified with this ',
        'error.email.id' => 'email id.',
        'error.mimes' => 'Please select proper file. The file must be a file of type: jpeg, png, jpg.',
        'otp.not.found' => 'Otp not found !'
    ];
    
    public $successMsg = [
        'voucher.add' => 'Voucher created successfully!',
        'voucher.update' => 'Voucher updated successfully!',
        'voucher.get' => 'Vouchers found successfully!',
        'voucher.share' => 'Voucher shared successfully!',
        'voucher.shared' => 'Voucher already shared!',        
        'voucher.add.services' => 'Services added to voucher successfully!',
        'pack.add' => 'Pack created successfully!',
        'pack.share' => 'Pack shared successfully!',
        'pack.shared' => 'Pack already shared!',
        'pack.get' => 'Packs found successfully!',        
        'login' => "Login successfully !",
        'edit.profile' => "Profile updated successfully!",
        'details.found' => "Admin details found successfully!",
        'success.email.otp.compare' => 'OTP matched successfully !',
        'success.sms.sent' => 'SMS sent successfully !',
        'success.email.sent' => 'Email sent successfully !',
        'service.add' => 'Service added successfully !',
        'massages' => 'Massages found successfully !',
        'therapies' => 'Therapies found successfully !',
        'success.otp' => 'Otp sent successfully !',
        'success.reset.password' => 'Password reset successfully !',
        'success.otp.verified' => 'Otp verified successfully !',
        'print.booking' => 'Booking data found successfully',
        'cancelled.booking' => 'Cancelled bookings found successfully',
        'past.booking' => 'Past bookings found successfully',
        'future.booking' => 'Future bookings found successfully',
        'pending.booking' => 'Pending bookings found successfully',
    ];

    public function addVoucher(Request $request) {

        $model = new Voucher();
        $data = $request->all();
        $data['number'] = generateRandomString();
        $data['expired_date'] = Carbon::createFromTimestampMs($data['expired_date']);

        $checks = $model->validator($data);
        if ($checks->fails()) {
            return $this->returnError($checks->errors()->first(), NULL, true);
        }
        
        /* For profile Image */
        if ($request->hasFile('image')) {
            $checkImage = $model->validateImage($data);
            if ($checkImage->fails()) {
                unset($data['image']);

                return $this->returnError($checkImage->errors()->first(), NULL, true);
            }
            $fileName = time() . '.' . $data['image']->getClientOriginalExtension();
            $storeFile = $data['image']->storeAs($model->profilePhotoPath, $fileName, $model->fileSystem);

            if ($storeFile) {
                $data['image'] = $fileName;
            }
        }
        $voucher = $model->create($data);

        return $this->returnSuccess(__($this->successMsg['voucher.add']), $voucher);
    }

    public function updateVoucher(Request $request) {

        $voucher = Voucher::find($request->voucher_id);
        $data = $request->all();
        $data['expired_date'] = Carbon::createFromTimestampMs($data['expired_date']);
        
        /* For profile Image */
        if ($request->hasFile('image')) {
            $checkImage = $model->validateImage($data);
            if ($checkImage->fails()) {
                unset($data['image']);

                return $this->returnError($checkImage->errors()->first(), NULL, true);
            }
            $fileName = time() . '.' . $data['image']->getClientOriginalExtension();
            $storeFile = $data['image']->storeAs($model->profilePhotoPath, $fileName, $model->fileSystem);

            if ($storeFile) {
                $data['image'] = $fileName;
            }
        }
        
        $voucher->update($data);

        return $this->returnSuccess(__($this->successMsg['voucher.update']), $voucher);
    }

    public function getVouchers() {

        $vouchers = Voucher::all();
        return $this->returnSuccess(__($this->successMsg['voucher.get']), $vouchers);
    }

    public function shareVoucher(Request $request) {

        $model = new VoucherShop();
        $data = $request->all();

        $checks = $model->validator($data);
        if ($checks->fails()) {
            return $this->returnError($checks->errors()->first(), NULL, true);
        }
        
        $is_exist = $model->where(['shop_id' => $data['shop_id'], 'voucher_id' => $data['voucher_id']])->first();
        if(!empty($is_exist)) {
            return $this->returnSuccess(__($this->successMsg['voucher.shared']), $is_exist);
        }
        $voucher = $model->create($data);

        return $this->returnSuccess(__($this->successMsg['voucher.share']), $voucher);
    }

    public function addServicesToVoucher(Request $request) {

        DB::beginTransaction();
        try {
            $model = new UserVoucher();
            foreach ($request->services as $key => $service) {

                $service['user_voucher_price_id'] = $request->user_voucher_price_id;
                $checks = $model->validator($service);
                if ($checks->fails()) {
                    return $this->returnError($checks->errors()->first(), NULL, true);
                }
                $voucherServices[] = UserVoucher::create($service);
            }
            DB::commit();
            return $this->returnSuccess(__($this->successMsg['voucher.add.services']), $voucherServices);
            
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function addPack(Request $request) {

        DB::beginTransaction();
        try {
            $model = new Pack();
            $data = $request->all();
            $data['number'] = generateRandomString();
            $data['expired_date'] = Carbon::createFromTimestampMs($data['expired_date']);
            
            $checks = $model->validator($data);
            if ($checks->fails()) {
                return $this->returnError($checks->errors()->first(), NULL, true);
            }

            /* For profile Image */
            if ($request->hasFile('image')) {
                $checkImage = $model->validateImage($data);
                if ($checkImage->fails()) {
                    unset($data['image']);

                    return $this->returnError($checkImage->errors()->first(), NULL, true);
                }
                $fileName = time() . '.' . $data['image']->getClientOriginalExtension();
                $storeFile = $data['image']->storeAs($model->profilePhotoPath, $fileName, $model->fileSystem);

                if ($storeFile) {
                    $data['image'] = $fileName;
                }
            }
            $pack = $model->create($data);
            $packServiceModel = new PackService();
            if(!empty($data['service_id'])) {
                foreach ($data['service_id'] as $key => $value) {
                    $service = [
                        'service_id' => $value,
                        'service_timing_id' => $data['service_timing_id'][$key],
                        'pack_id' => $pack->id
                    ];
                    $checks = $packServiceModel->validator($service);
                    if ($checks->fails()) {
                        return $this->returnError($checks->errors()->first(), NULL, true);
                    }
                    $packServiceModel->create($service);
                }
            }
            DB::commit();
            return $this->returnSuccess(__($this->successMsg['pack.add']), $pack);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollback();
            throw $e;
        }
    }
    
    public function sharePack(Request $request) {

        $model = new PackShop();
        $data = $request->all();

        $checks = $model->validator($data);
        if ($checks->fails()) {
            return $this->returnError($checks->errors()->first(), NULL, true);
        }
        $is_exist = $model->where(['shop_id' => $data['shop_id'], 'pack_id' => $data['pack_id']])->first();
        if(!empty($is_exist)) {
            return $this->returnSuccess(__($this->successMsg['pack.shared']), $is_exist);
        }
        $pack = $model->create($data);

        return $this->returnSuccess(__($this->successMsg['pack.share']), $pack);
    }
    
    public function getPacks(){
        
        $packs = Pack::with('services')->get();
        return $this->returnSuccess(__($this->successMsg['pack.get']), $packs);
    }        
    
    public function signIn(Request $request) {
        $data = $request->all();
        $email = (!empty($data['email'])) ? $data['email'] : NULL;
        $password = (!empty($data['password'])) ? $data['password'] : NULL;


        if (empty($email)) {
            return $this->returnError($this->errorMsg['loginEmail']);
        } elseif (empty($password)) {
            return $this->returnError($this->errorMsg['loginPass']);
        }

        if (!empty($email) && !empty($password)) {

            $user = Superadmin::with('country','city')->where(['email' => $email])->first();
            if (!empty($user) && Hash::check($password, $user->password)) {
                return $this->returnSuccess(__($this->successMsg['login']), $user);
            } else {
                return $this->returnError($this->errorMsg['loginBoth']);
            }
        }
        return $this->returnNull();
    }
    
    public function updateProfile(Request $request) {
        
        $adminModel = new Superadmin();
        $data = $request->all();
        
        $admin = $adminModel->find($data['superadmin_id']);
        if(empty($admin)) {
            return $this->returnError($this->errorMsg['admin.not.found']);
        }
        
        /* For profile Image */
        if ($request->hasFile('profile_photo')) {
            $checkImage = $adminModel->validatePhoto($data);
            if ($checkImage->fails()) {
                unset($data['profile_photo']);

                return $this->returnError($checkImage->errors()->first(), NULL, true);
            }
            $fileName = time().'.' . $data['profile_photo']->getClientOriginalExtension();
            $storeFile = $data['profile_photo']->storeAs($adminModel->profilePhotoPath, $fileName, $adminModel->fileSystem);

            if ($storeFile) {
                $data['profile_photo'] = $fileName;
            }
        }
        $checks = $adminModel->validator($data, $admin->id, true);
        if ($checks->fails()) {
            return $this->returnError($checks->errors()->first(), NULL, true);
        }
        
        if(isset($data['tel_number']) && !empty($data['tel_number'])) {
            if($admin->tel_number != $data['tel_number']) {
                $data['is_mobile_verified'] = Superadmin::IS_NOT_VERIFIED;
            }
        }
        if(isset($data['email']) && !empty($data['email'])) {
            if($admin->email != $data['email']) {
                $data['is_email_verified'] = Superadmin::IS_NOT_VERIFIED;
            }
        }
        
        $admin->update($data);
        $admin = $adminModel->with('country','city')->find($data['superadmin_id']);
        return $this->returnSuccess(__($this->successMsg['edit.profile']), $admin);
    }
    
    public function getDetails(Request $request) {
        
        $admin = Superadmin::with('country','city')->where('id',$request->superadmin_id)->first();
        if(empty($admin)) {
            return $this->returnError($this->errorMsg['admin.not.found']);
        }
        
        return $this->returnSuccess(__($this->successMsg['details.found']), $admin);
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
    
    public function verifyEmail(Request $request)
    {
        $data           = $request->all();
        $model          = new Superadmin();
        $modelEmailOtp  = new SuperAdminEmailOtp();

        $id      = (!empty($data['admin_id'])) ? $data['admin_id'] : 0;
        $getAdmin = $model->find($id);

        if (!empty($getAdmin)) {
            $emailId = (!empty($data['email'])) ? $data['email'] : NULL;

            // Validate
            $data = [
                'admin_id' => $id,
                'otp'          => 1434,
                'email'        => $emailId,
                'is_send'      => '0'
            ];

            $validator = $modelEmailOtp->validate($data);
            if ($validator['is_validate'] == '0') {
                return $this->returns($validator['msg'], NULL, true);
            }

            if ($emailId == $getAdmin->email && $getAdmin->is_email_verified == '1') {
                $this->errorMsg['error.email.already.verified'] = $this->errorMsg['error.email.already.verified'] . $emailId . $this->errorMsg['error.email.id'];

                return $this->returns('error.email.already.verified', NULL, true);
            }

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

            $getData = $modelEmailOtp->where(['admin_id' => $id])->get();

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
            return $this->returns('error.admin.id', NULL, true);
        }

        return $this->returns('success.email.sent', collect([]));
    }

    public function compareOtpEmail(Request $request)
    {
        $data       = $request->all();
        $model      = new SuperAdminEmailOtp();
        $modelUser  = new Superadmin();

        $adminId = (!empty($data['admin_id'])) ? $data['admin_id'] : 0;
        $otp    = (!empty($data['otp'])) ? $data['otp'] : NULL;

        if (empty($otp)) {
            return $this->returns('error.otp', NULL, true);
        }

        if (strtolower(env('APP_ENV') != 'live') && $otp == '1234') {
            $getAdmin = $model->where(['admin_id' => $adminId])->get();
        } else {
            $getAdmin = $model->where(['admin_id' => $adminId, 'otp' => $otp])->get();
        }

        if (!empty($getAdmin) && !$getAdmin->isEmpty()) {
            $getAdmin = $getAdmin->first();

            if ($getAdmin->is_verified == '1') {
                return $this->returns('error.otp.already.verified', NULL, true);
            } else {
                $modelUser->where(['id' => $adminId])->update(['email' => $getAdmin->email, 'is_email_verified' => '1']);

                $model->setIsVerified($getAdmin->id, '1');
            }
        } else {
            return $this->returns('error.otp.wrong', NULL, true);
        }

        return $this->returns('success.email.otp.compare', collect([]));
    }

    public function verifyMobile(Request $request)
    {
        $data   = $request->all();

        /* TODO all things like email otp after get sms gateway. */

        return $this->returns('success.sms.sent', collect([]));
    }
    
    public function compareOtpSms(Request $request)
    {
        $data   = $request->all();
        $model  = new Superadmin();

        /* TODO all things like email otp compare after get sms gateway. */
        $adminId = (!empty($data['admin_id'])) ? $data['admin_id'] : 0;
        $otp    = (!empty($data['otp'])) ? $data['otp'] : NULL;

        if (strtolower(env('APP_ENV') != 'live') && $otp == '1234') {
            $model->where(['id' => $adminId])->update(['is_mobile_verified' => '1']);
        } else {
            return $this->returns('error.otp.wrong', NULL, true);
        }

        return $this->returns('success.email.otp.compare', collect([]));
    }
    
    public function addTimingsPricings(Request $request, $service) {
     
        $timingModel = new ServiceTiming();
        $pricingModel = new ServicePricing();
        
        $data = $request->all();
        $serviceData = [];
        foreach ($data['timings'] as $key => $value) { 
            $timingData = [
                'time' => $value,
                'service_id' => $service->id
            ];
            $checks = $timingModel->validator($timingData);
            if ($checks->fails()) {
                return ['error' => $checks->errors()->first(), 'data' => NULL];
            }
            $timing = $timingModel->create($timingData);
            $pricingData = [
                'service_id' => $service->id,
                'service_timing_id' => $timing->id,
                'price' => $data['pricings'][$key],
                'cost' => $data['cost'][$key]
            ];
            $check = $pricingModel->validator($pricingData);
            if ($check->fails()) {
                return ['error' => $check->errors()->first(), 'data' => NULL];
            }
            $pricingModel->create($pricingData);
            $serviceData[] = [
                'time' => $value,
                'service_id' => $service->id,
                'service_timing_id' => $timing->id,
                'price' => $data['pricings'][$key],
                'cost' => $data['cost'][$key]
            ];
        }
        
        return $serviceData;
    }
    
    public function addRequirements(Request $request, $service) {
        
        $requirementModel = new ServiceRequirement();
        $data = $request->all();
        $requirementData = [
            'service_id' => $service->id,
            'massage_through' => $data['massage_through'],
            'special_tools' => $data['special_tools'],
            'platform' => $data['platform'],
            'oil_usage' => $data['oil_usage']
        ];
        $checks = $requirementModel->validator($requirementData);
        if ($checks->fails()) {
            return ['error' => $checks->errors()->first(), 'data' => NULL];
        }
        $requirement = $requirementModel->create($requirementData);
        return $requirement;
    }
    
    public function addImages(Request $request, $service, $key, $type) {
        
        $imgData = [];
        if (!empty($request->$key)) {
            $imageModel = new ServiceImage();        
            
            if ($request->hasfile($key)) {
                foreach ($request->file($key) as $file) {
                    
                    $allowedfileExtension=['jpg','png','jpeg'];
                    $name = $file->getClientOriginalExtension();
                    $fileName = mt_rand(). time() . '_' . $service->id . '.' . $name;
                    $check=in_array($name,$allowedfileExtension);

                    if($check) {
                        $image['image'] = $fileName;
                        $image['service_id'] = $service->id;
                        $image['is_featured'] = $type;

                        $storeFile = $file->storeAs($imageModel->directory, $fileName, $imageModel->fileSystem);
                        if($storeFile) {
                            $check = $imageModel->validator($image);
                            if ($check->fails()) {
                                return ['error' => $check->errors()->first(), 'data' => NULL];
                            }
                            $imageModel->create($image);
                        }
                        $imgData[] = $image;
                    } else {
                        return ['error' => $this->errorMsg['error.mimes'], 'data' => NULL];
                    }
                }
            }
        }
        return $imgData;
    }
    
    public function addFeatureImage(Request $request, $service) {

        $data = $request->all();
        $imageModel = new ServiceImage();
        $imgData = [];

        if (!empty($data['featured_image']) && $data['featured_image'] instanceof UploadedFile) {
            $checkImage = $imageModel->validateFeaturedImage($data);

            if ($checkImage->fails()) {
                unset($data['featured_image']);
                return ['isError' => true, 'message' => $checkImage->errors()->first()];
            }

            $extension = $data['featured_image']->getClientOriginalExtension();
            $extension = empty($extension) ? $data['featured_image']->extension() : $extension;
            $fileName = mt_rand() . time() . '_' . $service->id . '.' . $extension;
            $storeFile = $data['featured_image']->storeAs($imageModel->directory, $fileName, $imageModel->fileSystem);

            if ($storeFile) {
                $image['image'] = $fileName;
                $image['service_id'] = $service->id;
                $image['is_featured'] = ServiceImage::IS_FEATURED;
                $check = $imageModel->validator($image);
                if ($check->fails()) {
                    return ['error' => $check->errors()->first(), 'data' => NULL];
                }
                $imageModel->create($image);
                $imgData[] = $image;
            }
        }
        return $imgData;
    }

    public function addService(Request $request) {
        
        DB::beginTransaction();
        try {
            
            $data   = $request->all();
            $serviceData = [
                'english_name' => $data['english_name'],
                'portugese_name' => $data['portugese_name'],
                'short_description' => $data['short_description'],
                'priority' => $data['priority'],
                'expenses' => $data['expenses'],
                'service_type' => $data['service_type']
            ];
            $model  = new Service();
            
            $checks = $model->validator($serviceData);
            if ($checks->fails()) {
                return $this->returnError($checks->errors()->first(), NULL, true);
            }
            $service = $model->create($serviceData);
            
            $timingPricing = $this->addTimingsPricings($request, $service);
            if (!empty($timingPricing['error'])) {
                return ['isError' => true, 'message' => $timingPricing['error']];
            }
            
            $requirements = $this->addRequirements($request, $service);
            if (!empty($requirements['error'])) {
                return ['isError' => true, 'message' => $requirements['error']];
            }
                                    
            $featuredImages = $this->addFeatureImage($request, $service);
            if (!empty($featuredImages['error'])) {
                return ['isError' => true, 'message' => $featuredImages['error']];
            }
            
            $galleryImages = $this->addImages($request, $service, 'gallery', ServiceImage::IS_NOT_FEATURED);
            if (!empty($galleryImages['error'])) {
                return ['isError' => true, 'message' => $galleryImages['error']];
            }
            
            $service = Service::with('timings', 'pricings', 'images', 'requirement')->where('id', $service->id)->first();
            DB::commit();
            return $this->returnSuccess(__($this->successMsg['service.add']), $service);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollback();
            throw $e;
        }
    }
    
    public function getMassages() {
        
        $massages = Service::with('timings', 'pricings', 'images', 'requirement')->where('service_type', Service::MASSAGE)->get();
        foreach ($massages as $key => $massage) {
            $massage->name = $massage->english_name;
        }
        return $this->returnSuccess(__($this->successMsg['massages']), $massages);
    }
    
    public function getTherapies() {
        
        $therapies = Service::with('timings', 'pricings', 'images', 'requirement')->where('service_type', Service::THERAPY)->get();
        foreach ($therapies as $key => $therapy) {
            $therapy->name = $therapy->english_name;
        }
        return $this->returnSuccess(__($this->successMsg['therapies']), $therapies);
    }
    
    public function deleteOtp(Request $request) {
        
        $otps = ForgotOtp::where(['model_id' => $request->user_id, 'model' => Superadmin::ADMIN])->get();
        foreach ($otps as $key => $otp) {
            $otp->delete();
        }
        return true;
    }
    
    public function forgotPassword(Request $request) {

        $admin = Superadmin::where('tel_number', $request->mobile_number)->first();

        if (empty($admin)) {
            return $this->returnError($this->errorMsg['admin.not.found']);
        }

        $request->request->add(['user_id' => $admin->id]);
        $this->deleteOtp($request);
        
        $data = [
            'model_id' => $admin->id,
            'model' => Superadmin::ADMIN,
            'otp' => 1234,
            'mobile_number' => $request->mobile_number,
            'mobile_code' => $request->mobile_code,
        ];

        ForgotOtp::create($data);
        return $this->returnSuccess(__($this->successMsg['success.otp']), ['user_id' => $admin->id, 'otp' => 1234]);
    }
    
    public function resetPassword(Request $request) {
        
        $admin = Superadmin::find($request->user_id);

        if (empty($admin)) {
            return $this->returnError($this->errorMsg['admin.not.found']);
        }
        
        $admin->update(['password' => Hash::make($request->password)]);
        $this->deleteOtp($request);
        
        return $this->returnSuccess(__($this->successMsg['success.reset.password']), $admin);
    }

    public function verifyOtp(Request $request) {
        
        $is_exist = ForgotOtp::where(['model_id' => $request->user_id, 'model' => Superadmin::ADMIN, 'otp' => $request->otp])->first();
        
        if(empty($is_exist)) {
            return $this->returnError($this->errorMsg['otp.not.found']);
        }
        return $this->returnSuccess(__($this->successMsg['success.otp.verified']), $is_exist);
    }
    
    public function cancelBooking(Request $request) {

        $request->request->add(['bookings_filter' => array(Booking::BOOKING_CANCELLED)]);
        $bookingModel = new Booking();
        $cancelBooking = $bookingModel->getGlobalQuery($request);        

        return $this->returnSuccess(__($this->successMsg['cancelled.booking']), $cancelBooking);
    }
    
    public function pastBooking(Request $request) {

        $request->request->add(['bookings_filter' => array(Booking::BOOKING_PAST)]);
        $bookingModel = new Booking();
        $pastBooking = $bookingModel->getGlobalQuery($request);

        return $this->returnSuccess(__($this->successMsg['past.booking']), $pastBooking);
    }
    
    public function futureBooking(Request $request) {

        $request->request->add(['bookings_filter' => array(Booking::BOOKING_FUTURE)]);
        $bookingModel = new Booking();
        $futureBooking = $bookingModel->getGlobalQuery($request);

        return $this->returnSuccess(__($this->successMsg['future.booking']), $futureBooking);
    }
    
    public function pendingBooking(Request $request) {

        $request->request->add(['bookings_filter' => array(Booking::BOOKING_WAITING)]);
        $bookingModel = new Booking();
        $futureBooking = $bookingModel->getGlobalQuery($request);

        return $this->returnSuccess(__($this->successMsg['pending.booking']), $futureBooking);
    }
    
    public function printBookingDetails(Request $request) {
        
        $shopModel = new Shop();
        $bookingDetails = $shopModel->printBooking($request);
        
        if (!empty($bookingDetails['isError']) && !empty($bookingDetails['message'])) {
            return $this->returnError($bookingDetails['message'], NULL, true);
        }
        
        return $this->returnSuccess(__($this->successMsg['print.booking']), $bookingDetails);
    }
    
}
