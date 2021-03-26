<?php

namespace App;

use App\User;
use App\Shop;
use App\SessionType;
use App\BookingInfo;
use App\UserGenderPreference;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class Booking extends BaseModel
{
    protected $fillable = [
        'booking_type',
        'special_notes',
        'total_persons',
        'bring_table_futon',
        'table_futon_quantity',
        'session_id',
        'copy_with_id',
        'user_id',
        'shop_id',
        'booking_date_time'
    ];

    const BOOKING_TYPE_IMC = '1';
    const BOOKING_TYPE_HHV = '2';
    const BOOKING_ONGOING = '0';
    const BOOKING_WAITING = '1';
    const BOOKING_FUTURE = '2';
    const BOOKING_COMPLETED = '3';
    const BOOKING_CANCELLED = '4';
    const BOOKING_PAST = '5';

    public static $defaultTableFutons = ['0', '1', '2'];
    public static $tableFutons = ['0', '1', '2'];

    public static $bookingTypes = [
        self::BOOKING_TYPE_IMC => 'In massage center',
        self::BOOKING_TYPE_HHV => 'Home / Hotel visit'
    ];

    public function validator(array $data, $isUpdate = false)
    {
        $validatorExtended = [];
        if ($isUpdate === false) {
            $totalBookingInfos = (!empty($data['booking_info']) && is_array($data['booking_info']) ? count($data['booking_info']) : 0);
            $validatorExtended = ['total_persons' => [new Rules\CheckValidBookingCount($totalBookingInfos)]];
        }

        $validator = Validator::make($data, array_merge([
            'booking_type'         => ['required', 'in:' . implode(",", array_keys(self::$bookingTypes))],
            'special_notes'        => ['max:255'],
            'copy_with_id'         => ['max:255'],
            'user_id'              => ['required', 'integer', 'exists:' . User::getTableName() . ',id'],
            'shop_id'              => ['required', 'integer', 'exists:' . Shop::getTableName() . ',id'],
            'session_id'           => ['required', 'integer', 'exists:' . SessionType::getTableName() . ',id'],
            'total_persons'        => ['required', 'integer'],
            'bring_table_futon'    => ['in:' . implode(",", self::$tableFutons)],
            'table_futon_quantity' => ['integer'],
            'booking_date_time'    => ['required'],
            'booking_info'         => ['required', 'array']
        ], $validatorExtended));

        return $validator;
    }

    public function getBookingDateTimeAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        return strtotime($value) * 1000;
    }

    public function getBookingTypeAttribute($value)
    {
        return (isset(self::$bookingTypes[$value])) ? self::$bookingTypes[$value] : $value;
    }

    public function bookingInfo()
    {
        return $this->hasMany('App\BookingInfo', 'booking_id', 'id');
    }

    public function bookingInfoWithFilters($type = 'today')
    {
        return $this->hasMany('App\BookingInfo', 'booking_id', 'id')->select(['id', 'booking_id', 'id as booking_info_id', 'massage_date', 'massage_time', 'user_people_id', 'therapist_id'])
                    ->where(function($query) use($type) {
                        $query->filterDatas();
                    })->with(['userPeople' => function($query) {
                        return $query->filterDatas();
                    }, 'therapist', 'bookingMassages' => function($query) {
                        $query->with(['massageTiming' => function($query1) {
                            return $query1->with('massage');
                        }]);
                    }]);
    }

    public function bookingInfoWithBookingMassages()
    {
        return $this->hasMany('App\BookingInfo', 'booking_id', 'id')->with('bookingMassages');
    }

    public function shop()
    {
        return $this->hasOne('App\Shop', 'id', 'shop_id');
    }

    public function user()
    {
        return $this->hasOne('App\User', 'id', 'user_id');
    }

    public function filterDatas(Builder $builder)
    {
        $request        = request();
        $bookingType    = $request->get('booking_type');
        $sessionType    = $request->get('session_type');

        if (isset($bookingType) && !empty(self::$bookingTypes[$bookingType])) {
            $builder->where('booking_type', (string)$bookingType);
        }

        if (!empty($sessionType)) {
            $builder->where('session_id', (string)$sessionType);
        }

        return $builder;
    }

    public function getGlobalQuery(Request $request)
    {
        $id             = $request->get('booking_info_id');
        $bookingDate    = $request->get('booking_date');
        $therapistId    = $request->get('id');
        $shopId         = $request->get('shop_id');
        $type           = $request->get('type');
        $bookingsFilter = $request->get('bookings_filter');
        $therapist      = $request->get('therapist_id');
        $roomId         = $request->get('room_id');
        $bookingId      = $request->get('booking_id');
        $date           = $request->get('date');
        $userId         = $request->get('user_id');
        
        $userPeopleModel                = new UserPeople();
        $bookingInfoModel               = new BookingInfo();
        $sessionTypeModel               = new SessionType();
        $massageModel                   = new Massage();
        $bookingMassageModel            = new BookingMassage();
        $massagePriceModel              = new MassagePrice();
        $massageTimingModel             = new MassageTiming();
        $massagePreferenceOptionModel   = new MassagePreferenceOption();
        $shopModel                      = new Shop();
        $userModel                      = new User();
        $roomModel                      = new Room();
        $therapistModel                 = new Therapist();
        $userGenderPreferenceModel      = new UserGenderPreference();
        $therapiesModel                 = new Therapy();
        $therapiesTimingModel           = new TherapiesTimings();
        $therapiesPriceModel            = new TherapiesPrices();

        $data = $this
                ->select(
                        DB::RAW(
                            'CONCAT(' . $userModel::getTableName() . '.name, " ", ' . $userModel::getTableName() . '.surname) as client_name, ' . 
                            $userPeopleModel::getTableName() . '.name as client_name, '. 
                            $bookingInfoModel::getTableName() . '.id as booking_info_id, '.
                            $bookingMassageModel::getTableName() . '.id as booking_massage_id, ' .
                            $bookingInfoModel::getTableName() . '.user_people_id, '.
                            $sessionTypeModel::getTableName() . '.type as session_type, ' . 
                            $massageModel::getTableName() . '.name as massage_name,' . 
                            $bookingInfoModel::getTableName() . '.massage_date as massage_date, UNIX_TIMESTAMP(' . 
                            $bookingInfoModel::getTableName() . '.massage_time) * 1000 as massage_start_time, UNIX_TIMESTAMP(' . 
                            'DATE_ADD(' . $bookingInfoModel::getTableName() . '.massage_time, INTERVAL ' . $massageTimingModel::getTableName() . '.time MINUTE)) * 1000 as massage_end_time, ' . 
                            'gender.name as gender_preference, ' . 
                            'pressure.name as pressure_preference, ' . 
                            $this::getTableName() . '.special_notes as notes, ' .
                            $bookingMassageModel::getTableName() . '.notes_of_injuries as injuries, ' . 
                            'focus_area.name as focus_area, ' . 
                            $this::getTableName() . '.table_futon_quantity, ' . 
                            $this::getTableName() . '.booking_type, ' . 
                            $shopModel::getTableName() . '.name as shop_name, ' . 
                            'CONCAT(' . $shopModel::getTableName() . '.address, " ", ' . $shopModel::getTableName() . '.address2) as shop_address, ' . 
                            'DATE_FORMAT(' . $bookingInfoModel::getTableName() . '.massage_date, "%a") as massage_day_name, ' . 
                            $userPeopleModel::getTableName() . '.age as client_age, ' . 
                            'CASE ' . $userPeopleModel::getTableName() . '.gender WHEN "m" THEN "' . $userPeopleModel->gender[$userPeopleModel::MALE] . '" WHEN "f" THEN "' . $userPeopleModel->gender[$userPeopleModel::FEMALE] . '" ELSE "" END as client_gender, ' . 
                            'CONCAT(' . $massageTimingModel::getTableName() . '.time, " ", "Mins") as massage_duration, ' .                            
                            $userModel::getTableName() . '.qr_code_path, ' . 
                            $this::getTableName() . '.user_id,'.
                            $bookingMassageModel::getTableName() . '.is_confirm, ' . 
                            $this::getTableName().'.id as booking_id,'.
                            $this::getTableName().'.shop_id as shop_id,'.
                            $this::getTableName().'.session_id as sessionId,'.
                            'CONCAT(' . $therapistModel::getTableName() . '.name, " ", ' . $therapistModel::getTableName() . '.surname) as therapistName, ' . 
                            $roomModel::getTableName().'.name as roomName,'.
                            $userGenderPreferenceModel::getTableName().'.name as genderPreference,'.
                            $massagePriceModel::getTableName().'.cost,'.
                            $therapistModel::getTableName().'.id as therapist_id,'.
                            $roomModel::getTableName().'.id as room_id,'.
                            $therapiesModel::getTableName().'.name as therapy_name,'.
                            $bookingMassageModel::getTableName().'.massage_timing_id,'.
                            $bookingMassageModel::getTableName().'.massage_prices_id,'.
                            $bookingMassageModel::getTableName().'.therapy_timing_id,'.
                            $bookingMassageModel::getTableName().'.therapy_prices_id'
                        )
                )
                ->join($bookingInfoModel::getTableName(), $this::getTableName() . '.id', '=', $bookingInfoModel::getTableName() . '.booking_id')
                ->join($userPeopleModel::getTableName(), $bookingInfoModel::getTableName() . '.user_people_id', '=', $userPeopleModel::getTableName() . '.id')
                ->join($shopModel::getTableName(), $this::getTableName() . '.shop_id', '=', $shopModel::getTableName() . '.id')
                ->join($userModel::getTableName(), $this::getTableName() . '.user_id', '=', $userModel::getTableName() . '.id')
                ->join($therapistModel::getTableName(),$bookingInfoModel::getTableName().'.therapist_id', '=', $therapistModel::getTableName().'.id')
                ->join($bookingMassageModel::getTableName(), $bookingInfoModel::getTableName() . '.id', '=', $bookingMassageModel::getTableName() . '.booking_info_id')
                ->leftJoin($roomModel::getTableName(),$bookingMassageModel::getTableName().'.room_id', '=', $roomModel::getTableName().'.id')
                ->leftJoin($userGenderPreferenceModel::getTableName(),$bookingMassageModel::getTableName().'.gender_preference', '=', $userGenderPreferenceModel::getTableName().'.id')
                ->join($sessionTypeModel::getTableName(), $this::getTableName() . '.session_id', '=', $sessionTypeModel::getTableName() . '.id')
                ->leftJoin($massagePriceModel::getTableName(), $bookingMassageModel::getTableName() . '.massage_prices_id', '=', $massagePriceModel::getTableName() . '.id')
                ->leftJoin($massageModel::getTableName(), $massagePriceModel::getTableName() . '.massage_id', '=', $massageModel::getTableName() . '.id')
                ->leftJoin($massageTimingModel::getTableName(), $massagePriceModel::getTableName() . '.massage_timing_id', '=', $massageTimingModel::getTableName() . '.id')
                ->leftJoin($massagePreferenceOptionModel::getTableName() . ' as gender', $bookingMassageModel::getTableName() . '.gender_preference', '=', 'gender.id')
                ->leftJoin($massagePreferenceOptionModel::getTableName() . ' as pressure', $bookingMassageModel::getTableName() . '.pressure_preference', '=', 'pressure.id')
                ->leftJoin($massagePreferenceOptionModel::getTableName() . ' as focus_area', $bookingMassageModel::getTableName() . '.focus_area_preference', '=', 'focus_area.id')            
                ->leftJoin($therapiesPriceModel::getTableName(), $bookingMassageModel::getTableName() . '.therapy_prices_id', '=', $therapiesPriceModel::getTableName() . '.id')
                ->leftJoin($therapiesModel::getTableName(), $therapiesPriceModel::getTableName() . '.therapy_id', '=', $therapiesModel::getTableName() . '.id')
                ->leftJoin($therapiesTimingModel::getTableName(), $therapiesPriceModel::getTableName() . '.therapy_timing_id', '=', $therapiesTimingModel::getTableName() . '.id')
                ->where($this::getTableName() . '.shop_id', (int)$shopId)
                ->whereNull($bookingMassageModel::getTableName().'.deleted_at');

        if (!empty($therapistId)) {
            $data->where($bookingInfoModel::getTableName() . '.therapist_id', (int) $therapistId);
        }
        if (!empty($id)) {
            $data->where($bookingInfoModel::getTableName() . '.id', (int) $id);
        }
        if (!empty($bookingDate)) {
            $bookingDate = Carbon::createFromTimestampMs($bookingDate)->format('Y-m-d');

            $data->whereDate($bookingInfoModel::getTableName() . '.massage_date', $bookingDate);
        }
        if (!empty($type)) {
            $data->where($this::getTableName() . '.booking_type', $type);
        }
        if ($therapist) {
            $data->where($bookingInfoModel::getTableName() . '.therapist_id', $therapist);
        }
        if ($roomId) {
            $data->where($bookingMassageModel::getTableName() . '.room_id', $roomId);
        }
        if ($bookingId) {
            $data->where($this::getTableName() . '.id', $bookingId);
        }
        if ($date) {
            $data->where($bookingInfoModel::getTableName() . '.massage_date', '=', $date);
        }
        if($userId) {
            $data->where($this::getTableName() . '.user_id', '=', $userId);
        }
        if (isset($bookingsFilter)) {
            if ($bookingsFilter == self::BOOKING_ONGOING) {
                $data->where($bookingMassageModel::getTableName() . '.is_confirm', BookingMassage::IS_CONFIRM);
            }
            if ($bookingsFilter == self::BOOKING_WAITING) {
                $data->where($bookingMassageModel::getTableName() . '.is_confirm', BookingMassage::IS_NOT_CONFIRM);
            }
            if ($bookingsFilter == self::BOOKING_FUTURE) {
                $data->where($bookingInfoModel::getTableName() . '.massage_date', '>=', Carbon::now()->format('Y-m-d'));
            }
            if ($bookingsFilter == self::BOOKING_COMPLETED) {
                $data->where($bookingInfoModel::getTableName() . '.is_done', BookingInfo::IS_DONE);
            }
            if ($bookingsFilter == self::BOOKING_CANCELLED) {
                $data->where($bookingInfoModel::getTableName() . '.is_cancelled', BookingInfo::IS_CANCELLED);
            }
            if ($bookingsFilter == self::BOOKING_PAST) {
                $data->where($bookingInfoModel::getTableName() . '.massage_date', '<=', Carbon::now()->format('Y-m-d'));
            }
        }

        $data = $data->get();

        if (!empty($data) && !$data->isEmpty()) {
            $data->map(function(&$record) use($userModel) {
                $record->qr_code_path = $userModel->getQrCodePathAttribute($record->qr_code_path);

                if (empty($record->qr_code_path)) {
                    $find = $userModel::find($record->user_id);

                    if (!empty($find)) {
                        $find->storeQRCode();

                        $record->qr_code_path = $find->qr_code_path;
                    }
                }
            });
        }

        return $data;
    }
}
