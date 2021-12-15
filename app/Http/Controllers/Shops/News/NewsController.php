<?php

namespace App\Http\Controllers\Shops\News;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use App\News;
use App\Manager;
use App\Therapist;
use App\Jobs\TherapistNotification;
use App\Notification;

class NewsController extends BaseController {

    public $errorMsg = [
        'news.not.found' => 'News data not found!',
    ];

    public $successMsg = [
        'news.add' => 'News added successfully!',
        'news.edit' => 'News updated successfully!',
        'news.delete' => 'News deleted successfully!'
    ];
    
    public function addNews(Request $request) {
        
        $data = $request->all();
        $newsModel = new News();
        
        $checks = $newsModel->validator($data);
        if ($checks->fails()) {
            return $this->returnError($checks->errors()->first(), NULL, true);
        }
        $manager = Manager::find($request->manager_id);
        $therapists = Therapist::where('shop_id', $manager->shop_id)->get();
        
        $news = $newsModel->create($data);
        foreach ($therapists as $key => $therapist) {
            dispatch(new TherapistNotification($therapist->id, "News", "News added successfully", Notification::SEND_FROM_MANAGER_APP, Notification::SEND_TO_THERAPIST_APP, $therapist->id));
        }
        return $this->returnSuccess(__($this->successMsg['news.add']), $news);
    }
    
    public function updateNews(Request $request) {
        
        $data = $request->all();
        $newsModel = new News();
        
        $checks = $newsModel->validator($data);
        if ($checks->fails()) {
            return $this->returnError($checks->errors()->first(), NULL, true);
        }
        
        $news = $newsModel->find($data['news_id']);
        if(empty($news)) {
            return $this->returnError(__($this->errorMsg['news.not.found']));
        }
        $news->update($data);
        return $this->returnSuccess(__($this->successMsg['news.edit']), $news);
    }
    
    public function deleteNews(Request $request) {
        
        $data = $request->all();
        $newsModel = new News();
        
        $news = $newsModel->find($data['news_id']);
        if(empty($news)) {
            return $this->returnError(__($this->errorMsg['news.not.found']));
        }
        $news->delete();
        return $this->returnSuccess(__($this->successMsg['news.delete']), $news);
    }
    
}
