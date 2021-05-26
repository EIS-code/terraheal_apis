<?php

namespace App\Http\Controllers\Shops\Staffs;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Staff;
use App\StaffWorkingSchedule;

class StaffsController extends BaseController {

    public $successMsg = [
        
        'staff.create' => 'Staff added successfully',
        'staff.list' => 'Staff data found successfully'
    ];
    
    public function createStaff(Request $request) {
        
        DB::beginTransaction();
        try {
            $model = new Staff();
            $data = $request->all();

            $checks = $model->validator($data);
            if ($checks->fails()) {
                return $this->returnError($checks->errors()->first(), NULL, true);
            }
            
            $data['password'] = Hash::make($data['password']);
            $staff = Staff::create($data);
            
            if(!empty($data['schedule'])) {
                foreach ($data['schedule'] as $key => $value) {

                    $startTime  = Carbon::createFromTimestampMs($value['startTime']);
                    $endTime  = Carbon::createFromTimestampMs($value['endTime']);
                    $scheduleData = [
                        "day_name" => $value['day_name'],
                        "startTime" => $startTime,
                        "endTime" => $endTime,
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

    public function staffList(Request $request) {
        
        $staffs = Staff::with('schedule','country','city')->where('shop_id', $request->shop_id)->get();
        return $this->returnSuccess(__($this->successMsg['staff.list']),$staffs);
    }
}
