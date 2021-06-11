<?php

namespace App;

use App\Therapist;
use App\Shop;
use App\ShopShift;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use DB;

class TherapistWorkingSchedule extends BaseModel
{
    protected $fillable = [
        'date',       
        'therapist_id',
        'shift_id',
        'shop_id',
        'is_working',
        'is_exchange'
    ];
    
    const WORKING       = '1';
    const NOT_WORKING   = '0';
    
     public static $isWorking = [
        self::WORKING       => 'Working',
        self::NOT_WORKING   => 'Nope'
    ];
     
    const IS_EXCHANGE    = '1';
    const NOT_EXCHANGE   = '0';
    
     public static $isExchange = [
        self::IS_EXCHANGE    => 'Exchange',
        self::NOT_EXCHANGE   => 'Not exchange'
    ];
   
    public function __construct($attributes = [])
    {
        parent::__construct($attributes);

    }

    public function validator(array $data)
    {
        return Validator::make($data, [
            'date'          => ['required', 'date:Y-m-d'],
            'shift_id'      => ['required', 'exists:' . ShopShift::getTableName() . ',id'],
            'therapist_id'  => ['required', 'exists:' . Therapist::getTableName() . ',id'],
            'shop_id'       => ['required', 'exists:' . Shop::getTableName() . ',id'],
            'is_working'    => ['in:' . implode(",", array_keys(self::$isWorking))],
            'is_exchange'   => ['in:' . implode(",", array_keys(self::$isExchange))]
        ]);
    }

    public function getDateAttribute($value)
    {
        return strtotime($value) * 1000;
    }
    
    public function therapist() {
        
        return $this->hasOne('App\Therapist', 'id', 'therapist_id');
    }
    
    public function shifts() {
        
        return $this->hasOne('App\ShopShift', 'id', 'shift_id');
    }
    
    public function getIsWorkingAttribute($value)
    {
        return (isset(self::$isWorking[$value])) ? self::$isWorking[$value] : $value;
    }
    
    public function getIsExchangeAttribute($value)
    {
        return (isset(self::$isExchange[$value])) ? self::$isExchange[$value] : $value;
    }

    public function shops() {
        
        return $this->hasMany('App\Shop', 'id', 'shop_id');
    }

    /**
     * Get schedule by date.  If not pass then it will take current month.
     *
     * @$month date
     */
    public static function getScheduleByDate(int $id, $date)
    {
        $currentDate   = Carbon::now();
        $date          = empty($date) ? $currentDate : new Carbon($date);

        $data = self::whereDate('date', $date)->where('therapist_id', $id)->get();

        return $data;
    }

    /**
     * Get schedule by month.  If not pass then it will take current month.
     *
     * @$month date
     */
    public static function getScheduleByMonth(int $id, $month)
    {
        $currentMonth   = Carbon::now();
        $month          = empty($month) ? $currentMonth : new Carbon($month);
        $startDate      = $month->format('Y') . '-' . $month->format('m') . '-01';
        $endDate        = $month->format('Y') . '-' . $month->format('m') . '-' . $month->endOfMonth()->format('d');

        $data = self::whereBetween('date', [$startDate, $endDate])->where('therapist_id', $id)->get();

        return $data;
    }

    public static function getAvailabilities(int $id, $date)
    {
        $now   = Carbon::now();
        $date  = Carbon::createFromTimestampMs($date);
        $date  = strtotime($date) > 0 ? $date->format('Y-m-d') : $now->format('Y-m-d');
        $data  = [];
        $model = new Shop();

        /*$data = TherapistWorkingSchedule::with('therapist', 'shifts')->where(['date' => $date, 'therapist_id' => $id, 
            'is_exchange' => TherapistWorkingSchedule::NOT_EXCHANGE, 'is_working' => TherapistWorkingSchedule::WORKING])->get()->groupBy('shop_id');*/

        $getShops = $model::select($model::getTableName() . '.name as shop_name', $model::getTableName() . '.featured_image', TherapistWorkingSchedule::getTableName() . '.*', ShopShift::getTableName() . '.from', ShopShift::getTableName() . '.to')
                        ->join(TherapistWorkingSchedule::getTableName(), $model::getTableName() . '.id', '=', TherapistWorkingSchedule::getTableName() . '.shop_id')
                        ->leftJoin(ShopShift::getTableName(), function($leftJoin) use($model) {
                            $leftJoin->on(TherapistWorkingSchedule::getTableName() . '.shift_id', '=', ShopShift::getTableName() . '.id')
                                     ->where($model::getTableName() . '.id', '=', DB::raw(ShopShift::getTableName() . '.shop_id'));
                        })
                        ->whereDate('date', $date)
                        ->where('is_exchange', TherapistWorkingSchedule::NOT_EXCHANGE)
                        ->where('is_working', TherapistWorkingSchedule::WORKING)
                        ->where('therapist_id', $id)
                        ->whereNotNull(ShopShift::getTableName() . '.from')
                        ->whereNotNull(ShopShift::getTableName() . '.to')
                        ->get();

        if (!empty($getShops) && !$getShops->isEmpty()) {
            foreach ($getShops as $row) {
                if (empty($data[$row->shop_id])) {
                    $data[$row->shop_id] = [
                        'shop_id'        => $row->shop_id,
                        'shop_name'      => $row->shop_name,
                        'featured_image' => $model->getFeaturedImageAttribute($row->featured_image),
                        'shift_date'     => strtotime($row->date) * 1000
                    ];
                }

                $data[$row->shop_id]['shifts'][] = [
                    'shift_id' => $row->shift_id,
                    'from'     => strtotime($row->from) * 1000,
                    'to'       => strtotime($row->to) * 1000
                ];
            }

            $data = reset($data);
        }

        /*$availability = [];
        if ($data->count()) {
            
            foreach ($data as $key => $value) {
                $availability['therapist_id'] = $id;
                $availability['name'] = $value[0]->therapist->name;
                $availability['surname'] = $value[0]->therapist->surname;
                foreach ($value as $key => $shift) {
                     $therapist_shifts[] = [
                        'shop_id' => $shift->shop_id,
                        'date' => $shift->date,
                        'shift_id' => $shift->shift_id,
                        'from' => $shift->shifts->from,
                        'to' => $shift->shifts->to
                    ];
                }
                $availability['shifts'][] = $therapist_shifts;
                unset($therapist_shifts);
            }
        }*/

        return collect($data);
    }

    public static function getMissingDays(int $id, $month)
    {
        $currentMonth   = Carbon::now();
        $month          = empty($month) ? $currentMonth : new Carbon($month);
        $startDate      = $month->format('Y') . '-' . $month->format('m') . '-01';
        $endDate        = $month->format('Y') . '-' . $month->format('m') . '-' . $month->endOfMonth()->format('d');

        $date = self::where('therapist_id', $id)->whereBetween('date', [$startDate, $endDate])->where('is_absent', self::ABSENT)->get();

        return $date;
    }
}
