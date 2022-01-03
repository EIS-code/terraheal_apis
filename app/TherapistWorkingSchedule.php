<?php

namespace App;

use App\Therapist;
use App\Shop;
use App\ShopShift;
use App\BookingMassage;
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
    
    public function therapistBreakTime()
    {
        return $this->hasMany('App\TherapistWorkingScheduleBreak', 'schedule_id', 'id');
    }
    
    public function therapist() {
        
        return $this->hasOne('App\Therapist', 'id', 'therapist_id');
    }
    
    public function shop() {
        
        return $this->hasOne('App\Shop', 'id', 'shop_id');
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

        $data = self::whereBetween('date', [$startDate, $endDate])->where(['therapist_id' => $id, 'is_exchange' => self::NOT_EXCHANGE])->get()->groupBy('date');

        $schedule = [];
        foreach ($data as $key => $dates) {
            
            $date = $dates->first();
            $schedule[] = [
                'date' => $date->date,
                'is_working' => $date->is_working
            ];
            
        }
        return $schedule;
    }

    public static function getAvailabilities(int $id, $date)
    {
        $now   = Carbon::now();
        $date  = Carbon::createFromTimestampMs($date);
        $date  = strtotime($date) > 0 ? $date->format('Y-m-d') : $now->format('Y-m-d');
        $data  = [];
        $model = new Shop();

        $getShops = DB::table('therapist_working_schedules')
                ->join('shops', 'shops.id', '=', 'therapist_working_schedules.shop_id')
                ->join('shop_shifts', 'shop_shifts.id', '=', 'therapist_working_schedules.shift_id')
                ->select('shops.id', 'shops.name', 'shops.featured_image', 'therapist_working_schedules.*', 'shop_shifts.from', 'shop_shifts.to')
                ->where(['therapist_working_schedules.date' => $date,
                    'therapist_working_schedules.is_working' => TherapistWorkingSchedule::WORKING,
                    'therapist_working_schedules.is_exchange' => TherapistWorkingSchedule::NOT_EXCHANGE])
                ->where('therapist_working_schedules.therapist_id', $id)
                ->get()->groupBy('shop_id');
                        
        $availability = $data = [];
        if (!empty($getShops) && !$getShops->isEmpty()) {
            foreach ($getShops as $shops) {
                $row = $shops->first();
                $data = [
                    'shop_id'        => $row->shop_id,
                    'shop_name'      => $row->name,
                    'featured_image' => $model->getImageAttribute($row->featured_image),
                ];
                foreach ($shops as $shop) {
                    $shifts[] = [
                        'shift_id' => $shop->shift_id,
                        'from'     => strtotime($shop->from) * 1000,
                        'to'       => strtotime($shop->to) * 1000
                    ];
                }
                $data['shifts'] = $shifts;
                array_push($availability, $data);
                unset($shifts);
                unset($data);
            }
        }

        return collect($availability);
    }

    public static function getMissingDays(int $id, $month)
    {
        $currentMonth   = Carbon::now();
        $month          = empty($month) ? $currentMonth : new Carbon($month);
        $startDate      = $month->format('Y') . '-' . $month->format('m') . '-01';
        $endDate        = $month->format('Y') . '-' . $month->format('m') . '-' . $month->endOfMonth()->format('d');

        $data           = self::select([self::getTableName() . '.id', self::getTableName() . '.date'])
                          ->where(self::getTableName() . '.therapist_id', $id)
                          ->whereBetween(self::getTableName() . '.date', [$startDate, $endDate])
                          ->leftJoin(BookingMassage::getTableName(), function($query) {
                                $query->on(self::getTableName() . '.therapist_id', '=', BookingMassage::getTableName() . '.therapist_id')
                                      ->whereRaw('DATE(' . self::getTableName() . '.date) = DATE(' . BookingMassage::getTableName() . '.massage_date_time)');
                          })
                          ->whereNull(BookingMassage::getTableName() . '.id')
                          ->get();

        return $data;
    }
}
