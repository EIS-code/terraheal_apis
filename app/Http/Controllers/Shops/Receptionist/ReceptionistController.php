<?php

namespace App\Http\Controllers\Shops\Receptionist;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use App\Receptionist;
use App\ReceptionistDocuments;
use App\ReceptionistTimeTables;
use Carbon\Carbon;
use App\Libraries\CommonHelper;
use App\ReceptionistBreakTime;
use App\Shop;

class ReceptionistController extends BaseController {

    public $successMsg = [
        
        'receptionist.create' => 'Receptionist created successfully',
        'receptionist.document' => 'Receptionist document uploaded successfully',
        'receptionist.data' => 'Receptionist found successfully',
        'receptionist.statistics' => 'Receptionist statistics data found successfully',
        'receptionist.break' => 'Break added successfully',
        'not.found' => 'Receptionist data not found',
        'shop' => 'Please provide shop id.',
    ];
    
    public function createReceptionist(Request $request) {
        
        $model = new Receptionist();
        $data = $request->all();
        
        $checks = $model->validator($data);
        if ($checks->fails()) {
            return $this->returnError($checks->errors()->first(), NULL, true);
        }
        /* For profile Image */
        if ($request->hasFile('photo')) {
            $checkImage = $model->validatePhoto($data);
            if ($checkImage->fails()) {
                unset($data['photo']);

                return $this->returnError($checkImage->errors()->first(), NULL, true);
            }
            $fileName = time().'.' . $data['photo']->getClientOriginalExtension();
            $storeFile = $data['photo']->storeAs($model->profilePhotoPath, $fileName, $model->fileSystem);

            if ($storeFile) {
                $data['photo'] = $fileName;
            }
        }
        $date = Carbon::createFromTimestampMs($data['dob']);
        $data['dob'] = $date->format('Y-m-d');
        $receptionist = $model->create($data);
        
        return $this->returnSuccess(__($this->successMsg['receptionist.create']),$receptionist);
    }

    public function addDocument(Request $request) {
        
        $model = new ReceptionistDocuments();
        $receptionist = Receptionist::find($request->receptionist_id);
        
        if(empty($receptionist)) {
            return $this->returnError(__($this->successMsg['not.found']));
        }
        
        $data = $request->all();
        
        $checks = $model->validator($data);
        if ($checks->fails()) {
            return $this->returnError($checks->errors()->first(), NULL, true);
        }
        
        if ($request->hasFile('file_name')) {
            $fileName = time().'.' . $data['file_name']->getClientOriginalExtension();
            $storeFile = $data['file_name']->storeAs($model->directory, $fileName, $model->fileSystem);

            if ($storeFile) {
                $data['file_name'] = $fileName;
            }
        }        
        $document = $model->create($data);
        
        return $this->returnSuccess(__($this->successMsg['receptionist.document']),$document);
    }
    
    public function getReceptionist(Request $request) {
        
        if(!empty($request->receptionist_id)){
            if(!empty($request->shop_id)){
                return $this->returnError(__($this->successMsg['shop']));
            }
            $receptionist = Shop::with('receptionist')->where('id',$request->shop_id)->first();
            $receptionistId = $receptionist->receptionist->id;
        } else {
            $receptionistId = $request->receptionist_id;
        }
        $receptionist = Receptionist::with('country','city','shop:id,name','documents')->where('id',$receptionistId)->get();
        
        if($receptionist->count() > 0) {
            return $this->returnSuccess(__($this->successMsg['receptionist.data']),$receptionist);
        } else {
            return $this->returnError(__($this->successMsg['not.found']));
        }
    }
    
    public function getStatistics(Request $request) {
        
        $date  = Carbon::createFromTimestampMs($request->date);
        $date = !empty($request->date) ? $date : Carbon::now();
        $receptionist = ReceptionistTimeTables::with('breaks')->where('receptionist_id',$request->receptionist_id)
                ->whereMonth('login_date',$date->month)->get();
        $presentDays = ReceptionistTimeTables::with('breaks')->where(['receptionist_id' => $request->receptionist_id, 'is_working' => ReceptionistTimeTables::IS_WORKING])->count();
        $absentDays = ReceptionistTimeTables::with('breaks')->where(['receptionist_id' => $request->receptionist_id, 'is_working' => ReceptionistTimeTables::IS_NOT_WORKING])->count();
        
        $totalHours = [];
        $breakHours = [];        
        
        foreach ($receptionist as $key => $value) {
            
            $start_time = Carbon::createFromTimestampMs($value['login_time']);
            $end_time = Carbon::createFromTimestampMs($value['logout_time']);
            $total = new Carbon($start_time->diff($end_time)->format("%h:%i"));
            
            $receptionist_break = [];
            foreach ($value->breaks as $key => $break) {
                $break_start_time = Carbon::createFromTimestampMs($break['start_time']);
                $break_end_time = Carbon::createFromTimestampMs($break['end_time']);
                $breakHours[] = $receptionist_break[] = $break_start_time->diff($break_end_time)->format("%h:%i");
                
            }
            $value['break_time'] = CommonHelper::calculateHours($receptionist_break);
            $value['break_time'] = strtotime($value['break_time']) * 1000;
            $value['total'] = $totalHours[] = $total->diff(new Carbon($value['break_time']))->format("%h:%i");
            $value['total'] = strtotime($value['total']) * 1000;
            unset($receptionist_break); 
        }

        //calculate total hours
        $hours = CommonHelper::calculateHours($totalHours);
        
        //calculate total break hours
        $break = CommonHelper::calculateHours($breakHours);
        
        return $this->returnSuccess(__($this->successMsg['receptionist.data']),['receptionistData' => $receptionist, 
            'totalWorkingDays' => $receptionist->count(), 'presentDays' => $presentDays, 'absentDays' => $absentDays,
            'totalHours' => explode(':', $hours)[0], 'totalBreakHours' => explode(':', $break)[0],'totalWorkingHours' => explode(':', $hours)[0]-explode(':', $break)[0]]);
    }
    
    public function takeBreak(Request $request) {
        
        $model = new ReceptionistBreakTime();
        $date = Carbon::createFromFormat('Y-m-d',$request->date);
        $date = $date->format('Y-m-d');
        $receptionist_schedule = ReceptionistTimeTables::where(['receptionist_id' => $request->receptionist_id, 'login_date' => $date])->first();
        
        
        if(!empty($receptionist_schedule)) {
            $data = $request->all();
            $data['receptionist_schedule_id'] = $receptionist_schedule->id;
            $checks = $model->validator($data);

            if ($checks->fails()) {
                return $this->returnError($checks->errors()->first(), NULL, true);
            }
            $break = $model->create($data);
            return $this->returnSuccess(__($this->successMsg['receptionist.break']),$break);
        } else {
            return $this->returnError(__($this->successMsg['not.found']));
        }
    }
}
