<?php

namespace App\Http\Controllers\Shops\Manager;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use App\TherapistWorkingSchedule;
use App\TherapistShift;

class ManagerController extends BaseController {

    public $successMsg = [
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

}
