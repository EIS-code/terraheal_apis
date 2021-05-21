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

class ManagerController extends BaseController {

    public $errorMsg = [
        'loginEmail' => "Please provide email.",
        'loginPass' => "Please provide password.",
        'loginBoth' => "Shop email or password seems wrong.",
    ];
    
    public $successMsg = [
        'login' => "Manager found successfully !",
        'therapist.availability' => 'Therapist availability added successfully !',
    ];

    public function addAvailabilities(Request $request) {

        DB::beginTransaction();
        try {

            $data = $request->all();
            $scheduleModel = new TherapistWorkingSchedule();

            $date = Carbon::createFromTimestampMs($data['date']);
            $scheduleData = [
                'date' => $date->format('Y-m-d'),
                'is_working' => TherapistWorkingSchedule::WORKING,
                'is_absent' => TherapistWorkingSchedule::NOT_ABSENT,
                'therapist_id' => $data['therapist_id'],
            ];
            $checks = $scheduleModel->validator($scheduleData);
            if ($checks->fails()) {
                return $this->returnError($checks->errors()->first(), NULL, true);
            }
            $schedule = $scheduleModel->updateOrCreate(['therapist_id' => $data['therapist_id']], $scheduleData);

            $shiftModel = new TherapistShift();
            foreach ($data['shifts'] as $key => $value) {

                $shiftData = [
                    'shift_id' => $value,
                    'schedule_id' => $schedule->id
                ];
                $checks = $shiftModel->validator($shiftData);
                if ($checks->fails()) {
                    return $this->returnError($checks->errors()->first(), NULL, true);
                }
                $shiftModel->updateOrCreate($shiftData, $shiftData);
            }
            $schedule = $scheduleModel->with('therapistShifts')->where('id', $schedule->id)->first();
            DB::commit();
            return $this->returnSuccess(__($this->successMsg['therapist.availability']),$schedule);
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
}
