<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Auth\Passwords\CanResetPassword;
use App\BookingInfo;
use App\UserPack;
use Illuminate\Http\Request;
use DB;
use Carbon\Carbon;

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
   
    public function massages()
    {
        return $this->hasMany('App\Massage', 'shop_id', 'id');
    }

    public function therapies()
    {
        return $this->hasMany('App\Therapy', 'shop_id', 'id');
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
    
    public function addBookingMassages($service, $bookingInfo, $request, $user, $isMassage) {
        
        $bookingMassageModel = new BookingMassage();     
        if($isMassage)
        {
            $servicePrice = MassagePrice::where('massage_timing_id',$service['massage_timing_id'])->first();
        } else {
            $servicePrice = TherapiesPrices::where('therapy_timing_id',$service['therapy_timing_id'])->first();
        }
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
            "massage_timing_id" => $isMassage ? $service['massage_timing_id'] : NULL,
            "massage_prices_id" => $isMassage ? $servicePrice->id  : NULL,
            "therapy_timing_id" => $isMassage ? NULL : $service['therapy_timing_id'],
            "therapy_prices_id" => $isMassage ? NULL : $servicePrice->id,
            "booking_info_id" => $bookingInfo->id,
            "pressure_preference" => isset($user) ? $user['pressure_preference'] : $request->pressure_preference,
            "gender_preference" => isset($user) ? $user['gender_preference'] : (!empty($request->gender_preference) ? $request->gender_preference : NULL),
            "focus_area_preference" => isset($user) ? $user['focus_area_preference'] : $request->focus_area_preference
        ];
        $checks = $bookingMassageModel->validator($bookingMassageData);
        if ($checks->fails()) {
            return ['isError' => true, 'message' => $checks->errors()->first()];
        }
        return BookingMassage::create($bookingMassageData);
    }

    public function totalServices()
    {
        $totalMassages  = $this->massages->count();

        $totalTherapies = $this->therapies->count();

        return $totalMassages + $totalTherapies;
    }

    public function getTotalServicesAttribute()
    {
        return $this->totalServices();
    }
    
    public function getMassages(Request $request) {
        
         $services = Massage::with('timing', 'pricing')->select('id', 'name', 'image', 'icon', 'shop_id')
                        ->where('shop_id', $request->center_id)->get();
         return $services;
    }
    
    public function getTherapies(Request $request) {
        
         $services = Therapy::with('timing', 'pricing')->where('shop_id', $request->center_id)->get();
         return $services;
    }
    
    public function getTopItems(Request $request) {

        $massageModel = new Massage();
        $therapyModel = new Therapy();

        $service = $request->service ? $request->service : Booking::MASSAGES;
        if ($service == Booking::MASSAGES) {
            $massageModel->setMysqlStrictFalse();
            $getTopMassages = $massageModel->select(Massage::getTableName() . ".id", Massage::getTableName() . ".name", Massage::getTableName() . ".icon", BookingMassage::getTableName() . '.price', Massage::getTableName() . ".shop_id", DB::raw('SUM(' . BookingMassage::getTableName() . '.price) As totalEarning'))
                    ->leftJoin(MassagePrice::getTableName(), Massage::getTableName() . '.id', '=', MassagePrice::getTableName() . '.massage_id')
                    ->leftJoin(BookingMassage::getTableName(), MassagePrice::getTableName() . '.id', '=', BookingMassage::getTableName() . '.massage_prices_id')
                    ->whereNotNull(BookingMassage::getTableName() . '.id');
            if (!empty($request->center_id)) {
                $getTopMassages->where(Massage::getTableName() . ".shop_id", $request->center_id);
            }
            $getTopMassages = $getTopMassages->groupBy(Massage::getTableName() . '.id')
                    ->orderBy('totalEarning', 'DESC')
                    ->get();
            $massageModel->setMysqlStrictTrue();
            return $getTopMassages;
        }
        if ($service == Booking::THERAPIES) {
            $therapyModel->setMysqlStrictFalse();
            $getTopTherapies = $therapyModel->select(Therapy::getTableName() . ".id", Therapy::getTableName() . ".name", Therapy::getTableName() . ".image", BookingMassage::getTableName() . '.price', Therapy::getTableName() . ".shop_id", DB::raw('SUM(' . BookingMassage::getTableName() . '.price) As totalEarning'))
                    ->leftJoin(TherapiesPrices::getTableName(), Therapy::getTableName() . '.id', '=', TherapiesPrices::getTableName() . '.therapy_id')
                    ->leftJoin(BookingMassage::getTableName(), TherapiesPrices::getTableName() . '.id', '=', BookingMassage::getTableName() . '.therapy_timing_id')
                    ->whereNotNull(BookingMassage::getTableName() . '.id');
            if (!empty($request->center_id)) {
                $getTopTherapies->where(Therapy::getTableName() . ".shop_id", $request->center_id);
            }
            $getTopTherapies = $getTopTherapies->groupBy(Therapy::getTableName() . '.id')
                    ->orderBy('totalEarning', 'DESC')
                    ->get();
            $therapyModel->setMysqlStrictTrue();
            return $getTopTherapies;
        }
    }
}
