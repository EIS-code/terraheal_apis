<?php

namespace App\Http\Controllers\Shops\Manager;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use App\TherapistWorkingSchedule;
use App\Manager;
use Illuminate\Support\Facades\Hash;
use App\TherapistNews;
use App\Therapist;
use App\News;
use App\TherapistShop;

class ManagerController extends BaseController {

    public $errorMsg = [
        'loginEmail' => "Please provide email.",
        'loginPass' => "Please provide password.",
        'loginBoth' => "Shop email or password seems wrong.",
        'news.not.found' => "News not found.",
    ];
    
    public $successMsg = [
        'login' => "Manager found successfully !",
        'therapist.availability' => 'Therapist availability added successfully !',
        'news' => 'News data found successfully !',
        'news.details' => 'News details found successfully !',
        'new.therapist' => 'New therapist created successfully !',
        'existing.therapist' => 'Existing therapist added successfully to this shop!',
    ];

    public function addAvailabilities(Request $request) {

        DB::beginTransaction();
        try {

            $data = $request->all();
            $scheduleModel = new TherapistWorkingSchedule();
            $date = Carbon::createFromTimestampMs($data['date']);
            
            foreach ($data['shifts'] as $key => $shift) {
                foreach ($shift['therapists'] as $key => $therapist) {
                    $scheduleData = [
                        'date' => $date->format('Y-m-d'),
                        'therapist_id' => $therapist['therapist_id'],
                        'shift_id' => $shift['shift_id'],
                        'is_working' => TherapistWorkingSchedule::WORKING,
                        'shop_id' => $data['shop_id'],
                    ];
                    $checks = $scheduleModel->validator($scheduleData);
                    if ($checks->fails()) {
                        return $this->returnError($checks->errors()->first(), NULL, true);
                    }
                    $schedules[] = $scheduleModel->updateOrCreate($scheduleData, $scheduleData);
                }
            }
            DB::commit();
            return $this->returnSuccess(__($this->successMsg['therapist.availability']), $schedules);
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

        $data = TherapistNews::with('therapists:id', 'news')
                ->whereHas('news', function($q) use($request) {
            $q->where('manager_id', $request->manager_id);
        });
        $filter = !empty($request->filter) ? $request->filter : News::TODAY;
        $todayDate = Carbon::now()->format('Y-m-d');
        
        if ($filter == News::TODAY) {
            $data->whereHas('news', function($q) use($todayDate) {
                $q->whereDate('created_at', $todayDate);
            });
        }
        if ($filter == News::CURRENT_MONTH) {
            $data->whereHas('news', function($q) use($todayDate) {
                $q->whereMonth('created_at', $todayDate->month);
            });
        }
        if ($filter == News::LAST_7_DAYS) {
            $agoDate = Carbon::now()->subDays(7)->format('Y-m-d');
            $data->whereHas('news', function($q) use($todayDate, $agoDate){
                $q->whereDate('created_at', '>=', $agoDate)->whereDate('created_at', '<=', $todayDate);
            });
        }
        if ($filter == News::LAST_14_DAYS) {
            $agoDate = Carbon::now()->subDays(14)->format('Y-m-d');
            $data->whereHas('news', function($q) use($todayDate, $agoDate){
                $q->whereDate('created_at', '>=', $agoDate)->whereDate('created_at', '<=', $todayDate);
            });
        }
        if ($filter == News::LAST_30_DAYS) {
            $agoDate = Carbon::now()->subDays(30)->format('Y-m-d');
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
}
