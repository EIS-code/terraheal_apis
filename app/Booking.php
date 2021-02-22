<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use App\User;
use App\Shop;
use App\SessionType;
use App\BookingInfo;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use DB;
use Carbon\Carbon;

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
        'user_id'
    ];

    const BOOKING_TYPE_IMC = '1';
    const BOOKING_TYPE_HHV = '0';

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
        return $this->hasMany('App\BookingInfo', 'booking_id', 'id')->select(['booking_id', 'id as booking_info_id', 'massage_date', 'massage_time', 'user_people_id', 'therapist_id'])
                    ->where(function($query) use($type) {
                        $query->filterDatas();
                    })->with(['userPeople' => function($query) {
                        return $query->filterDatas();
                    }, 'therapist']);
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

        $userPeopleModel                = new UserPeople();
        $bookingInfoModel               = new BookingInfo();
        $sessionTypeModel               = new SessionType();
        $massageModel                   = new Massage();
        $bookingMassageModel            = new BookingMassage();
        $massagePriceModel              = new MassagePrice();
        $massageTimingModel             = new MassageTiming();
        $massagePreferenceOptionModel   = new MassagePreferenceOption();
        $shopModel                      = new Shop();

        $data = $this
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
                            'CONCAT(' . $massageTimingModel::getTableName() . '.time, " ", "Mins") as massage_duration'
                        )
                )
                ->join($bookingInfoModel::getTableName(), $this::getTableName() . '.id', '=', $bookingInfoModel::getTableName() . '.booking_id')
                ->join($userPeopleModel::getTableName(), $bookingInfoModel::getTableName() . '.user_people_id', '=', $userPeopleModel::getTableName() . '.id')
                ->join($shopModel::getTableName(), $this::getTableName() . '.shop_id', '=', $shopModel::getTableName() . '.id')
                ->leftJoin($sessionTypeModel::getTableName(), $this::getTableName() . '.session_id', '=', $sessionTypeModel::getTableName() . '.id')
                ->leftJoin($bookingMassageModel::getTableName(), $bookingInfoModel::getTableName() . '.id', '=', $bookingMassageModel::getTableName() . '.booking_info_id')
                ->leftJoin($massagePriceModel::getTableName(), $bookingMassageModel::getTableName() . '.massage_prices_id', '=', $massagePriceModel::getTableName() . '.id')
                ->leftJoin($massageModel::getTableName(), $massagePriceModel::getTableName() . '.massage_id', '=', $massageModel::getTableName() . '.id')
                ->leftJoin($massageTimingModel::getTableName(), $massagePriceModel::getTableName() . '.massage_timing_id', '=', $massageTimingModel::getTableName() . '.id')
                ->leftJoin($massagePreferenceOptionModel::getTableName() . ' as gender', $bookingMassageModel::getTableName() . '.gender_preference', '=', 'gender.id')
                ->leftJoin($massagePreferenceOptionModel::getTableName() . ' as pressure', $bookingMassageModel::getTableName() . '.pressure_preference', '=', 'pressure.id')
                ->leftJoin($massagePreferenceOptionModel::getTableName() . ' as focus_area', $bookingMassageModel::getTableName() . '.focus_area_preference', '=', 'focus_area.id')
                ->where($bookingInfoModel::getTableName() . '.therapist_id', (int)$therapistId)
                ->where($this::getTableName() . '.shop_id', (int)$shopId);

        if (!empty($id)) {
            $data->where($bookingInfoModel::getTableName() . '.id', (int)$id);
        }

        if (!empty($bookingDate)) {
            $bookingDate = Carbon::createFromTimestampMs($bookingDate)->format('Y-m-d');

            $data->whereDate($bookingInfoModel::getTableName() . '.massage_date', $bookingDate);
        }

        return $data->get();
    }
}
