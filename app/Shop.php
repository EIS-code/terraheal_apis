<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Auth\Passwords\CanResetPassword;
use App\BookingInfo;
use App\UserPack;
use App\TherapistWorkingSchedule;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;
use App\ServicePricing;
use App\Booking;
use Illuminate\Support\Facades\Storage;

class Shop extends BaseModel implements CanResetPasswordContract
{
    
    use CanResetPassword, Notifiable;
    
    protected $fillable = [
        'name',
        'featured_image',
        'address',
        'address2',
        'description',
        'longitude',
        'latitude',
        'zoom',
        'owner_name',
        'owner_surname',
        'owner_email',
        'owner_mobile_number',
        'owner_mobile_number_alternative',
        'finacial_situation',
        'email',
        'tel_number',
        'time_zone',
        'shop_user_name',
        'shop_password',
        'city_id',
        'province_id',
        'country_id',
        'currency_id',
        'pin_code'
    ];

    protected $hidden = ['shop_password','remember_token', 'created_at', 'updated_at'];

    public $fileSystem  = 'public';
    public $storageFolderName = 'shop\\featured\\';
    
    const IS_ADMIN = '0';
    const MASSAGES = '0';
    const THERAPIES = '1';
    
    public function validator(array $data, $id = false)
    {
        if (!empty($id)) {
            $emailValidator      = ['unique:shops,email,' . $id];
        } else {
            $emailValidator      = ['unique:shops'];
        }

        return Validator::make($data, [
            'name'                => ['nullable', 'string', 'max:255'],
            'address'             => ['nullable', 'string', 'max:255'],
            'address2'            => ['nullable', 'string', 'max:255'],
            'description'         => ['nullable', 'string'],
            'longitude'           => ['nullable', 'between:0,99.99'],
            'latitude'            => ['nullable', 'between:0,99.99'],
            'zoom'                => ['nullable', 'integer'],
            'tel_number'          => ['nullable', 'string', 'max:50'],
            'email'               => array_merge(['required', 'string', 'email', 'max:255'], $emailValidator),
            'time_zone'           => ['nullable', 'string', 'max:255'],
            'shop_user_name'      => ['required', 'string', 'max:255'],
            'shop_password'       => ['required', 'string', 'max:255'],
            'city_id'             => ['nullable'],
            'province_id'         => ['nullable'],
            'country_id'          => ['nullable'],
            'currency_id'         => ['nullable'],
            'pin_code'            => ['nullable', 'string', 'max:255']
        ]);
    }

    public function validatorOwner(array $data, $id = false)
    {
        if (!empty($id)) {
            $ownerEmailValidator = ['unique:shops,owner_email,' . $id];
        } else {
            $ownerEmailValidator = ['unique:shops'];
        }

        return Validator::make($data, [
            'shop_id'             => ['required', 'integer', 'exists:' . Shop::getTableName() . ',id'],
            'owner_name'          => ['nullable', 'string', 'max:255'],
            'owner_surname'       => ['nullable', 'string', 'max:255'],
            'owner_mobile_number' => ['nullable', 'string', 'max:50'],
            'owner_email'         => array_merge(['required', 'string', 'email', 'max:255'], $ownerEmailValidator),
            'financial_situation' => ['nullable', 'string'],
            'owner_mobile_number_alternative' => ['nullable', 'string', 'max:50']
        ]);
    }

    public function validatorLocation(array $data)
    {
        return Validator::make($data, [
            'address'     => ['required', 'string', 'max:255'],
            'address2'    => ['nullable', 'string', 'max:255'],
            'longitude'   => ['required', 'string'],
            'latitude'    => ['required', 'string'],
            'zoom'        => ['nullable', 'integer'],
            'city_id'     => ['required', 'integer'],
            'country_id'  => ['required', 'integer'],
            'pin_code'    => ['nullable', 'string', 'max:255']
        ]);
    }
   
    public function validateImages($request)
    {
        return Validator::make($request, [
            'featured_image' => 'mimes:jpeg,png,jpg',
        ], [
            'featured_image' => 'Please select proper file. The file must be a file of type: jpeg, png, jpg.'
        ]);
    }
    
    public function getImageAttribute($value)
    {
        $default = '';

        if (empty($value)) {
            return $default;
        }

        $storageFolderNameRegistration = (str_ireplace("\\", "/", $this->storageFolderName));
        if (Storage::disk($this->fileSystem)->exists($storageFolderNameRegistration . $value)) {
            return Storage::disk($this->fileSystem)->url($storageFolderNameRegistration . $value);
        }

        return $default;
    }
    
    public function services()
    {
        return $this->hasMany('App\ShopService', 'shop_id', 'id');
    }
    
    public function bookings()
    {
        return $this->hasMany('App\Booking', 'shop_id', 'id');
    }

    public function bookingsImc()
    {
        return $this->hasMany('App\Booking', 'shop_id', 'id')->where('booking_type', '1');
    }

    public function bookingsHv()
    {
        return $this->hasMany('App\Booking', 'shop_id', 'id')->where('booking_type', '0');
    }

    public function receptionist()
    {
        return $this->hasOne('App\Receptionist');
    }

    public function centerHours()
    {
        return $this->hasOne('App\ShopHour', 'shop_id', 'id');
    }
    
    public function featuredImages()
    {
        return $this->hasMany('App\ShopFeaturedImage', 'shop_id', 'id');
    }
    
    public function gallery()
    {
        return $this->hasMany('App\ShopGallary', 'shop_id', 'id');
    }
    
    public function timetable()
    {
        return $this->hasMany('App\ShopHour', 'shop_id', 'id');
    }
    
    public function documents()
    {
        return $this->hasMany('App\ShopDocument', 'shop_id', 'id');
    }
    
    public function company()
    {
        return $this->hasOne('App\ShopCompany', 'id', 'shop_id');
    }
    
    public function payment()
    {
        return $this->hasOne('App\ShopPaymentDetail', 'shop_id');
    }
    
    public function apiKey()
    {
        return $this->hasMany('App\ApiKeyShop', 'shop_id', 'id');
    }

    public function therapistWorkingSchedules($isExchange = TherapistWorkingSchedule::NOT_EXCHANGE, $isWorking = TherapistWorkingSchedule::WORKING)
    {
        return $this->hasMany('App\TherapistWorkingSchedule', 'shop_id', 'id')->where('is_exchange', $isExchange)->where('is_working', $isWorking);
    }

    public function sendPasswordResetNotification($token)
    {
        $classPasswordNotification = new ResetPasswordNotification($token);

        $classPasswordNotification::$createUrlCallback = 'toMailContentsUrl';

        $this->notify($classPasswordNotification);
    }
    
    public function addBookingInfo($infoData, $newBooking, $newUser, $isPack) {

        if(isset($isPack)) {
            $pack = UserPack::where(['pack_id' => $isPack, 'users_id' => $newBooking->user_id])->first();
            if(empty($pack)) {
                return ['isError' => true, 'message' => 'Pack not found'];
            }
        }
        $therapist_id = isset($newUser) ? $newUser->therapist_id : $infoData->therapist_id;
        
        $shop = Shop::find($infoData->shop_id);
        if(empty($shop)) {
            return ['isError' => true, 'message' => 'Shop not found'];
        }
        $date = Carbon::createFromTimestampMs($infoData->booking_date_time);
        $bookingInfoData = [
            'location' => $shop->address,
            'booking_currency_id' => $shop->currency_id,
            'shop_currency_id' => $shop->currency_id,
            'booking_id' => $newBooking->id,
            'imc_type' => BookingInfo::IMC_TYPE_ASAP,
            'massage_date' => $date->format('Y-m-d'),
            'massage_time' => $date,
            'user_people_id' => isset($newUser) ? $newUser->id : NULL,
            'therapist_id' => isset($pack) ? $pack->therapist_id : $therapist_id
        ];
        $bookingInfoModel = new BookingInfo();
        $checks = $bookingInfoModel->validator($bookingInfoData);
        if ($checks->fails()) {
            return ['isError' => true, 'message' => $checks->errors()->first()];
        }
        $bookingInfo = BookingInfo::create($bookingInfoData);
        return $bookingInfo;
    }
    
    public function addBookingMassages($service, $bookingInfo, $request, $user) {
        
        $bookingMassageModel = new BookingMassage();     
        $servicePrice = ServicePricing::where(['service_timing_id' => $service['service_timing_id'], 'service_id' => $service['service_id']])->first();
        if(empty($servicePrice)) {
                return ['isError' => true, 'message' => 'Service price not found'];
        }
        
        $injuries = isset($user) ? $user['notes_of_injuries'] : $request->notes_of_injuries;
        $bookingMassageData = [
            "price" => $servicePrice->price,
            "cost" => $servicePrice->cost,
            "origional_price" => $servicePrice->price,
            "origional_cost"  => $servicePrice->cost,
            "exchange_rate" => isset($service['exchange_rate']) ? $service['exchange_rate'] : 0.00,
            "notes_of_injuries" => isset($injuries) ? $injuries : NULL,
            "service_pricing_id" => $servicePrice->id,
            "booking_info_id" => $bookingInfo->id,
            "pressure_preference" => isset($user) ? $user['pressure_preference'] : $request->pressure_preference,
            "gender_preference" => isset($user) ? $user['gender_preference'] : (!empty($request->gender_preference) ? $request->gender_preference : NULL),
            "focus_area_preference" => isset($user) ? $user['focus_area_preference'] : $request->focus_area_preference
        ];
        $checks = $bookingMassageModel->validator($bookingMassageData);
        if ($checks->fails()) {
            return ['isError' => true, 'message' => $checks->errors()->first()];
        }
        return BookingMassage::updateOrCreate(["service_pricing_id" => $servicePrice->id,"booking_info_id" => $bookingInfo->id], $bookingMassageData);
    }

    public function totalServices()
    {
        return $this->services->count();
    }

    public function getTotalServicesAttribute()
    {
        return $this->totalServices();
    }
    
    public function getMassages(Request $request) {
        
         $services = ShopService::with('service')->where('shop_id', $request->get('shop_id'))
                    ->whereHas('service', function($q) {
                            $q->where('service_type', Service::MASSAGE);
                        })->get()->groupBy('service_id');
         return $services;
    }
    
    public function getTherapies(Request $request) {
        
         $services = ShopService::with('service')->where('shop_id', $request->get('shop_id'))
                    ->whereHas('service', function($q) {
                            $q->where('service_type', Service::THERAPY);
                        })->get()->groupBy('service_id');
         return $services;
    }
    
    public function getTopItems(Request $request) {

        $serviceModel = new ShopService();

        $service = $request->service ? $request->service : Service::MASSAGE;
        $serviceModel->setMysqlStrictFalse();
        $getTopServices = $serviceModel->select(Service::getTableName() . ".id", Service::getTableName() . ".english_name", Service::getTableName() . ".portugese_name",
                BookingMassage::getTableName() . '.price', ShopService::getTableName() . ".shop_id", DB::raw('SUM(' . BookingMassage::getTableName() . '.price) As totalEarning'))
                ->join(Service::getTableName(), Service::getTableName() . '.id', '=', ShopService::getTableName() . '.service_id')
                ->leftJoin(ServicePricing::getTableName(), Service::getTableName() . '.id', '=', ServicePricing::getTableName() . '.service_id')
                ->leftJoin(BookingMassage::getTableName(), ServicePricing::getTableName() . '.id', '=', BookingMassage::getTableName() . '.service_pricing_id')
                ->whereNotNull(BookingMassage::getTableName() . '.id')
                ->where(Service::getTableName() . '.service_type', (string)$service);
        if (!empty($request->shop_id)) {
            $getTopServices->where(ShopService::getTableName() . ".shop_id", $request->shop_id);
        }
        $getTopServices = $getTopServices->groupBy(ShopService::getTableName() . '.service_id')
                ->orderBy('totalEarning', 'DESC')
                ->get();
        $serviceModel->setMysqlStrictTrue();
        return $getTopServices;
    }
    
    public function dashboardInfo(Request $request) {
        
        $shopModel = new Shop();
        $massages = $shopModel->getMassages($request)->count();
        $therapies = $shopModel->getTherapies($request)->count();
        $therapists = Therapist::where('shop_id', $request->shop_id)->get()->count();
        $clients = User::where('shop_id', $request->shop_id)->get()->count();
        
        return ['massages' => $massages, 'therapies' => $therapies, 'therapists' => $therapists,'clients' => $clients];
    }
    
    public function getTherapists(Request $request) {
        
        $therapists = Therapist::where('shop_id', $request->shop_id)->select('id','name','profile_photo','shop_id')->get();
        
        foreach ($therapists as $key => $therapist) {
            $selectedMassages = TherapistSelectedService::with('service')->where('therapist_id', $therapist->id)
                            ->whereHas('service', function($q) {
                                $q->where('service_type', Service::MASSAGE);
                            })->get()->count();
            $therapist['massages'] = $selectedMassages;
            $selectedTherapies = TherapistSelectedService::with('service')->where('therapist_id', $therapist->id)
                            ->whereHas('service', function($q) {
                                $q->where('service_type', Service::THERAPY);
                            })->get()->count();
            $therapist['therapies'] = $selectedTherapies;
            $ratings = TherapistUserRating::where(['model_id' => $therapist->id, 'model' => 'App\Therapist'])->get();

            $cnt = $rates = $avg = 0;
            if ($ratings->count() > 0) {
                foreach ($ratings as $i => $rating) {
                    $rates += $rating->rating;
                    $cnt++;
                }
                $avg = $rates / $cnt;
            }
            $therapist['average'] = number_format($avg, 2);
        }
        return $therapists;
    }
    
    public function getBookings(Request $request) {
        
        $dateFilter = !empty($request->date_filter) ? $request->date_filter : Booking::TODAY;
        $booking = DB::table('bookings')
                ->join('booking_infos', 'booking_infos.booking_id', '=', 'bookings.id')
                ->select('booking_infos.*', 'bookings.*');
        
        $now = Carbon::now();
        if ($dateFilter == Booking::TODAY) {
            $booking->where('booking_infos.massage_date', $now->format('Y-m-d'));
        }
        if ($dateFilter == Booking::YESTERDAY) {
            $booking->where('booking_infos.massage_date', $now->subDays(1)->format('Y-m-d'));
        }
        if ($dateFilter == Booking::THIS_WEEK) {
            $weekStartDate = $now->startOfWeek()->format('Y-m-d');
            $weekEndDate = $now->endOfWeek()->format('Y-m-d');

            $booking->whereBetween('booking_infos.massage_date', [$weekStartDate, $weekEndDate]);
        }
        if ($dateFilter == Booking::THIS_MONTH) {
            $booking->whereMonth('booking_infos.massage_date', $now->month);
        }
        $center = clone $booking;
        
        $homeVisit = $booking->where('bookings.booking_type' , Booking::BOOKING_TYPE_HHV)->get()->count();
        $centerVisit = $center->where('bookings.booking_type' , Booking::BOOKING_TYPE_IMC)->get()->count();
        
        return ['homeVisit' => $homeVisit, 'centerVisit' => $centerVisit];
    }
}
