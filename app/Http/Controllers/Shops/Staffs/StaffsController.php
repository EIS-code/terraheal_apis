<?php

namespace App\Http\Controllers\Shops\Staffs;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Staff;
use App\StaffWorkingSchedule;
use App\StaffDocument;

class StaffsController extends BaseController {

    public $errorMsg = [
        'staff.not.found' => 'Staff not found.',
        'receptionist.exist' => 'Receptionist is already added.',
    ];
    
    public $successMsg = [
        'staff.create' => 'Staff added successfully !',
        'staff.update' => 'Staff data updated successfully !',
        'staff.list' => 'Staff data found successfully !',
        'staff.document' => 'Staff document uploaded successfully !'
    ];
    
    public function createStaff(Request $request) {
        
        DB::beginTransaction();
        try {
            $model = new Staff();
            $data = $request->all();
            if(!empty($data['dob'])) {
                $date = Carbon::createFromTimestampMs($data['dob']);
                $data['dob'] = $date->format('Y-m-d');
            }

            $checks = $model->validator($data);
            if ($checks->fails()) {
                return $this->returnError($checks->errors()->first(), NULL, true);
            }
            $data['password'] = $data['password'] ? Hash::make($data['password']) : NULL;
            
            if($data['role'] == Staff::RECEPTIONIST) {
                $is_exist = $model->where(['role' => $data['role'], 'shop_id' => $data['shop_id']])->first();
                if(!empty($is_exist)) {
                    return $this->returnError(__($this->errorMsg['receptionist.exist']));
                }
            }
            $data['city_id'] = $data['city_id'] ? $data['city_id'] : NULL;
            $data['country_id'] = $data['country_id'] ? $data['country_id'] : NULL;
            $data['pay_scale'] = $data['pay_scale'] ? $data['pay_scale'] : NULL;
            $data['amount'] = $data['amount'] ? $data['amount'] : NULL;
            $staff = Staff::create($data);

            if(!empty($data['schedule'])) {
                foreach ($data['schedule'] as $key => $value) {

                    $startTime  = Carbon::createFromTimestampMs($value['start_time']);
                    $endTime  = Carbon::createFromTimestampMs($value['end_time']);
                    $scheduleData = [
                        "day_name" => $value['day_name'],
                        "start_time" => $startTime->format("H:i:s"),
                        "end_time" => $endTime->format("H:i:s"),
                        "staff_id" => $staff->id
                    ];

                    $scheduleModel = new StaffWorkingSchedule();
                    $checks = $scheduleModel->validator($scheduleData);
                    if ($checks->fails()) {
                        return $this->returnError($checks->errors()->first(), NULL, true);
                    }
                    $scheduleModel->create($scheduleData);
                }
            }
            
            $staff = Staff::with('schedule')->where('id',$staff->id)->get();
            DB::commit();
            return $this->returnSuccess(__($this->successMsg['staff.create']),$staff);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollback();
            throw $e;
        }
    }
    
    public function updateStaff(Request $request) {
        
        DB::beginTransaction();
        try {
            
            $model = new Staff();
            $staff = Staff::where('id', $request->staff_id)->first();
            if(empty($staff)) {
                return $this->returnError(__($this->errorMsg['staff.not.found']));
            }
            
            $data = $request->all();
            if(!empty($data['dob'])) {
                $data['dob'] = $data['dob'] ? Carbon::createFromTimestampMs($data['dob']) : NULL;
            }
            
            $checks = $model->validator($data, $request->staff_id, true);
            if ($checks->fails()) {
                return $this->returnError($checks->errors()->first(), NULL, true);
            }
            
            if(!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }
            
            if($data['role'] == Staff::RECEPTIONIST) {
                $is_exist = $model->where(['role' => $data['role'], 'shop_id' => $data['shop_id']])->first();
                if($is_exist->id != $staff->id) {
                    return $this->returnError(__($this->errorMsg['receptionist.exist']));
                }
            }
            
            $staff->update($data);
            
            if(!empty($data['schedule'])) {
                foreach ($data['schedule'] as $key => $value) {

                    $startTime  = Carbon::createFromTimestampMs($value['start_time']);
                    $endTime  = Carbon::createFromTimestampMs($value['end_time']);
                    $scheduleData = [
                        "day_name" => $value['day_name'],
                        "start_time" => $startTime->format("H:i:s"),
                        "end_time" => $endTime->format("H:i:s"),
                        "staff_id" => $request->staff_id
                    ];

                    $scheduleModel = new StaffWorkingSchedule();
                    $check = $scheduleModel->validator($scheduleData);
                    if ($check->fails()) {
                        return $this->returnError($check->errors()->first(), NULL, true);
                    }
                    $scheduleModel->updateOrCreate($scheduleData, $scheduleData);
                }
            }
            
            $staff = Staff::with('schedule')->where('id',$request->staff_id)->get();
            DB::commit();
            return $this->returnSuccess(__($this->successMsg['staff.update']),$staff);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function staffList(Request $request) {
        
        $staffs = Staff::with('schedule','country','city')->where('shop_id', $request->shop_id);
        $search_val = $request->search_val;
        
        if(!empty($search_val)) {
            $staffs->where(function($query) use ($search_val) {
                    $query->where('full_name', 'like', $search_val.'%')
                            ->orWhere('email', $search_val)
                            ->orWhere('dob', $search_val)
                            ->orWhere('mobile_number', $search_val)
                            ->orWhere('nif', $search_val);
                });
        }
        return $this->returnSuccess(__($this->successMsg['staff.list']),$staffs->get());
    }
    
    public function uploadDocument(Request $request) {
        
        $model = new StaffDocument();
        
        $data = $request->all();
        if(!empty($data['expired_date'])){
            $data['expired_date'] = Carbon::createFromTimestampMs($data['expired_date']);
            $data['is_expired'] = '1';
        }
        
        $checks = $model->validator($data);
        if ($checks->fails()) {
            return $this->returnError($checks->errors()->first(), NULL, true);
        }
        
        if ($request->hasFile('document')) {
            $fileName = time().'.' . $data['document']->getClientOriginalExtension();
            $storeFile = $data['document']->storeAs($model->directory, $fileName, $model->fileSystem);

            if ($storeFile) {
                $data['document'] = $fileName;
            }
        }        
        $document = $model->create($data);
        
        return $this->returnSuccess(__($this->successMsg['staff.document']),$document);
    }
}
