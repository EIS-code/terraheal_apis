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

class ManagerController extends BaseController {

    public $errorMsg = [
        'loginEmail' => "Please provide email.",
        'loginPass' => "Please provide password.",
        'loginBoth' => "Shop email or password seems wrong.",
    ];
    
    public $successMsg = [
        'login' => "Manager found successfully !",
        'therapist.availability' => 'Therapist availability added successfully !',
        'news' => 'News data found successfully !',
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

            $manager = Manager::where(['email' => $email])->first();
            
            if (!empty($manager) && Hash::check($password, $manager->password)) {
                return $this->returnSuccess(__($this->successMsg['login']), $manager);
            } else {
                return $this->returnError($this->errorMsg['loginBoth']);
            }
        }
        return $this->returnNull();
    }
    
    public function getNews(Request $request) {
        
        $data = TherapistNews::with('therapists:id', 'news')->get()->groupBy('news_id');
        $allTherapist = Therapist::where('shop_id', $request->shop_id)->get()->count();
        
        $allNews = [];
        if(!empty($data)) {
            foreach ($data as $key => $news) {

                $value = $news[0]['news'];
                $newsData = [
                    'id' => $value['id'],
                    'title' => $value['title'],
                    'sub_title' => $value['sub_title'],
                    'description' => $value['description'],
                    'manager_id' => $value['manager_id'],
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
}
