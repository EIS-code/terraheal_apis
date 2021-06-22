<?php

namespace App\Http\Controllers\Shops\Manager;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use App\TherapistWorkingSchedule;
use App\TherapistShift;
use App\Manager;
use Illuminate\Support\Facades\Hash;
use App\TherapistNews;
use App\Therapist;
use App\News;
use App\TherapistShop;
use App\Shop;
use App\Service;
use App\Libraries\CommonHelper;
use App\ManagerEmailOtp;

class ManagerController extends BaseController {

    public $errorMsg = [
        'loginEmail' => "Please provide email.",
        'loginPass' => "Please provide password.",
        'loginBoth' => "Shop email or password seems wrong.",
        'news.not.found' => "News not found.",
        'center.not.found' => "Shop not found.",
        'error.otp' => 'Please provide OTP properly.',
        'error.otp.wrong' => 'OTP seems wrong.',
        'error.otp.already.verified' => 'OTP already verified.',
        'error.manager.id' => 'Please provide valid manager id.',
        'error.email.already.verified' => 'This user email already verified with this.',
        'manager.not.found' => 'Manager not found.',
        'not.verified' => 'Your account is not verified yet.',
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
        'massages.found' => 'Massages found successfully!',
        'therapies.found' => 'Therapies found successfully!',
        'booking.details' => 'Booking details found successfully!',
        'success.email.otp.compare' => 'OTP matched successfully !',
        'success.sms.sent' => 'SMS sent successfully !',
        'success.email.sent' => 'Email sent successfully !',
        'edit.profile' => 'Profile updated successfully!',
    ];

    public function addAvailabilities(Request $request) {

        DB::beginTransaction();
        try {

            $data = $request->all();
            $scheduleModel = new TherapistWorkingSchedule();
            $shiftModel = new TherapistShift();
            $date = Carbon::createFromTimestampMs($data['date']);
            
            foreach ($data['shifts'] as $key => $shift) {
                foreach ($shift['therapists'] as $key => $therapist) {
                    
                    $scheduleData = [
                        'date' => $date->format('Y-m-d'),
                        'therapist_id' => $therapist['therapist_id'],
                    ];
                    $checks = $scheduleModel->validator($scheduleData);
                    if ($checks->fails()) {
                        return $this->returnError($checks->errors()->first(), NULL, true);
                    }
                    $schedule = $scheduleModel->updateOrCreate($scheduleData, $scheduleData);
                    $shiftData = [
                        'shift_id' => $shift['shift_id'],
                        'is_working' => TherapistShift::WORKING,
                        'is_absent' => TherapistShift::NOT_ABSENT,
                        'schedule_id' => $schedule->id
                    ];
                    $checks = $shiftModel->validator($shiftData);
                    if ($checks->fails()) {
                        return $this->returnError($checks->errors()->first(), NULL, true);
                    }
                    $shiftModel->updateOrCreate($shiftData, $shiftData);
                }
            }
            DB::commit();
            return $this->returnSuccess(__($this->successMsg['therapist.availability']));
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

            $manager = Manager::where(['email' => $email, 'is_email_verified' => Manager::IS_VERIFIED])->first();
            
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

        $data = TherapistNews::with('therapists:id', 'news')
                ->whereHas('news', function($q) use($request) {
            $q->where('manager_id', $request->manager_id);
        });
        $filter = !empty($request->filter) ? $request->filter : News::TODAY;
        $now = Carbon::now();
        
        if ($filter == News::TODAY) {
            $data->whereHas('news', function($q) use($now) {
                $q->whereDate('created_at', $now->format('Y-m-d'));
            });
        }
        if ($filter == News::YESTERDAY) {
            $data->whereHas('news', function($q) use($now) {
                $q->whereDate('created_at', $now->subDays(1));
            });
        }
        if ($filter == News::THIS_WEEK) {
            $weekStartDate = $now->startOfWeek()->format('Y-m-d');
            $weekEndDate = $now->endOfWeek()->format('Y-m-d');
            $data->whereHas('news', function($q) use($weekStartDate, $weekEndDate){
                $q->whereDate('created_at', '>=', $weekEndDate)->whereDate('created_at', '<=', $weekStartDate);
            });
        }
        if ($filter == News::CURRENT_MONTH) {
            $data->whereHas('news', function($q) use($now) {
                $q->whereMonth('created_at', $now->month);
            });
        }
        if ($filter == News::LAST_7_DAYS) {
            $agoDate = $now->subDays(7)->format('Y-m-d');
            $todayDate = $now->format('Y-m-d');
            $data->whereHas('news', function($q) use($todayDate, $agoDate){
                $q->whereDate('created_at', '>=', $agoDate)->whereDate('created_at', '<=', $todayDate);
            });
        }
        if ($filter == News::LAST_14_DAYS) {
            $agoDate = $now->subDays(14)->format('Y-m-d');
            $todayDate = $now->format('Y-m-d');
            $data->whereHas('news', function($q) use($todayDate, $agoDate){
                $q->whereDate('created_at', '>=', $agoDate)->whereDate('created_at', '<=', $todayDate);
            });
        }
        if ($filter == News::LAST_30_DAYS) {
            $agoDate = $now->subDays(30)->format('Y-m-d');
            $todayDate = $now->format('Y-m-d');
            $data->whereHas('news', function($q) use($todayDate, $agoDate){
                $q->whereDate('created_at', '>=', $agoDate)->whereDate('created_at', '<=', $todayDate);
            });
        }
        if ($filter == News::CUSTOM) {
            $date = $date = Carbon::createFromTimestampMs($request->date);
            $data->whereHas('news', function($q) use($date) {
                $q->whereDate('created_at', $date);
            });
        }
        $data = $data->get()->groupBy('news_id');
        $allTherapist = Therapist::where('shop_id', $request->shop_id)->get()->count();

        $allNews = [];
        if (!empty($data)) {
            foreach ($data as $key => $news) {

                $value = $news[0]['news'];
                $newsData = [
                    'id' => $value['id'],
                    'title' => $value['title'],
                    'sub_title' => $value['sub_title'],
                    'description' => $value['description'],
                    'manager_id' => $value['manager_id'],
                    'created_at' => strtotime($value['created_at']) * 1000,
                ];
                $cnt = 0;
                foreach ($news as $key => $value) {
                    $cnt++;
                }
                $newsData['read'] = $cnt;
                $newsData['unread'] = $allTherapist - $cnt;

                array_push($allNews, $newsData);
                unset($newsData);
            }
        }

        return $this->returnSuccess(__($this->successMsg['news']), $allNews);
    }

    public function newsDetails(Request $request) {
        
        $news = News::with('therapists')->where('id', $request->news_id)->first();
        if(empty($news)) {
            return $this->returnSuccess(__($this->errorMsg['news.not.found']));
        }
        $allTherapist = Therapist::where('shop_id', $request->shop_id)->get()->count();
        $read = $news->therapists->count();
        $unread = $allTherapist = 0 ? 0 : $allTherapist - $read ;
        
        $newsData = [
            'id' => $news['id'],
            'title' => $news['title'],
            'sub_title' => $news['sub_title'],
            'description' => $news['description'],
            'manager_id' => $news['manager_id'],
            'read' => $read,
            'unread' => $unread 
        ];
        
        return $this->returnSuccess(__($this->successMsg['news.details']), $newsData);
    }
    
    public function newTherapist(Request $request) {
        
        $model = new Therapist();
        $data = $request->all();
        $checks = $model->validator($data);
        if ($checks->fails()) {
            return $this->returnError($checks->errors()->first(), NULL, true);
        }
        $data['password'] = Hash::make($data['password']);
        $therapist = $model->create($data);
        
        return $this->returnSuccess(__($this->successMsg['new.therapist']), $therapist);
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
        
        $shopModel = new Shop();
        $therapists = $shopModel->getTherapists($request);
        
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
        
        if(isset($data['mobile_number']) && !empty($data['mobile_number'])) {
            if($manager->mobile_number != $data['mobile_number']) {
                $data['is_mobile_verified'] = Manager::IS_NOT_VERIFIED;
            }
        }
        if(isset($data['email']) && !empty($data['email'])) {
            if($manager->mobile_number != $data['email']) {
                $data['is_email_verified'] = Manager::IS_NOT_VERIFIED;
            }
        }
        
        $manager->update($data);
        $manager = $managerModel->with('country', 'city', 'province')->find($data['manager_id']);
        return $this->returnSuccess(__($this->successMsg['edit.profile']), $manager);
    }
}
