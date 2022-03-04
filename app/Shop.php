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
        'tel_number_code',
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
    const SHOP = 'Shop';
    
    public function validator(array $data, $id = false)
    {
        if (!empty($id)) {
            $emailValidator      = ['unique:shops,email,' . $id];
            $numberValidator     = ['unique:shops,tel_number,' . $id];
        } else {
            $emailValidator      = ['unique:shops'];
            $numberValidator     = ['unique:shops'];
        }

        return Validator::make($data, [
            'name'                => ['nullable', 'string', 'max:255'],
            'address'             => ['nullable', 'string', 'max:255'],
            'address2'            => ['nullable', 'string', 'max:255'],
            'description'         => ['nullable', 'string'],
            'longitude'           => ['nullable', 'between:0,99.99'],
            'latitude'            => ['nullable', 'between:0,99.99'],
            'zoom'                => ['nullable', 'integer'],
            'tel_number'          => array_merge(['nullable', 'string', 'max:50'], $numberValidator),
            'tel_number_code'     => ['nullable', 'string', 'max:20'],
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
        return $this->hasMany('App\ShopHour', 'shop_id', 'id');
    }
    
    public function featuredImages()
    {
        return $this->hasMany('App\ShopFeaturedImage', 'shop_id', 'id');
    }
    
    public function featuredImage()
    {
        return $this->hasOne('App\ShopFeaturedImage', 'shop_id', 'id');
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
        $therapist_id = isset($newUser['therapist_id']) ? $newUser['therapist_id'] : (isset($infoData->therapist_id) ? $infoData->therapist_id : NULL);
        $user_id = isset($newUser['user_id']) ? $newUser['user_id'] : (isset($infoData->user_id) ? $infoData->user_id : NULL);
        
        $shop = Shop::find($infoData->shop_id);
        if(empty($shop)) {
            return ['isError' => true, 'message' => 'Shop not found'];
        }
        $bookingInfoData = [
            'location' => $shop->address,
            'booking_currency_id' => $shop->currency_id,
            'shop_currency_id' => $shop->currency_id,
            'booking_id' => $newBooking->id,
            'imc_type' => BookingInfo::IMC_TYPE_ASAP,   
            'user_id' => $user_id,
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
        $servicePrice = ServicePricing::where(['service_timing_id' => $service['service_timing_id']])->first();
        if(empty($servicePrice)) {
                return ['isError' => true, 'message' => 'Service price not found'];
        }
        
        $injuries = !empty($user['notes_of_injuries']) ? $user['notes_of_injuries'] : (!empty($request->notes_of_injuries) ? $request->notes_of_injuries : NULL);

        $date = !empty($user['booking_date_time']) ? Carbon::createFromTimestampMs($user['booking_date_time']) : NULL;

        $bookingMassageData = [
            "massage_date_time" => $date,
            "price" => $servicePrice->price,
            "cost" => $servicePrice->cost,
            "origional_price" => $servicePrice->price,
            "origional_cost"  => $servicePrice->cost,
            "exchange_rate" => isset($service['exchange_rate']) ? $service['exchange_rate'] : 0.00,
            "notes_of_injuries" => $injuries,
            "service_pricing_id" => $servicePrice->id,
            "booking_info_id" => $bookingInfo->id,
            "pressure_preference" => $user['pressure_preference'],
            "gender_preference" => !empty($user['gender_preference']) ? $user['gender_preference'] : (!empty($request->gender_preference) ? $request->gender_preference : NULL),
            "focus_area_preference" => !empty($user['focus_area_preference']) ? $user['focus_area_preference'] : NULL,
            "language_id" => !empty($request->language_id) ? $request->language_id : NULL,
        ];

        $checks = $bookingMassageModel->validator($bookingMassageData);

        if ($checks->fails()) {
            return ['isError' => true, 'message' => $checks->errors()->first()];
        }

        $is_done = BookingMassage::updateOrCreate(["service_pricing_id" => $servicePrice->id,"booking_info_id" => $bookingInfo->id], $bookingMassageData);
        return ['is_done' => $is_done, 'price' => $servicePrice->price];
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

        // Get top services (Services).
        $getTopServices = Service::selectRaw(Service::getTableName() . ".*, " . "SUM(" . BookingMassage::getTableName() . ".price)" . " AS sum_price")
                            ->with('imageFeatured')
                            ->join(ShopService::getTableName(), Service::getTableName() . '.id', '=', ShopService::getTableName() . '.service_id')
                            ->leftJoin(ServicePricing::getTableName(), ShopService::getTableName() . '.service_id', '=', ServicePricing::getTableName() . '.service_id')
                            ->leftJoin(BookingMassage::getTableName(), ServicePricing::getTableName() . '.id', '=', BookingMassage::getTableName() . '.service_pricing_id')
                            ->whereNotNull(BookingMassage::getTableName() . '.id');

        if (!empty($request->shop_id)) {
            $getTopServices->where(ShopService::getTableName() . ".shop_id", $request->shop_id);
        }

        $getTopServices = $getTopServices->groupBy(Service::getTableName() . '.id')->orderBy(DB::RAW('SUM(' . BookingMassage::getTableName() . '.price)'), 'DESC')->get();

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
                ->join('booking_massages', 'booking_massages.booking_info_id', '=', 'booking_infos.id')
                ->select('booking_infos.*', 'bookings.*');
        
        $now = Carbon::now();
        if ($dateFilter == Booking::TODAY) {
            $booking->where('booking_massages.massage_date_time', $now->format('Y-m-d'));
        }
        if ($dateFilter == Booking::YESTERDAY) {
            $booking->where('booking_massages.massage_date_time', $now->subDays(1)->format('Y-m-d'));
        }
        if ($dateFilter == Booking::THIS_WEEK) {
            $weekStartDate = $now->startOfWeek()->format('Y-m-d');
            $weekEndDate = $now->endOfWeek()->format('Y-m-d');

            $booking->whereBetween('booking_massages.massage_date_time', [$weekStartDate, $weekEndDate]);
        }
        if ($dateFilter == Booking::THIS_MONTH) {
            $booking->whereMonth('booking_massages.massage_date_time', $now->month);
        }
        $center = clone $booking;
        
        $homeVisit = $booking->where('bookings.booking_type' , Booking::BOOKING_TYPE_HHV)->get()->groupBy('bookings.id')->count();
        $centerVisit = $center->where('bookings.booking_type' , Booking::BOOKING_TYPE_IMC)->get()->groupBy('bookings.id')->count();
        
        return ['homeVisit' => $homeVisit, 'centerVisit' => $centerVisit];
    }
    
    public function printBooking($request) {
        
        $bookingModel = new Booking();
        $printDetails = $bookingModel->getGlobalQuery($request);
        $firstBooking  = clone $printDetails;
        $booking = $firstBooking->first();
        
        if(empty($booking)) {
            return ['isError' => true, 'message' => 'Booking not found'];
        }
        
        $services[]= [
            "name" => $booking['client_name'],
            "service_name" => $booking['service_name'],
            "massage_duration" => $booking['massage_duration'],
            "cost" => $booking['cost'],
        ];
            
        $sum = 0;
        foreach ($printDetails as $key => $printDetail) {
         
            $services[] = [
               "name" => $printDetail['user_people_name'],
               "service_name" => $printDetail['service_name'],
               "massage_duration" => $printDetail['massage_duration'],
               "cost" => $printDetail['cost'],
            ];
            $sum += $printDetail['cost'];
        }
        $bookingDetails = [
            "booking_id" => $booking['booking_id'],
            "book_platform" => $booking['book_platform'],
            "notes" => $booking['notes'],
            "date_time" => $booking['created_at'],
            "booking_type" => $booking['booking_type'],
            "session_type" => $booking['session_type'],
            "shop_id" => $booking['shop_id'],
            "shop_name" => $booking['shop_name'],
            "shop_address" => $booking['shop_address'],
            "booking_services" => $services,
            "total" => $sum,

        ];
        
        return $bookingDetails;
    }
}
