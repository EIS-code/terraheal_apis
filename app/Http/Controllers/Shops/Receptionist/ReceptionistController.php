<?php

namespace App\Http\Controllers\Shops\Receptionist;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use App\Receptionist;
use App\ReceptionistDocuments;
use App\ReceptionistTimeTables;
use Carbon\Carbon;

class ReceptionistController extends BaseController {

    public $successMsg = [
        
        'receptionist.create' => 'Receptionist created successfully',
        'receptionist.document' => 'Receptionist document uploaded successfully',
        'receptionist.data' => 'Receptionist found successfully',
        'receptionist.statistics' => 'Receptionist statistics data found successfully',
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
        $receptionist = $model->create($data);
        
        return $this->returnSuccess(__($this->successMsg['receptionist.create']),$receptionist);
    }

    public function addDocument(Request $request) {
        
        $model = new ReceptionistDocuments();
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
        
        $receptionist = Receptionist::with('country','city','shop:id,name','documents')->where('id',$request->receptionist_id)->get();
        
        return $this->returnSuccess(__($this->successMsg['receptionist.data']),$receptionist);
    }
    
    public function getStatistics(Request $request) {
        
        $date = Carbon::createFromFormat('Y-m-d', $request->date);
        $receptionist = ReceptionistTimeTables::where('receptionist_id',$request->receptionist_id)
                ->whereMonth('login_date',$date->month)->get();
        
        $totalHours = [];
        $breakHours = [];
        
        foreach ($receptionist as $key => $value) {
            
            $start_time = new Carbon($value['login_time']);
            $end_time = new Carbon($value['logout_time']);
            $afterBreak = $end_time->diff(new Carbon($value['break_time']));
            $newEndTime = $afterBreak->format("%h:%i:%s");
            $value['total'] = $totalHours[] = $start_time->diff($newEndTime)->format("%h:%i");        
            $breakHours[] = $value['break_time'];
        }
        
        $hours = '';
        foreach ($totalHours as $key => $value) {
            if ($key == 0) {
                $hours = $value;
            } else {
                $secs = strtotime($value) - strtotime("00:00:00");
                $result = date("H:i:s", strtotime($hours) + $secs);
                $hours = $result;
            }
        }

        $break = '';
        foreach ($breakHours as $key => $value) {
            if ($key == 0) {
                $break = $value;
            } else {
                $secs = strtotime($value) - strtotime("00:00:00");
                $result = date("H:i:s", strtotime($break) + $secs);
                $break = $result;
            }
        }

        $totalWorkingDays = cal_days_in_month(CAL_GREGORIAN, $date->month, $date->year);
        $presentDays = $receptionist->count();
        $hours = date("H",strtotime($hours));
        $break = date("H",strtotime($break));
        

        return $this->returnSuccess(__($this->successMsg['receptionist.data']),['receptionistData' => $receptionist, 
            'totalWorkingDays' => $totalWorkingDays, 'presentDays' => $presentDays, 'absentDays' => $totalWorkingDays - $presentDays,
            'totalHours' => $hours, 'totalBreakHours' => $break,'totalWorkingHours' => $hours-$break]);
    }
}
