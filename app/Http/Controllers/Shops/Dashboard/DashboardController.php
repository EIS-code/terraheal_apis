<?php

namespace App\Http\Controllers\Shops\Dashboard;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use App\Review;
use App\BookingInfo;
use Carbon\Carbon;
use App\BookingMassage;
use App\User;
use App\Booking;
use App\Voucher;
use App\Pack;
use App\Service;
use App\ShopService;
use App\ServicePricing;
use DB;

class DashboardController extends BaseController {

     public $successMsg = [
         'data.found' => 'Sidebar data found successfully.',
         'sales.data.found' => 'Sales data found successfully.',
         'customer.data.found' => 'Customers data found successfully.',
    ];
     
    public function getDetails(Request $request) {
        
        $massages = ShopService::with('service')->where('shop_id', $request->get('shop_id'))
                    ->whereHas('service', function($q) {
                            $q->where('service_type', Service::MASSAGE);
                        })->get()->count();
        $therapies = ShopService::with('service')->where('shop_id', $request->get('shop_id'))
                    ->whereHas('service', function($q) {
                            $q->where('service_type', Service::THERAPY);
                        })->get()->count();
        $reviews = Review::with('user')->where('is_delete', Review::IS_DELETE)
                        ->whereHas('user', function($q) use($request) {
                            $q->where('shop_id', '=', $request->get('shop_id'));
                        })->avg('rating');
        $reviews = !empty($reviews) ? $reviews : 0;
        $vouchers = Voucher::where('expired_date','>=', Carbon::now()->format('Y-m-d'))->get()->count();
        $packs = Pack::where('expired_date','>=', Carbon::now()->format('Y-m-d'))->get()->count();
        return $this->returnSuccess(__($this->successMsg['data.found']), ['massages' => $massages, 'therapies' => $therapies, 'reviews' => $reviews,
            'vouchers' => $vouchers, 'packs' => $packs]);
    }
    
    public function allBookings(Request $request) {
        $booking = BookingInfo::with('booking')
                        ->whereHas('booking', function($q) use($request) {
                            $q->where('shop_id', '=', $request->get('shop_id'));
                        })->count();
        return $booking;
    }
    
    public function cancelBookings(Request $request) {
        $booking = BookingInfo::with('booking')->where('is_cancelled', BookingInfo::IS_CANCELLED)
                        ->whereHas('booking', function($q) use($request) {
                            $q->where('shop_id', '=', $request->get('shop_id'));
                        })->count();
        return $booking;
    }
    
    public function pendingBookings(Request $request) {
        $booking = BookingInfo::with('booking')->where('is_done', BookingInfo::IS_NOT_DONE)
                        ->whereHas('booking', function($q) use($request) {
                            $q->where('shop_id', '=', $request->get('shop_id'));
                        })->count();
        return $booking;
    }
    
    public function bookings(Request $request, $todayDate, $when) {
    
        $modelBooking = new Booking();
        $modelBookingMassage = new BookingMassage();
        $modelBookingInfo    = new BookingInfo();

        $futureCenterBookings = $modelBooking->select(DB::RAW($modelBooking::getTableName() . '.*, ' . $modelBookingInfo::getTableName() . '.*, ' . $modelBookingMassage::getTableName() . '.*'))                       
                ->join($modelBookingInfo::getTableName(), $modelBooking::getTableName() . '.id', '=', $modelBookingInfo::getTableName() . '.booking_id')
                ->join($modelBookingMassage::getTableName(), $modelBookingInfo::getTableName() . '.id', '=', $modelBookingMassage::getTableName() . '.booking_info_id')
                ->where($modelBooking::getTableName().'.shop_id', $request->get('shop_id'))
                ->where($modelBooking::getTableName().'.booking_type', Booking::BOOKING_TYPE_IMC);
        
        if($when == Booking::BOOKING_TODAY) {
            $futureCenterBookings = $futureCenterBookings->where($modelBookingMassage::getTableName(). '.massage_date_time', '=', $todayDate)->get();
        }
        if($when == Booking::BOOKING_FUTURE) {
            $futureCenterBookings = $futureCenterBookings->where($modelBookingMassage::getTableName(). '.massage_date_time', '>=', $todayDate)->get();
        }
        $centerBookings = [];
        foreach ($futureCenterBookings as $index => $value) {
            $centerBookings[$index] = [
                'booking_type' => Booking::BOOKING_TYPE_IMC,
                'booking_date' => strtotime($value->massage_date_time) * 1000
            ];
        }
        $futureHomeBookings = $modelBooking->select(DB::RAW($modelBooking::getTableName() . '.*, ' . $modelBookingInfo::getTableName() . '.*, ' . $modelBookingMassage::getTableName() . '.*'))                       
                ->join($modelBookingInfo::getTableName(), $modelBooking::getTableName() . '.id', '=', $modelBookingInfo::getTableName() . '.booking_id')
                ->join($modelBookingMassage::getTableName(), $modelBookingInfo::getTableName() . '.id', '=', $modelBookingMassage::getTableName() . '.booking_info_id')
                ->where($modelBooking::getTableName().'.shop_id', $request->get('shop_id'))
                ->where($modelBooking::getTableName().'.booking_type', Booking::BOOKING_TYPE_HHV);
                        
        if($when == Booking::BOOKING_TODAY) {
            $futureHomeBookings = $futureHomeBookings->where($modelBookingMassage::getTableName(). '.massage_date_time', '=', $todayDate)->get();
        }
        if($when == Booking::BOOKING_FUTURE) {
            $futureHomeBookings = $futureHomeBookings->where($modelBookingMassage::getTableName(). '.massage_date_time', '>=', $todayDate)->get();
        }
                        
        $homeBookings = [];
        foreach ($futureHomeBookings as $index => $value) {
            $homeBookings[$index] = [
                'booking_type' => Booking::BOOKING_TYPE_HHV,
                'booking_date' => strtotime($value->massage_date_time) * 1000
            ];
        }

        $upcomingBookings = [
            'total' => $futureCenterBookings->count() + $futureHomeBookings->count(),
            'totalCenterBookings' => $futureCenterBookings->count(),
            'totalHomeBookings' => $futureHomeBookings->count(),
            'centerBookings' => $centerBookings,
            'homeBookings' => $homeBookings
        ];
        
        return $upcomingBookings;
    }
    
    public function topServices(Request $request, $services, $todayDate, $agoDate) {
        
        $servicePricings = [];
        foreach ($services as $index => $service) {
            $pricings = ServicePricing::where('service_id' , $service->service_id)->get();
            foreach ($pricings as $index => $pricing) {
                $servicePricing = [
                    'pricing_id' => $pricing->id,
                    'english_name' => $service->service->english_name,
                    'portugese_name' => $service->service->portugese_name,
                    'service_id' => $service->service_id
                ];
                array_push($servicePricings, $servicePricing);
            }
        }
        $filter = $request->filter_type ? $request->filter_type : 0;
        $allServices = [];
        foreach ($servicePricings as $key => $value) {
            if($filter == 0) {
                $bookingMassages = BookingMassage::where('service_pricing_id', $value['pricing_id'])->whereBetween('massage_date_time', array($agoDate, $todayDate))->count();
            } else {
                $bookingMassages = BookingMassage::where('service_pricing_id', $value['pricing_id'])->whereMonth('massage_date_time', Carbon::now()->subMonth()->month)->count();
            }
            array_push($allServices, ['total' => $bookingMassages, 'english_name' => $value['english_name'],
                'portugese_name' => $value['portugese_name'], 'service_id' => $value['service_id']]);
        }
        $allServices = collect($allServices)->groupBy('service_id');
        $topServices = [];
        foreach ($allServices as $key => $value) {
            $total = 0;
            foreach ($value as $key => $service) {
                $total += $service['total'];
            }
            $data = [
                'total' => $total,
                'name' => $value[0]['english_name'],
                'english_name' => $value[0]['english_name'],
                'portugese_name' => $value[0]['portugese_name'],
                'service_id' => $value[0]['service_id']
            ];
            array_push($topServices, $data);
        }

        rsort($topServices);
        return $topServices;
    }

    public function salesInfo(Request $request) {

        $todayDate = Carbon::now()->format('Y-m-d');
        $agoDate = Carbon::now()->subDays(7)->format('Y-m-d');
        
        $allBookings = $this->allBookings($request);
        
        $cancelBookings = $this->cancelBookings($request);
        
        $pendingBookings = $this->pendingBookings($request);
        
        $futureBookings = $this->bookings($request, $todayDate, Booking::BOOKING_FUTURE);
        
        $todayBookings = $this->bookings($request, $todayDate, Booking::BOOKING_TODAY);
        
        $massages = ShopService::with('service')->where('shop_id', $request->get('shop_id'))
                    ->whereHas('service', function($q) {
                            $q->where('service_type', Service::MASSAGE);
                        })->get();
                        
        $therapies = ShopService::with('service')->where('shop_id', $request->get('shop_id'))
                    ->whereHas('service', function($q) {
                            $q->where('service_type', Service::THERAPY);
                        })->get();

        $topMassages = $this->topServices($request, $massages, $todayDate, $agoDate);
        
        $topTherapies = $this->topServices($request, $therapies, $todayDate, $agoDate);
        
        return $this->returnSuccess(__($this->successMsg['sales.data.found']), ['allBookings' => $allBookings, 'cancelBooking' => $cancelBookings, 'pendingBooking' => $pendingBookings,
            'totalMassages' => $massages->count(), 'totalTherapies' => $therapies->count(), 'futureBookings' => $futureBookings,
            'todayBookings' => $todayBookings,'topMassages' => $topMassages, 'topTherapies' => $topTherapies]);
    }
    
    public function customerInfo(Request $request) {
        
        $activeUsers = User::where(['is_removed' => User::$notRemoved, 'shop_id' => $request->shop_id])->count();
        $defectedUsers = User::where(['is_removed' => User::$removed, 'shop_id' => $request->shop_id])->count();
        
        return $this->returnSuccess(__($this->successMsg['customer.data.found']), ['activeUsers' => $activeUsers, 'defectedUsers' => $defectedUsers]);
    }
}
