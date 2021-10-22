<?php

namespace App\Http\Controllers\Shops\Manager;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use App\TherapistWorkingSchedule;
use App\Manager;
use Illuminate\Support\Facades\Hash;
use App\Therapist;
use App\News;
use App\TherapistShop;
use App\Shop;
use App\Service;
use App\Libraries\CommonHelper;
use App\ManagerEmailOtp;
use App\Booking;
use App\User;
use App\PackShop;
use App\VoucherShop;
use App\TherapistUserRating;
use App\ForgotOtp;
use App\TherapyQuestionnaireAnswer;
use App\TherapistDocument;
use App\TherapistSelectedService;
use Carbon\CarbonPeriod;

class ManagerController extends BaseController {

    public $errorMsg = [
        'loginEmail' => "Please provide email.",
        'loginPass' => "Please provide password.",
        'loginBoth' => "Manager email or password seems wrong.",
        'news.not.found' => "News not found.",
        'center.not.found' => "Shop not found.",
        'error.otp' => 'Please provide OTP properly.',
        'error.otp.wrong' => 'OTP seems wrong.',
        'error.otp.already.verified' => 'OTP already verified.',
        'error.manager.id' => 'Please provide valid manager id.',
        'error.email.already.verified' => 'This user email already verified with this.',
        'manager.not.found' => 'Manager not found.',
        'not.verified' => 'Your account is not verified yet.',
        'ans.not.found' => 'Therapy questionnaries answer not found.',
        'data.not.found' => 'Data not found.',
        'therapist.not.found' => 'Therapist not found.',
        'schedule.not.found' => 'Schedule data not found.',
        'from.date.not.found' => 'Please select from date.',
        'to.date.not.found' => 'Please select to date.',
    ];
    
    public $successMsg = [
        'login' => "Login successfully !",
        'therapist.availability' => 'Therapist availability added successfully !',
        'news' => 'News data found successfully !',
        'news.details' => 'News details found successfully !',
        'new.therapist' => 'New therapist created successfully !',
        'existing.therapist' => 'Existing therapist added successfully to this shop!',
        'details' => 'Dashboard data found successfully!',
        'therapists.details' => 'Therapists data found successfully!',
        'therapists.all' => 'Therapists found successfully!',
        'massages.found' => 'Massages found successfully!',
        'therapies.found' => 'Therapies found successfully!',
        'booking.details' => 'Booking details found successfully!',
        'success.email.otp.compare' => 'OTP matched successfully !',
        'success.sms.sent' => 'SMS sent successfully !',
        'success.email.sent' => 'Email sent successfully !',
        'edit.profile' => 'Profile updated successfully!',
        'get.profile' => 'Manager profile found successfully!',
        'users.get' => 'Users found successfully!',
        'schedule.data.found' => 'Time Table data found successfully',
        'success.packs.get' => 'Packs found successfully',
        'success.vouchers.get' => 'Vouchers found successfully',
        'success.otp' => 'Otp sent successfully !',
        'success.reset.password' => 'Password reset successfully !',
        'success.otp.verified' => 'Otp verified successfully !',
        'questionnaries.answer.saved' => 'Therapy questionnaries answer saved successfully !',
        'document.accept' => 'Document accepted successfully !',
        'document.reject' => 'Document rejected successfully !',
        'document.delete' => 'Document deleted successfully !',
        'service.add' => 'Service added successfully !',
        'service.delete' => 'Service deleted successfully !',
    ];

    public function addAvailabilities(Request $request) {

        DB::beginTransaction();
        try {

            $data = $request->all();
            $therapist = Therapist::find($data['therapist_id']);
            $scheduleModel = new TherapistWorkingSchedule();
            $schedule = [];
            
            if(empty($therapist)) {
                return $this->returnError($this->errorMsg['therapist.not.found']);
            }
            if(empty($data['from_date'])) {
                return $this->returnError($this->errorMsg['from.date.not.found']);
            }
            if(empty($data['to_date'])) {
                return $this->returnError($this->errorMsg['to.date.not.found']);
            }
            $from_date = Carbon::createFromTimestampMs($data['from_date']);
            $to_date = Carbon::createFromTimestampMs($data['to_date']);
            $period = CarbonPeriod::create($from_date, $to_date)->toArray();
            
            if(!empty($period)) {
                foreach ($period as $key => $date) {
                    $scheduleData = [
                        'shift_id' => $data['shift'],
                        'date' => $date->format('Y-m-d'),
                        'therapist_id' => $data['therapist_id'],
                        'is_working' => TherapistWorkingSchedule::WORKING,
                        'shop_id' => $therapist->shop_id
                    ];
                    $checks = $scheduleModel->validator($scheduleData);
                    if ($checks->fails()) {
                        return $this->returnError($checks->errors()->first(), NULL, true);
                    }
                    $schedule[] = $scheduleModel->updateOrCreate($scheduleData, $scheduleData);
                }
                DB::commit();
                return $this->returnSuccess(__($this->successMsg['therapist.availability']), $schedule);
            }
            return $this->returnError($this->errorMsg['schedule.not.found']);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollback();
            throw $e;
        }
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

            $manager = Manager::with('country', 'province', 'city')->where(['email' => $email])->first();
            
            if (!empty($manager) && Hash::check($password, $manager->password)) {
                return $this->returnSuccess(__($this->successMsg['login']), $manager);
            } else {
                return $this->returnError($this->errorMsg['loginBoth']);
            }
        } else {
            return $this->returnError($this->errorMsg['not.verified']);
        }
        return $this->returnNull();
    }
    
    public function getNews(Request $request) {

        $data = News::with('therapistsNews')->where('manager_id', $request->manager_id)->whereNull('deleted_at');
        $filter = !empty($request->filter) ? $request->filter : News::TODAY;
        $now = Carbon::now();

        if ($filter == News::TODAY) {
            $data->whereDate('created_at', $now->format('Y-m-d'));
        }
        if ($filter == News::YESTERDAY) {
            $data->whereDate('created_at', $now->subDays(1));
        }
        if ($filter == News::THIS_WEEK) {
            $weekStartDate = $now->startOfWeek()->format('Y-m-d');
            $weekEndDate = $now->endOfWeek()->format('Y-m-d');
            $data->whereDate('created_at', '>=', $weekStartDate)->whereDate('created_at', '<=', $weekEndDate);
        }
        if ($filter == News::CURRENT_MONTH) {
            $data->whereMonth('created_at', $now->month);
        }
        if ($filter == News::LAST_7_DAYS) {
            $todayDate = $now->format('Y-m-d');
            $agoDate = $now->subDays(7)->format('Y-m-d');           
            $data->whereDate('created_at', '>=', $agoDate)->whereDate('created_at', '<=', $todayDate);
        }
        if ($filter == News::LAST_14_DAYS) {
            $todayDate = $now->format('Y-m-d');
            $agoDate = $now->subDays(14)->format('Y-m-d');
            $data->whereDate('created_at', '>=', $agoDate)->whereDate('created_at', '<=', $todayDate);
        }
        if ($filter == News::LAST_30_DAYS) {
            $todayDate = $now->format('Y-m-d');
            $agoDate = $now->subDays(30)->format('Y-m-d');
            $data->whereDate('created_at', '>=', $agoDate)->whereDate('created_at', '<=', $todayDate);
        }
        if ($filter == News::CUSTOM) {
            $date = $date = Carbon::createFromTimestampMs($request->date);
            $data->whereDate('created_at', $date);
        }
        $data = $data->get();
        $allTherapist = Therapist::where('shop_id', $request->shop_id)->get()->count();

        $allNews = [];
        if (!empty($data)) {
            foreach ($data as $key => $news) {

                $newsData = [
                    'id' => $news['id'],
                    'title' => $news['title'],
                    'sub_title' => $news['sub_title'],
                    'description' => $news['description'],
                    'manager_id' => $news['manager_id'],
                    'created_at' => $news['created_at'],
                ];
                $newsData['read'] = $news['therapistsNews']->count();
                $newsData['unread'] = $newsData['read'] == $allTherapist ? 0 : $allTherapist - $newsData['read'];

                array_push($allNews, $newsData);
                unset($newsData);
            }
        }
        return $this->returnSuccess(__($this->successMsg['news']), $allNews);
    }

    public function newsDetails(Request $request) {
        
        $news = News::with('therapistsNews')->where(['id' => $request->news_id, 'manager_id' => $request->manager_id])->first();
        $filter = $request->filter ? $request->filter : 0;
        if(empty($news)) {
            return $this->returnSuccess(__($this->errorMsg['news.not.found']));
        }
        $allTherapist = Therapist::where('shop_id', $request->shop_id)->get();
        $read = $news->therapistsNews->count();
        $count = $allTherapist->count();
        $unread = $count == 0 ? 0 : $count - $read ;
        
        $therapists = [];
        if($filter == 0) {
            foreach ($news->therapistsNews as $key => $therapist) {
                $therapists[] = [
                    "therapist_id" => $therapist->therapists->id,
                    "therapist_name" => $therapist->therapists->name,
                    "profile_photo" => $therapist->therapists->profile_photo
                ];
            }
        } else {
            $therapist_read = $news->therapistsNews->pluck('therapist_id')->toArray();
            foreach ($allTherapist as $key => $therapist) {
                if (!in_array($therapist->id, $therapist_read)) {
                    $therapists[] = [
                        "therapist_id" => $therapist->id,
                        "therapist_name" => $therapist->name,
                        "profile_photo" => $therapist->profile_photo
                    ];
                }
            }
            
        }
        $newsData = [
            'id' => $news['id'],
            'title' => $news['title'],
            'sub_title' => $news['sub_title'],
            'description' => $news['description'],
            'manager_id' => $news['manager_id'],
            'created_at' => $news['created_at'],
            'read' => $read,
            'unread' => $unread,
            'therapists' => $therapists
        ];
        
        return $this->returnSuccess(__($this->successMsg['news.details']), $newsData);
    }
    
    public function newTherapist(Request $request) {
        
        DB::beginTransaction();
        try {

            $model = new Therapist();
            $data = $request->all();
            $checks = $model->validator($data);
            if ($checks->fails()) {
                return $this->returnError($checks->errors()->first(), NULL, true);
            }
            $data['password'] = Hash::make($data['password']);
            $therapist = $model->create($data);
            
            $shopData = [
                'therapist_id' => $therapist->id,
                'shop_id' => $therapist->shop_id
            ];
            $therapist_model = new TherapistShop();
            $checks = $therapist_model->validator($shopData);
            if ($checks->fails()) {
                return $this->returnError($checks->errors()->first(), NULL, true);
            }
            $therapist_model->create($shopData);
            
            DB::commit();
            return $this->returnSuccess(__($this->successMsg['new.therapist']), $therapist);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollback();
            throw $e;
        }
    }
    
    public function existingTherapist(Request $request) {
        
        $model = new TherapistShop();
        $data = $request->all();
        $checks = $model->validator($data);
        if ($checks->fails()) {
            return $this->returnError($checks->errors()->first(), NULL, true);
        }
        
        $therapist = $model->updateOrCreate($data, $data);
        return $this->returnSuccess(__($this->successMsg['existing.therapist']), $therapist);
    }
    
    public function getInfo(Request $request) {
        
        $shopModel = new Shop();
        $shop = Shop::find($request->shop_id);
        if (empty($shop)) {
            return $this->returnError($this->errorMsg['center.not.found']);
        } 
        $data = $shopModel->dashboardInfo($request);
        
        return $this->returnSuccess(__($this->successMsg['details']), $data);
    }
    
    public function getTherapists(Request $request) {
        
        $model = new Therapist();
        $therapists = $model->getTherapist($request);
        return $this->returnSuccess(__($this->successMsg['therapists.details']), $therapists);
    }
    
    public function getMassages(Request $request) {
        
        $request->request->add(['isGetAll' => true, 'type' => Service::MASSAGE]);
        $massages = CommonHelper::getAllService($request);
        
        return $this->returnSuccess(__($this->successMsg['massages.found']), $massages);
    }
    
    public function getTherapies(Request $request) {
        
        $request->request->add(['isGetAll' => true, 'type' => Service::THERAPY]);
        $therapies = CommonHelper::getAllService($request);
        
        return $this->returnSuccess(__($this->successMsg['therapies.found']), $therapies);
    }
    
    public function getBookings(Request $request) {
        
        $shopModel = new Shop();
        $bookings = $shopModel->getBookings($request);      
        
        return $this->returnSuccess(__($this->successMsg['booking.details']), $bookings);
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
        $model          = new Manager();
        $modelEmailOtp  = new ManagerEmailOtp();

        $id      = (!empty($data['manager_id'])) ? $data['manager_id'] : 0;
        $getManager = $model->find($id);

        if (!empty($getManager)) {
            $emailId = (!empty($data['email'])) ? $data['email'] : NULL;

            // Validate
            $data = [
                'manager_id' => $id,
                'otp'          => 1434,
                'email'        => $emailId,
                'is_send'      => '0'
            ];

            $validator = $modelEmailOtp->validate($data);
            if ($validator['is_validate'] == '0') {
                return $this->returns($validator['msg'], NULL, true);
            }

            if ($emailId == $getManager->email && $getManager->is_email_verified == '1') {
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

            $getData = $modelEmailOtp->where(['manager_id' => $id])->get();

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
            return $this->returns('error.manager.id', NULL, true);
        }

        return $this->returns('success.email.sent', collect([]));
    }

    public function compareOtpEmail(Request $request)
    {
        $data       = $request->all();
        $model      = new ManagerEmailOtp();
        $modelUser  = new Manager();

        $managerId = (!empty($data['manager_id'])) ? $data['manager_id'] : 0;
        $otp    = (!empty($data['otp'])) ? $data['otp'] : NULL;

        if (empty($otp)) {
            return $this->returns('error.otp', NULL, true);
        }

        if (strtolower(env('APP_ENV') != 'live') && $otp == '1234') {
            $getManager = $model->where(['manager_id' => $managerId])->get();
        } else {
            $getManager = $model->where(['manager_id' => $managerId, 'otp' => $otp])->get();
        }

        if (!empty($getManager) && !$getManager->isEmpty()) {
            $getManager = $getManager->first();

            if ($getManager->is_verified == '1') {
                return $this->returns('error.otp.already.verified', NULL, true);
            } else {
                $modelUser->where(['id' => $managerId])->update(['email' => $getManager->email, 'is_email_verified' => '1']);

                $model->setIsVerified($getManager->id, '1');
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
        $model  = new Manager();

        /* TODO all things like email otp compare after get sms gateway. */
        $managerId = (!empty($data['manager_id'])) ? $data['manager_id'] : 0;
        $otp    = (!empty($data['otp'])) ? $data['otp'] : NULL;

        if (strtolower(env('APP_ENV') != 'live') && $otp == '1234') {
            $model->where(['id' => $managerId])->update(['is_mobile_verified' => '1']);
        } else {
            return $this->returns('error.otp.wrong', NULL, true);
        }

        return $this->returns('success.email.otp.compare', collect([]));
    }
    
    public function updateProfile(Request $request) {
        
        $managerModel = new Manager();
        $data = $request->all();
        
        $manager = $managerModel->find($data['manager_id']);
        if(empty($manager)) {
            return $this->returnError($this->errorMsg['manager.not.found']);
        }
        
        /* For profile Image */
        if ($request->hasFile('image')) {
            $checkImage = $managerModel->validatePhoto($data);
            if ($checkImage->fails()) {
                unset($data['image']);

                return $this->returnError($checkImage->errors()->first(), NULL, true);
            }
            $fileName = time().'.' . $data['image']->getClientOriginalExtension();
            $storeFile = $data['image']->storeAs($managerModel->profilePhotoPath, $fileName, $managerModel->fileSystem);

            if ($storeFile) {
                $data['image'] = $fileName;
            }
        }
        $checks = $managerModel->validator($data, $manager->id, true);
        if ($checks->fails()) {
            return $this->returnError($checks->errors()->first(), NULL, true);
        }
        
        if(isset($data['tel_number']) && !empty($data['tel_number'])) {
            if($manager->tel_number != $data['tel_number']) {
                $data['is_mobile_verified'] = Manager::IS_NOT_VERIFIED;
            }
        }
        if(isset($data['email']) && !empty($data['email'])) {
            if($manager->email != $data['email']) {
                $data['is_email_verified'] = Manager::IS_NOT_VERIFIED;
            }
        }
        
        $manager->update($data);
        $manager = $managerModel->with('country', 'province', 'city')->find($data['manager_id']);
        return $this->returnSuccess(__($this->successMsg['edit.profile']), $manager);
    }
    
    public function getProfile(Request $request) {
        
        $manager = Manager::with('country', 'province', 'city')->find($request->manager_id);
        if(empty($manager)) {
            return $this->returnError($this->errorMsg['manager.not.found']);
        }
        return $this->returnSuccess(__($this->successMsg['get.profile']), $manager);
    }
    
    public function getUsers(Request $request) {
        
        $dateFilter = !empty($request->date_filter) ? $request->date_filter : Booking::TODAY;
        $shopId = $request->shop_id;

        $appUsers = DB::table('booking_massages')
                ->join('booking_infos', 'booking_infos.id', '=', 'booking_massages.booking_info_id')
                ->join('bookings', 'bookings.id', '=', 'booking_infos.booking_id')
                ->join('users', 'users.id', '=', 'bookings.user_id')
                ->select('booking_massages.*', 'booking_infos.*', 'booking_infos.*')
                ->where('bookings.book_platform', (string) Booking::BOOKING_PLATFORM_APP)
                ->where('users.shop_id', $shopId);
                
        $guestUsers = User::where(['is_guest' => (string) User::IS_GUEST, 'shop_id' => $shopId]);
        $registeredUsers = User::where(['is_guest' => (string) User::IS_NOT_GUEST, 'shop_id' => $shopId]);
        
        if(!empty($dateFilter)) {
            
            $now = Carbon::now();
            if ($dateFilter == Booking::YESTERDAY) {
                $appUsers->whereDate('users.created_at', Carbon::yesterday()->format('Y-m-d'));
                $guestUsers->whereDate('created_at', Carbon::yesterday()->format('Y-m-d'));
                $registeredUsers->whereDate('created_at', Carbon::yesterday()->format('Y-m-d'));
            }
            if ($dateFilter == Booking::TODAY) {
                $appUsers->whereDate('users.created_at', $now->format('Y-m-d'));
                $guestUsers->whereDate('created_at', $now->format('Y-m-d'));
                $registeredUsers->whereDate('created_at', $now->format('Y-m-d'));
            }
            if ($dateFilter == Booking::THIS_WEEK) {
                $weekStartDate = $now->startOfWeek()->format('Y-m-d');
                $weekEndDate = $now->endOfWeek()->format('Y-m-d');

                $appUsers->whereDate('users.created_at', '>=', $weekStartDate)->whereDate('users.created_at', '<=', $weekEndDate);
                $guestUsers->whereDate('created_at', '>=', $weekStartDate)->whereDate('created_at', '<=', $weekEndDate);
                $registeredUsers->whereDate('created_at', '>=', $weekStartDate)->whereDate('created_at', '<=', $weekEndDate);
            }
            if ($dateFilter == Booking::THIS_MONTH) {
                $appUsers->whereMonth('users.created_at', $now->month)
                     ->whereYear('users.created_at', $now->year);
                $guestUsers->whereMonth('created_at', $now->month)
                     ->whereYear('created_at', $now->year);
                $registeredUsers->whereMonth('created_at', $now->month)
                     ->whereYear('created_at', $now->year);
            }
        }
        
        $appUsers = $appUsers->get()->groupBy('bookings.user_id')->count();
        $guestUsers = User::where('is_guest', (string) User::IS_GUEST)->get()->count();
        $registeredUsers = User::where('is_guest', (string) User::IS_NOT_GUEST)->get()->count();
        return $this->returnSuccess(__($this->successMsg['users.get']), ['appUsers' => $appUsers, 'guestUsers' => $guestUsers, 'registeredUsers' => $registeredUsers]);
    }
    
    public function getTimeTable(Request $request) {
        
        $date  = Carbon::createFromTimestampMs($request->date);
        $date = !empty($request->date) ? $date : Carbon::now();
        
        $schedules = TherapistWorkingSchedule::with('shifts', 'therapist:id,name,shop_id')                   
                        ->whereMonth('date', $date->month)
                        ->whereYear('date', $date->year)
                        ->whereHas('therapist', function($q) use($request) {
                                $q->where('shop_id',$request->shop_id);
                        });
                        
//        // 1 for yesterday ,2 for current month, 3 for last 7 days, 4 for last 14 days, 5 for last 30 days
//        if (!empty($filter)) {
//            if ($filter == 1) {
//                $schedules = $schedules->where('date', Carbon::yesterday());
//            } else if ($filter == 2) {
//                $schedules = $schedules->whereMonth('date', Carbon::now()->month);
//            } else if ($filter == 3) {
//                $schedules = $schedules->whereBetween('date', [Carbon::now()->subDays(7), Carbon::now()]);
//            } else if ($filter == 4) {
//                $schedules = $schedules->whereBetween('date', [Carbon::now()->subDays(14), Carbon::now()]);
//            } else if ($filter == 5) {
//                $schedules = $schedules->whereBetween('date', [Carbon::now()->subDays(30), Carbon::now()]);
//            }
//        } else {
//            $schedules = $schedules->whereBetween('date', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
//        }                     
        return $this->returnSuccess(__($this->successMsg['schedule.data.found']), $schedules->get());
    }
    
    public function getPacks(Request $request) {
        
        $manager = Manager::find($request->manager_id);
        if(empty($manager)) {
            return $this->returnError($this->errorMsg['manager.not.found']);
        }
        $packs = PackShop::with('pack')->where('shop_id', $manager->shop_id)->get();
        $packsData = [];
        if(count($packs) > 0) {
            foreach ($packs as $key => $value) {
                $packsData[] = [
                    'id' => $value->pack->id,
                    'name' => $value->pack->name,
                    'sub_title' => $value->pack->sub_title,
                    'number' => $value->pack->number,
                    'image' => $value->pack->image,
                    'total_price' => $value->pack->total_price,
                    'pack_price' => $value->pack->pack_price,
                    'expired_date' => $value->pack->expired_date,
                    'receptionist_id' => $value->pack->receptionist_id,
                    'is_personalized' => $value->pack->is_personalized,
                ];
            }
        }
        
        return $this->returnSuccess(__($this->successMsg['success.packs.get']), $packsData);
    }
    
    public function getVouchers(Request $request) {
        
        $manager = Manager::find($request->manager_id);
        if(empty($manager)) {
            return $this->returnError($this->errorMsg['manager.not.found']);
        }
        $vouchers = VoucherShop::with('voucher')->where('shop_id', $manager->shop_id)->get();
        $vouchersData = [];
        if(count($vouchers) > 0) {
            foreach ($vouchers as $key => $value) {
                $vouchersData[] = [
                    'id' => $value->voucher->id,
                    'name' => $value->voucher->name,
                    'number' => $value->voucher->number,
                    'image' => $value->voucher->image,
                    'price' => $value->voucher->price,
                    'created_at' => strtotime($value->voucher->created_at) * 1000,
                    'expired_date' => $value->voucher->expired_date,
                ];
            }
        }
        
        return $this->returnSuccess(__($this->successMsg['success.vouchers.get']), $vouchersData);
    }
    
    public function deleteOtp(Request $request) {
        
        $otps = ForgotOtp::where(['model_id' => $request->user_id, 'model' => Manager::MANAGER])->get();
        foreach ($otps as $key => $otp) {
            $otp->delete();
        }
        return true;
    }
    
    public function forgotPassword(Request $request) {

        $manager = Manager::where('tel_number', $request->mobile_number)->first();

        if (empty($manager)) {
            return $this->returnError($this->errorMsg['manager.not.found']);
        }
        $request->request->add(['user_id' => $manager->id]);
        $this->deleteOtp($request);
        
        $data = [
            'model_id' => $manager->id,
            'model' => Manager::MANAGER,
            'otp' => 1234,
            'mobile_number' => $request->mobile_number,
            'mobile_code' => $request->mobile_code,
        ];

        ForgotOtp::create($data);
        return $this->returnSuccess(__($this->successMsg['success.otp']), ['user_id' => $manager->id, 'otp' => 1234]);
    }
    
    public function resetPassword(Request $request) {
        
        $manager = Manager::find($request->user_id);

        if (empty($manager)) {
            return $this->returnError($this->errorMsg['manager.not.found']);
        }
        
        $manager->update(['password' => Hash::make($request->password)]);                
        $this->deleteOtp($request);
        
        return $this->returnSuccess(__($this->successMsg['success.reset.password']), $manager);
    }

    public function verifyOtp(Request $request) {
        
        $is_exist = ForgotOtp::where(['model_id' => $request->user_id, 'model' => Manager::MANAGER, 'otp' => $request->otp])->first();
        
        if(empty($is_exist)) {
            return $this->returnError($this->errorMsg['otp.not.found']);
        }
        return $this->returnSuccess(__($this->successMsg['success.otp.verified']), $is_exist);
    }
    
    public function updateQuestionnaries(Request $request) {
        
        $id = $request->answer_id;
        if(empty($id)) {
            $ans = TherapyQuestionnaireAnswer::create($request->all());
        } else {
            $ans = TherapyQuestionnaireAnswer::find($id);
            if(empty($ans)) {
                return $this->returnError($this->errorMsg['ans.not.found']);
            }
            $ans->update($request->all());
        }
        return $this->returnSuccess(__($this->successMsg['questionnaries.answer.saved']), $ans);
    }
    
    public function acceptDocument(Request $request) {
        
        $user = User::find($request->user_id);
        $user->update(['is_document_verified' => User::ACCEPT]);
        return $this->returnSuccess(__($this->successMsg['document.accept']), $user);
    }
    
    public function declineDocument(Request $request) {
        
        $user = User::find($request->user_id);
        $user->update(['is_document_verified' => User::REJECT]);
        return $this->returnSuccess(__($this->successMsg['document.reject']), $user);
    }
    
    public function getAllTherapists(Request $request) {
        
        $therapists = Therapist::with('country', 'city', 'shops');
        $search_val = $request->search_val;
        if(!empty($search_val)) {
            
            if(is_numeric($search_val)) {
                $therapists->where(function($query) use ($search_val) {
                    $query->where('mobile_number', $search_val)
                            ->orWhere('nif', $search_val);
                });
            } else {
                $therapists->where(function($query) use ($search_val) {
                    $query->where('name', 'like', $search_val.'%')
                            ->orWhere('email', 'like', $search_val.'%');
                });
            }
        }
        
        $therapists = $therapists->get();
        foreach ($therapists as $key => $row) {
            
            $ratings = TherapistUserRating::where(['model_id' => $row->id, 'model' => 'App\Therapist'])->get();

            $cnt = $rates = $avg = 0;
            if ($ratings->count() > 0) {
                foreach ($ratings as $i => $rating) {
                    $rates += $rating->rating;
                    $cnt++;
                }
                $avg = $rates / $cnt;
            }
            
            $row['avg'] = number_format($avg, 2);
        }
        return $this->returnSuccess(__($this->successMsg['therapists.all']), $therapists);
    }
    
    public function deleteDocument(Request $request) {
        
        $document = TherapistDocument::find($request->document_id);
        if(empty($document)) {
            return $this->returnError($this->errorMsg['data.not.found']);
        }
        
        $document->delete();
        return $this->returnSuccess(__($this->successMsg['document.delete']),$document);
    }
    
    public function addService(Request $request) {
        
        $services = $request->services;
        $model = new TherapistSelectedService();
        $all_services = [];
        
        foreach ($services as $key => $service) {
            
            $data = [
                'therapist_id' => $request->therapist_id,
                'service_id' => $service
            ];
            $checks = $model->validator($data);
            if ($checks->fails()) {
                return $this->returnError($checks->errors()->first(), NULL, true);
            }
            
            $all_services[] = $model->updateOrCreate($data); 
        }
        
        return $this->returnSuccess(__($this->successMsg['service.add']),$all_services);
    }
    
    public function deleteService(Request $request) {
        
        $services = $request->services;
        $model = new TherapistSelectedService();
        $all_services = [];
        
        foreach ($services as $key => $service) {
            
            $all_services[] = $data = [
                'therapist_id' => $request->therapist_id,
                'service_id' => $service
            ];
            $find = $model->where($data)->first();
            if($find) {
                $find->delete(); 
            }
        }
        
        return $this->returnSuccess(__($this->successMsg['service.delete']),$all_services);
    }
}
