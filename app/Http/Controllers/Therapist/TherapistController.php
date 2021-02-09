<?php

namespace App\Http\Controllers\Therapist;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use App\Therapist;
use App\UserPeople;
use App\SessionType;
use App\Booking;
use App\BookingInfo;
use App\BookingMassage;
use App\Massage;
use App\MassagePrice;
use App\MassageTiming;
use App\MassagePreferenceOption;
use DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

class TherapistController extends BaseController
{

    public $errorMsg = [
        'loginEmail' => "Please provide email properly.",
        'loginPass'  => "Please provide password properly.",
        'loginBoth'  => "Therapist email or password seems wrong."
    ];

    public $successMsg = [
        'login' => "Therapist found successfully !",
        'booking.details.found.successfully' => "Bookings found successfully !",
        'booking.today.found.successfully' => "Today bookings found successfully !",
        'booking.future.found.successfully' => "Future bookings found successfully !",
        'booking.past.found.successfully' => "Past bookings found successfully !",
    ];

    public function signIn(int $isFreelancer = Therapist::IS_NOT_FREELANCER, Request $request)
    {
        $model    = new Therapist();
        $data     = $request->all();
        $email    = (!empty($data['email'])) ? $data['email'] : NULL;
        $password = (!empty($data['password'])) ? $data['password'] : NULL;

        if (empty($email)) {
            return $this->returnError($this->errorMsg['loginEmail']);
        } elseif (empty($password)) {
            return $this->returnError($this->errorMsg['loginPass']);
        }

        if (!empty($email) && !empty($password)) {
            $getTherapist = $model->where(['email' => $email, 'is_freelancer' => (string)$isFreelancer])->first();

            if (!empty($getTherapist) && Hash::check($password, $getTherapist->password)) {
                $getTherapist = $getTherapist->first();

                return $this->returnSuccess(__($this->successMsg['login']), $getTherapist);
            } else {
                return $this->returnError($this->errorMsg['loginBoth']);
            }
        }

        return $this->returnNull();
    }

    public function getGlobalResponse(Request $request)
    {
        $id   = $request->get('booking_info_id');

        // $data = Therapist::with('selectedMassages', 'selectedTherapies')->where('id', $id)->first();

        $bookingModel                   = new Booking();
        $userPeopleModel                = new UserPeople();
        $bookingInfoModel               = new BookingInfo();
        $sessionTypeModel               = new SessionType();
        $massageModel                   = new Massage();
        $bookingMassageModel            = new BookingMassage();
        $massagePriceModel              = new MassagePrice();
        $massageTimingModel             = new MassageTiming();
        $massagePreferenceOptionModel   = new MassagePreferenceOption();

        $data = $bookingModel
                ->select(
                        DB::RAW(
                            $userPeopleModel::getTableName() . '.name as client_name, '. 
                            $bookingInfoModel::getTableName() . '.id as booking_info_id, '. 
                            $sessionTypeModel::getTableName() . '.type as session_type, ' . 
                            $massageModel::getTableName() . '.name as service_name, UNIX_TIMESTAMP(' . 
                            $bookingInfoModel::getTableName() . '.massage_date) * 1000 as massage_date, UNIX_TIMESTAMP(' . 
                            $bookingInfoModel::getTableName() . '.massage_time) * 1000 as massage_start_time, UNIX_TIMESTAMP(' . 
                            'DATE_ADD(' . $bookingInfoModel::getTableName() . '.massage_time, INTERVAL ' . $massageTimingModel::getTableName() . '.time MINUTE)) * 1000 as massage_end_time, ' . 
                            'gender.name as gender_preference, ' . 
                            'pressure.name as pressure_preference, ' . 
                            $bookingModel::getTableName() . '.special_notes as notes, ' . 
                            $bookingMassageModel::getTableName() . '.notes_of_injuries as injuries, ' . 
                            'focus_area.name as focus_area, ' . 
                            $bookingModel::getTableName() . '.table_futon_quantity, ' . 
                            $bookingModel::getTableName() . '.booking_type'
                        )
                )
                ->join($bookingInfoModel::getTableName(), $bookingModel::getTableName() . '.id', '=', $bookingInfoModel::getTableName() . '.booking_id')
                ->join($userPeopleModel::getTableName(), $bookingInfoModel::getTableName() . '.user_people_id', '=', $userPeopleModel::getTableName() . '.id')
                ->leftJoin($sessionTypeModel::getTableName(), $bookingModel::getTableName() . '.session_id', '=', $sessionTypeModel::getTableName() . '.id')
                ->leftJoin($bookingMassageModel::getTableName(), $bookingInfoModel::getTableName() . '.id', '=', $bookingMassageModel::getTableName() . '.booking_info_id')
                ->leftJoin($massagePriceModel::getTableName(), $bookingMassageModel::getTableName() . '.massage_prices_id', '=', $massagePriceModel::getTableName() . '.id')
                ->leftJoin($massageModel::getTableName(), $massagePriceModel::getTableName() . '.massage_id', '=', $massageModel::getTableName() . '.id')
                ->leftJoin($massageTimingModel::getTableName(), $massagePriceModel::getTableName() . '.massage_timing_id', '=', $massageTimingModel::getTableName() . '.id')
                ->leftJoin($massagePreferenceOptionModel::getTableName() . ' as gender', $bookingMassageModel::getTableName() . '.gender_preference', '=', 'gender.id')
                ->leftJoin($massagePreferenceOptionModel::getTableName() . ' as pressure', $bookingMassageModel::getTableName() . '.pressure_preference', '=', 'pressure.id')
                ->leftJoin($massagePreferenceOptionModel::getTableName() . ' as focus_area', $bookingMassageModel::getTableName() . '.focus_area_preference', '=', 'focus_area.id')
                ->where($bookingInfoModel::getTableName() . '.id', (int)$id)
                ->get();

        if (!empty($data)) {
            return $this->returnSuccess(__($this->successMsg['booking.details.found.successfully']), $data);
        }

        return $this->returnNull();
    }

    public function filter(Collection &$return)
    {
        if (!empty($return) && !$return->isEmpty()) {
            $increments = 0;

            $return->map(function(&$data, $index) use(&$return, &$increments) {
                if (empty($data->bookingInfoWithFilters) || $data->bookingInfoWithFilters->isEmpty()) {
                    unset($return[$index]);
                } elseif (request()->get('client_name', false) && empty($data->bookingInfoWithFilters[0]->userPeople)) {
                    unset($return[$index]);
                } elseif (empty($data->bookingInfoWithFilters[0]->therapist) || $data->bookingInfoWithFilters[0]->therapist->shop_id !== $data->shop_id) {
                    unset($return[$index]);
                } else {
                    if (!empty($data->bookingInfoWithFilters[0]->userPeople)) {
                        unset($data->bookingInfoWithFilters[0]->userPeople);
                    }

                    if (!empty($data->bookingInfoWithFilters[0]->therapist)) {
                        unset($data->bookingInfoWithFilters[0]->therapist);
                    }

                    $data->booking_infos = $data->bookingInfoWithFilters;

                    unset($data->bookingInfoWithFilters);
                }
            });
        }

        return $return;
    }

    public function getTodayBooking(Request $request)
    {
        $bookingModel = new Booking();

        $data = $bookingModel->with('bookingInfoWithFilters')->filterDatas()->get();

        $this->filter($data);

        return $this->returns('booking.today.found.successfully', $data);
    }

    public function getFutureBooking(Request $request)
    {
        $bookingModel = new Booking();

        $data = $bookingModel->with('bookingInfoWithFilters')->filterDatas()->get();

        $this->filter($data);

        return $this->returns('booking.future.found.successfully', $data);
    }

    public function getPastBooking(Request $request)
    {
        $bookingModel = new Booking();

        $data = $bookingModel->with('bookingInfoWithFilters')->filterDatas()->get();

        $this->filter($data);

        return $this->returns('booking.past.found.successfully', $data);
    }

    public function returns($message = NULL, $with = NULL)
    {
        $message = __($this->successMsg[$message]);

        if (!empty($with) && !$with->isEmpty()) {
            return $this->returnSuccess($message, array_values($with->toArray()));
        }

        return $this->returnNull();
    }
}
