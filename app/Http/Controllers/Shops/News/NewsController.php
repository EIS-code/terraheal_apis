<?php

namespace App\Http\Controllers\Shops\News;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use App\News;

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
        
        $news = $newsModel->create($data);
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
