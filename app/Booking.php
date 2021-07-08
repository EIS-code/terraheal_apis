<?php

namespace App;

use App\User;
use App\Shop;
use App\SessionType;
use App\BookingInfo;
use App\UserGenderPreference;
use App\BookingMassage;
use App\UserPeople;
use App\BookingMassageStart;
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
        'booking_date_time',
        'pack_id',
        'book_platform'
    ];

    protected $hidden = ['is_removed', 'updated_at', 'deleted_at'];
    
    const BOOKING_TYPE_IMC = '1';
    const BOOKING_TYPE_HHV = '2';
    const BOOKING_ONGOING = '0';
    const BOOKING_WAITING = '1';
    const BOOKING_FUTURE = '2';
    const BOOKING_COMPLETED = '3';
    const BOOKING_CANCELLED = '4';
    const BOOKING_PAST = '5';
    const BOOKING_TODAY = '6';
    const BOOKING_PLATFORM_APP = '0';
    const BOOKING_PLATFORM_WEB = '1';
    const MASSAGES = '0';
    const THERAPIES = '1';
    const TODAY = '0';
    const YESTERDAY = '1';
    const THIS_WEEK = '2';
    const THIS_MONTH = '3';
    const TOMORROW = '4';

    public static $defaultTableFutons = ['0', '1', '2'];
    public static $tableFutons = ['0', '1', '2'];

    public static $bookingTypes = [
        self::BOOKING_TYPE_IMC => 'In massage center',
        self::BOOKING_TYPE_HHV => 'Home / Hotel visit'
    ];
    
    public static $bookingPlatforms = [
        self::BOOKING_PLATFORM_APP => 'App',
        self::BOOKING_PLATFORM_WEB => 'Web'
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
            'pack_id'              => ['nullable', 'integer', 'exists:' . Pack::getTableName() . ',id'],
            'total_persons'        => ['required', 'integer'],
            'bring_table_futon'    => ['in:' . implode(",", self::$tableFutons)],
            'table_futon_quantity' => ['integer'],
            'booking_date_time'    => ['required']
        ], $validatorExtended));

        return $validator;
    }

    public function getCreatedAtAttribute($value)
    {
        if (empty($value)) {
            return $value;
        }

        return strtotime($value) * 1000;
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
    
    public function getBookPlatformAttribute($value)
    {
        return (isset(self::$bookingPlatforms[$value])) ? self::$bookingPlatforms[$value] : $value;
    }

    public function bookingInfo()
    {
        return $this->hasMany('App\BookingInfo', 'booking_id', 'id');
    }

    public function bookingInfoWithFilters($type = 'today')
    {
        return $this->hasMany('App\BookingInfo', 'booking_id', 'id')->select(['id', 'booking_id', 'id as booking_info_id', 'massage_date', 'massage_time', 'user_people_id', 'therapist_id', 'is_done'])
                    ->where(function($query) use($type) {
                        $query->filterDatas();
                    })->with(['userPeople' => function($query) {
                        return $query->filterDatas();
                    }, 'therapist', 'bookingMassages' => function($query) {
                        $query->with(['servicePrices' => function($query1) {
                            return $query1->with('service');
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
        $id                 = $request->get('booking_info_id');
        $bookingDate        = $request->get('booking_date');
        $therapistId        = $request->get('id');
        $shopId             = $request->get('shop_id');
        $type               = $request->get('type');
        $bookingsFilter     = $request->get('bookings_filter');
        $therapist          = $request->get('therapist_id');
        $roomId             = $request->get('room_id');
        $bookingId          = $request->get('booking_id');
        $date               = $request->get('date');
        $userId             = $request->get('user_id');
        $bookingMassageId   = $request->get('booking_massage_id');
        $month              = $request->get('month');
        $service            = $request->get('service');
        $dateFilter         = $request->get('date_filter');
        $sessionId          = $request->get('session_id');
        $serviceId          = $request->get('service_id');

        $userPeopleModel                = new UserPeople();
        $bookingInfoModel               = new BookingInfo();
        $sessionTypeModel               = new SessionType();
        $serviceModel                   = new Service();
        $bookingMassageModel            = new BookingMassage();
        $servicePriceModel              = new ServicePricing();
        $serviceTimingModel             = new ServiceTiming();
        $massagePreferenceOptionModel   = new MassagePreferenceOption();
        $shopModel                      = new Shop();
        $userModel                      = new User();
        $roomModel                      = new Room();
        $therapistModel                 = new Therapist();
        $userGenderPreferenceModel      = new UserGenderPreference();
        $bookingMassageStartModel       = new BookingMassageStart();

        $data = $this
                ->select(
                        DB::RAW(
                            $this::getTableName().'.id as booking_id,'.
                            $bookingInfoModel::getTableName() . '.id as booking_info_id, '.
                            $bookingMassageModel::getTableName() . '.id as booking_massage_id, ' .
                            $this::getTableName() . '.user_id as client_id,'.
                            'CONCAT_WS(" ",' . $userModel::getTableName() . '.name,' . $userModel::getTableName() . '.surname) as client_name, ' . 
                            'CASE ' . $userPeopleModel::getTableName() . '.gender WHEN "m" THEN "' . $userPeopleModel->gender[$userPeopleModel::MALE] . '" WHEN "f" THEN "' . $userPeopleModel->gender[$userPeopleModel::FEMALE] . '" ELSE "" END as client_gender, ' . 
                            $userPeopleModel::getTableName() . '.age as client_age, ' . 
                            $bookingInfoModel::getTableName() . '.user_people_id, '.
                            $userPeopleModel::getTableName() . '.name as user_people_name, '. 
                            $this::getTableName().'.session_id as sessionId,'.
                            $sessionTypeModel::getTableName() . '.type as session_type, ' .
                            $this::getTableName() . '.booking_type, ' . 
                            $this::getTableName() . '.book_platform, ' .
                            $this::getTableName().'.shop_id as shop_id,'.
                            $shopModel::getTableName() . '.name as shop_name, ' . 
                            'CONCAT(' . $shopModel::getTableName() . '.address, " ", ' . $shopModel::getTableName() . '.address2) as shop_address, ' . 
                            $therapistModel::getTableName().'.id as therapist_id,'.
                            'CONCAT_WS(" ",' . $therapistModel::getTableName() . '.name,' . $therapistModel::getTableName() . '.surname) as therapistName, ' . 
                            $roomModel::getTableName().'.id as room_id,'.
                            $roomModel::getTableName().'.name as roomName,'.
                            $roomModel::getTableName().'.total_beds as totalBeds,'.
                            $serviceModel::getTableName() . '.english_name as service_name,' . 
                            $serviceModel::getTableName() . '.service_type as service_type,' . 
                            $bookingInfoModel::getTableName() . '.massage_date as massage_date, UNIX_TIMESTAMP(' . 
                            $bookingMassageModel::getTableName() . '.massage_date_time) * 1000 as massage_date_time,'.
                            $bookingInfoModel::getTableName() . '.massage_date as massage_date, UNIX_TIMESTAMP(' . 
                            $bookingInfoModel::getTableName() . '.massage_time) * 1000 as massage_start_time, UNIX_TIMESTAMP(' . 
                            'DATE_ADD(' . $bookingInfoModel::getTableName() . '.massage_time, INTERVAL ' . $serviceTimingModel::getTableName() . '.time MINUTE)) * 1000 as massage_end_time, ' . 
                            'DATE_FORMAT(' . $bookingInfoModel::getTableName() . '.massage_date, "%a") as massage_day_name, ' . 
                            'CONCAT(' . $serviceTimingModel::getTableName() . '.time, " ", "Mins") as massage_duration, ' . 
                            $servicePriceModel::getTableName().'.price,'.
                            $servicePriceModel::getTableName().'.cost,'.
                            'gender.name as gender_preference, ' . 
                            'pressure.name as pressure_preference, ' . 
                            $this::getTableName() . '.special_notes as notes, ' .
                            $bookingMassageModel::getTableName() . '.notes_of_injuries as injuries, ' . 
                            'focus_area.name as focus_area, ' . 
                            $this::getTableName() . '.table_futon_quantity, ' . 
                            $this::getTableName() . '.created_at, ' . 
                            $userModel::getTableName() . '.qr_code_path, ' . 
                            $userGenderPreferenceModel::getTableName().'.name as genderPreference,' . 
                            $bookingMassageModel::getTableName().'.service_pricing_id,' . 
                            $bookingMassageModel::getTableName().'.observation,' . 
                            $bookingMassageModel::getTableName() . '.is_confirm, ' . 
                            $bookingInfoModel::getTableName().'.is_done,' . 
                            $bookingInfoModel::getTableName().'.is_cancelled,' . 
                            $bookingInfoModel::getTableName().'.cancel_type,' . 
                            $bookingInfoModel::getTableName().'.cancelled_reason, ' . 
                            $bookingMassageStartModel::getTableName().'.start_time as actual_start_time, ' . 
                            $bookingMassageStartModel::getTableName().'.end_time as actual_end_time'
                        )
                )
                ->join($bookingInfoModel::getTableName(), $this::getTableName() . '.id', '=', $bookingInfoModel::getTableName() . '.booking_id')
                ->join($bookingMassageModel::getTableName(), $bookingInfoModel::getTableName() . '.id', '=', $bookingMassageModel::getTableName() . '.booking_info_id')
                ->join($shopModel::getTableName(), $this::getTableName() . '.shop_id', '=', $shopModel::getTableName() . '.id')
                ->join($userModel::getTableName(), $this::getTableName() . '.user_id', '=', $userModel::getTableName() . '.id')
                ->join($sessionTypeModel::getTableName(), $this::getTableName() . '.session_id', '=', $sessionTypeModel::getTableName() . '.id')
                ->leftJoin($userPeopleModel::getTableName(), $bookingInfoModel::getTableName() . '.user_people_id', '=', $userPeopleModel::getTableName() . '.id')
                ->leftJoin($roomModel::getTableName(),$bookingMassageModel::getTableName().'.room_id', '=', $roomModel::getTableName().'.id')
                ->leftJoin($userGenderPreferenceModel::getTableName(),$bookingMassageModel::getTableName().'.gender_preference', '=', $userGenderPreferenceModel::getTableName().'.id')
                ->leftJoin($therapistModel::getTableName(),$bookingInfoModel::getTableName().'.therapist_id', '=', $therapistModel::getTableName().'.id')
                ->leftJoin($servicePriceModel::getTableName(), $bookingMassageModel::getTableName() . '.service_pricing_id', '=', $servicePriceModel::getTableName() . '.id')
                ->leftJoin($serviceModel::getTableName(), $servicePriceModel::getTableName() . '.service_id', '=', $serviceModel::getTableName() . '.id')
                ->leftJoin($serviceTimingModel::getTableName(), $servicePriceModel::getTableName() . '.service_timing_id', '=', $serviceTimingModel::getTableName() . '.id')
                ->leftJoin($massagePreferenceOptionModel::getTableName() . ' as gender', $bookingMassageModel::getTableName() . '.gender_preference', '=', 'gender.id')
                ->leftJoin($massagePreferenceOptionModel::getTableName() . ' as pressure', $bookingMassageModel::getTableName() . '.pressure_preference', '=', 'pressure.id')
                ->leftJoin($massagePreferenceOptionModel::getTableName() . ' as focus_area', $bookingMassageModel::getTableName() . '.focus_area_preference', '=', 'focus_area.id')
                ->leftJoin($bookingMassageStartModel::getTableName(), $bookingMassageModel::getTableName() . '.id', '=', $bookingMassageStartModel::getTableName() . '.booking_massage_id')
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
            $date = Carbon::createFromTimestampMs($date)->format('Y-m-d');
            
            $data->where($bookingInfoModel::getTableName() . '.massage_date', $date);
        }
        if ($month) {
            $data->whereMonth($bookingInfoModel::getTableName() . '.massage_date', '=', $month->month)
                 ->whereYear($bookingInfoModel::getTableName() . '.massage_date', '=', $month->year);
        }
        if($userId) {
            $data->where($this::getTableName() . '.user_id', '=', $userId);
        }
        if ($bookingMassageId) {
            $data->where($bookingMassageModel::getTableName() . '.id', $bookingMassageId);
        }
        if ($sessionId) {
            $data->where($this::getTableName() . '.session_id', $sessionId);
        }
        if ($serviceId) {
            $data->where($serviceModel::getTableName() . '.id', $serviceId);
        }

        if (isset($service)) {
            if ($service == self::MASSAGES) {
                $data->where($serviceModel::getTableName() . '.service_type', Service::MASSAGE);
            }
            if ($service == self::THERAPIES) {
                $data->where($serviceModel::getTableName() . '.service_type', Service::THERAPY);
            }
        }

        if (isset($bookingsFilter)) {
            if (in_array(self::BOOKING_ONGOING, $bookingsFilter)) {
                $data->where([$bookingMassageModel::getTableName() . '.is_confirm' => (string)BookingMassage::IS_CONFIRM,
                    $bookingInfoModel::getTableName() . '.massage_date' => Carbon::now()->format('Y-m-d')]);
            }
            if (in_array(self::BOOKING_WAITING, $bookingsFilter)) {
                $data->where([$bookingMassageModel::getTableName() . '.is_confirm' => (string)BookingMassage::IS_NOT_CONFIRM,
                              $bookingInfoModel::getTableName() . '.is_cancelled' => (string)BookingInfo::IS_NOT_CANCELLED]);

                $data->whereDate($bookingInfoModel::getTableName() . '.massage_date', '>=', Carbon::now()->format('Y-m-d'));
            }
            if (in_array(self::BOOKING_FUTURE, $bookingsFilter)) {
                $data->where($bookingInfoModel::getTableName() . '.massage_date', '>=', Carbon::now()->format('Y-m-d'))
                        ->where($bookingInfoModel::getTableName() . '.is_cancelled', (string)BookingInfo::IS_NOT_CANCELLED);
            }
            if (in_array(self::BOOKING_COMPLETED, $bookingsFilter)) {
                $data->where($bookingInfoModel::getTableName() . '.is_done', (string) BookingInfo::IS_DONE)
                        ->where($bookingInfoModel::getTableName() . '.is_cancelled', (string)BookingInfo::IS_NOT_CANCELLED);
            }
            if (in_array(self::BOOKING_CANCELLED, $bookingsFilter)) {
                $data->where($bookingInfoModel::getTableName() . '.is_cancelled', (string)BookingInfo::IS_CANCELLED);
            }
            if (in_array(self::BOOKING_PAST, $bookingsFilter)) {
                $data->where($bookingInfoModel::getTableName() . '.massage_date', '<=', Carbon::now()->format('Y-m-d'));
            }
            if (in_array(self::BOOKING_TODAY, $bookingsFilter)) {
                $data->where($bookingInfoModel::getTableName() . '.massage_date', Carbon::now()->format('Y-m-d'));
            }
        }
        if(isset($dateFilter)) {
            $now = Carbon::now();
            if ($dateFilter == self::YESTERDAY) {
                $data->where($bookingInfoModel::getTableName() . '.massage_date', Carbon::yesterday()->format('Y-m-d'));
            }
            if ($dateFilter == self::TOMORROW) {
                $data->where($bookingInfoModel::getTableName() . '.massage_date', Carbon::tomorrow()->format('Y-m-d'));
            }
            if ($dateFilter == self::THIS_WEEK) {
                $weekStartDate = $now->startOfWeek()->format('Y-m-d');
                $weekEndDate = $now->endOfWeek()->format('Y-m-d');

                $data->whereBetween($bookingInfoModel::getTableName() . '.massage_date', [$weekStartDate, $weekEndDate]);
            }
            if ($dateFilter == self::THIS_MONTH) {
                $data->whereMonth($bookingInfoModel::getTableName() . '.massage_date', $now->month)
                     ->whereYear($bookingInfoModel::getTableName() . '.massage_date', $now->year);
            }
        }

        $data = $data->orderBy($bookingInfoModel::getTableName().'.massage_date','DESC')->get();

        if (!empty($data) && !$data->isEmpty()) {
            $data->map(function(&$record) use($userModel, $bookingMassageModel, $bookingMassageStartModel, $bookingInfoModel) {
                $record->qr_code_path = $userModel->getQrCodePathAttribute($record->qr_code_path);

                if (empty($record->qr_code_path)) {
                    $find = $userModel::find($record->client_id);

                    if (!empty($find)) {
                        $find->storeQRCode();

                        $record->qr_code_path = $find->qr_code_path;
                    }
                }

                $record->massage_date = $bookingInfoModel->getMassageDateAttribute($record->massage_date);

                $bookingType = $record->getAttributes()['booking_type'];

//                unset($record->booking_type);

                $record->booking_type_value = $bookingType;

                $bookingMassage = $bookingMassageModel::find($record->booking_massage_id);

                $record->service_status = $bookingMassage->getServiceStatus();

                if (!empty($record->actual_start_time)) {
                    $record->actual_start_time = $bookingMassageStartModel->getStartTimeAttribute($record->actual_start_time);
                }

                if (!empty($record->actual_end_time)) {
                    $record->actual_end_time = $bookingMassageStartModel->getEndTimeAttribute($record->actual_end_time);
                }
            });

            // $data->put('total_massages', $bookingInfoModel->getMassageCountByTherapist($therapistId));
            // $data->put('total_therapies', $bookingInfoModel->getTherapyCountByTherapist($therapistId));
        }

        return $data;
    }

    public function getWherePastFuture($userId, $isPast = false, $isUpcoming = true, $isPending = false)
    {
        $now                 = Carbon::now();

        $modelBookingMassage = new BookingMassage();
        $modelBookingInfo    = new BookingInfo();
        $modelShop           = new Shop();
        $modelUserPeople     = new UserPeople();
        $modelSessionType    = new SessionType();
        $modelService        = new Service();
        $modelServicePrice   = new ServicePricing();
        $modelServiceTiming  = new ServiceTiming();

        $bookings = $this->select(DB::RAW(self::getTableName() . '.id, massage_date, massage_time, ' . self::getTableName() . '.booking_type, ' . $modelShop::getTableName() . '.name as shop_name, ' . $modelShop::getTableName() . '.description as shop_description, ' . $modelSessionType::getTableName() . '.type as session_type, user_people_id, ' . $modelBookingInfo::getTableName() . '.id as bookingInfoId, ' . $modelUserPeople::getTableName() . '.name as user_people_name, ' . $modelUserPeople::getTableName() . '.age as user_people_age, ' . $modelUserPeople::getTableName() . '.gender as user_people_gender, ' . $modelUserPeople::getTableName() . '.photo as user_prople_photo'))
                         ->join($modelBookingInfo::getTableName(), self::getTableName() . '.id', '=', $modelBookingInfo::getTableName() . '.booking_id')
                         ->join($modelUserPeople::getTableName(), $modelBookingInfo::getTableName() . '.user_people_id', '=', $modelUserPeople::getTableName() . '.id')
                         ->leftJoin($modelShop::getTableName(), self::getTableName() . '.shop_id', '=', $modelShop::getTableName() . '.id')
                         ->leftJoin($modelSessionType::getTableName(), self::getTableName() . '.session_id', '=', $modelSessionType::getTableName() . '.id')
                         ->where($modelBookingInfo::getTableName() . '.massage_date', ($isPast === true ? '<' : '>='), $now);

        if (!empty($userId) && is_numeric($userId)) {
            $bookings->where(self::getTableName() . '.user_id', $userId);
        }

        $bookings       = $bookings->get();

        $returnBookings = [];

        if (!empty($bookings) && !$bookings->isEmpty()) {

            $userPeopleIds  = $bookings->pluck('user_people_id');
            $userPeoples    = $massagePrices = $bookingMassages = $massages = [];

            if (!empty($userPeopleIds) && !$userPeopleIds->isEmpty()) {
                $userPeoples = $modelUserPeople->select('id', 'name', 'age', 'gender')->whereIn('id', array_unique($userPeopleIds->toArray()))->get();

                if (!empty($userPeoples) && !$userPeoples->isEmpty()) {
                    $userPeoples = $userPeoples->keyBy('id');
                }
            }

            $return = [];
            $bookings->map(function($data, $index) use(&$return) {
                $return[$data->id][] = $data;
            });

            foreach ($return as $bookingId => $datas) {
                $returnUserPeoples = [];

                foreach ($datas as $index => $data) {
                    $bookingInfoId = $data->bookingInfoId;
                    $userPeopleId  = $data->user_people_id;

                    $returnUserPeoples[$bookingId][$index] = [
                        'id'     => $userPeopleId,
                        'name'   => $data->user_people_name,
                        'age'    => $data->user_people_age,
                        'gender' => $data->user_people_gender,
                        'photo'  => $data->user_prople_photo
                    ];

                    $bookingMassages = $modelBookingMassage
                                            ->select($modelService::getTableName() . '.english_name', $modelService::getTableName() . '.portugese_name', $modelBookingMassage::getTableName() . '.price', $modelServiceTiming::getTableName() . '.time')
                                            ->join($modelServicePrice::getTableName(), $modelBookingMassage::getTableName() . '.service_pricing_id', '=', $modelServicePrice::getTableName() . '.id')
                                            ->join($modelServiceTiming::getTableName(), $modelServicePrice::getTableName() . '.service_timing_id', '=', $modelServiceTiming::getTableName() . '.id')
                                            ->join($modelService::getTableName(), $modelServicePrice::getTableName() . '.service_id', '=', $modelService::getTableName() . '.id')
                                            ->where('booking_info_id', $bookingInfoId)
                                            ->get();

                    if (!empty($bookingMassages) && !$bookingMassages->isEmpty()) {
                        $returnUserPeoples[$bookingId][$index]['booking_massages'] = $bookingMassages;

                        if (isset($returnBookings[$bookingId]['total_price'])) {
                            $returnBookings[$bookingId]['total_price'] += $bookingMassages->sum('price');
                        } else {
                            $returnBookings[$bookingId]['total_price'] = $bookingMassages->sum('price');
                        }
                    }

                    $returnBookings[$bookingId] = [
                        'id'               => $bookingId,
                        'booking_type'     => $data->booking_type,
                        'shop_name'        => $data->shop_name,
                        'shop_description' => $data->shop_description,
                        'session_type'     => $data->session_type,
                        'massage_date'     => $data->massage_date,
                        'massage_time'     => $data->massage_time,
                        'total_price'      => number_format($returnBookings[$bookingId]['total_price'], 2)
                    ];
                }

                $returnBookings[$bookingId]['user_people'] = $returnUserPeoples[$bookingId];
            }

            $returnBookings = array_values($returnBookings);
        }

        return $returnBookings;
    }
}
