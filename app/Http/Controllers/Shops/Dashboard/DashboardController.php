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
use App\Manager;
use App\News;
use App\BookingPayment;

class DashboardController extends BaseController {

     public $successMsg = [
         'data.found' => 'Sidebar data found successfully.',
         'sales.data.found' => 'Sales data found successfully.',
         'customer.data.found' => 'Customers data found successfully.',
    ];
     
    public function getDetails(Request $request) {
        
        $filter = isset($request->filter) ? $request->filter : Review::LAST_WEEK;
        $massages = ShopService::with('service')->where('shop_id', $request->get('shop_id'))
                    ->whereHas('service', function($q) {
                            $q->where('service_type', Service::MASSAGE);
                        })->get()->count();
        $therapies = ShopService::with('service')->where('shop_id', $request->get('shop_id'))
                    ->whereHas('service', function($q) {
                            $q->where('service_type', Service::THERAPY);
                        })->get()->count();
                        
        $weekStartDate = Carbon::now()->startOfWeek();
        $weekEndDate = Carbon::now()->endOfWeek();
        $current_week = Review::with('user')->where('is_delete', (string) Review::IS_DELETE)
                        ->whereHas('user', function($q) use($request) {
                            $q->where('shop_id', $request->get('shop_id'));
                        })->whereBetween('created_at', [$weekStartDate, $weekEndDate])->get();
        $current_week = $current_week->count() == 0 ? 0 : number_format(($current_week->count() / $current_week->sum('rating')) * 100, 2);
        
        $previous_week = strtotime("-1 week +1 day");
        $start_week = strtotime("last monday midnight",$previous_week);
        $end_week = strtotime("next sunday",$start_week);
        $start_week = Carbon::parse($start_week);
        $end_week = Carbon::parse($end_week);
        
        $reviews = Review::with('user')->where('is_delete', (string) Review::IS_DELETE)
                    ->whereHas('user', function($q) use($request) {
                    $q->where('shop_id', $request->get('shop_id'));
                });

        if($filter == Review::LAST_WEEK) {
            $reviews = $reviews->whereBetween('created_at', [$start_week, $end_week])->get();
        }
        if($filter == Review::LAST_MONTH){
            $reviews = $reviews->whereMonth('created_at', Carbon::now()->subMonth()->month)->get();
        }
        
        $reviews = $reviews->count() == 0 ? 0 : number_format(($reviews->count() / $reviews->sum('rating')) * 100, 2);
        $vouchers = Voucher::where('expired_date','>=', Carbon::now()->format('Y-m-d'))->get()->count();
        $packs = Pack::where('expired_date','>=', Carbon::now()->format('Y-m-d'))->get()->count();
        $manager = Manager::where('shop_id', $request->get('shop_id'))->first();
        $news = !empty($manager) ? News::where('manager_id', $manager->id)->count() : 0;
        
        return $this->returnSuccess(__($this->successMsg['data.found']), ['massages' => $massages, 'therapies' => $therapies, 'reviews' => $reviews,
            'vouchers' => $vouchers, 'packs' => $packs, 'current_reviews' => $current_week, 'news' => $news]);
    }
    
    public function allBookings(Request $request) {
        $booking = Booking::with('payment')->where('shop_id', '=', $request->get('shop_id'))->get();
        return $booking;
    }
    
    public function cancelBookings(Request $request) {
        
        $request->request->add(['bookings_filter' => array(Booking::BOOKING_CANCELLED)]);
        $bookingModel = new Booking();
        $booking = $bookingModel->getGlobalQuery($request);
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
        $filter = isset($request->filter) ? $request->filter : Booking::LAST_WEEK;

        $futureCenterBookings = $modelBooking->select(DB::RAW($modelBooking::getTableName() . '.*, ' . $modelBookingInfo::getTableName() . '.*, ' . $modelBookingMassage::getTableName() . '.*'))                       
                ->join($modelBookingInfo::getTableName(), $modelBooking::getTableName() . '.id', '=', $modelBookingInfo::getTableName() . '.booking_id')
                ->join($modelBookingMassage::getTableName(), $modelBookingInfo::getTableName() . '.id', '=', $modelBookingMassage::getTableName() . '.booking_info_id')
                ->where($modelBooking::getTableName().'.shop_id', $request->get('shop_id'))
                ->where($modelBooking::getTableName().'.booking_type', Booking::BOOKING_TYPE_IMC);
        
        if($when == Booking::BOOKING_TODAY) {
            $futureCenterBookings = $futureCenterBookings->where($modelBookingMassage::getTableName(). '.massage_date_time', '=', $todayDate)->get();
        }
        if($when == Booking::BOOKING_FUTURE) {
            $previous_week = strtotime("-1 week +1 day");
            $start_week = strtotime("last monday midnight",$previous_week);
            $end_week = strtotime("next sunday",$start_week);
            $start_week = Carbon::parse($start_week);
            $end_week = Carbon::parse($end_week);
            if($filter == Booking::LAST_WEEK) {
                $futureCenterBookings = $futureCenterBookings->whereBetween($modelBookingMassage::getTableName(). '.massage_date_time', [$start_week, $end_week])->get();
            }
            if($filter == Booking::LAST_MONTH) {
                $futureCenterBookings = $futureCenterBookings->whereMonth($modelBookingMassage::getTableName(). '.massage_date_time', Carbon::now()->subMonth()->month)->get();
            }
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
            $previous_week = strtotime("-1 week +1 day");
            $start_week = strtotime("last monday midnight",$previous_week);
            $end_week = strtotime("next sunday",$start_week);
            $start_week = Carbon::parse($start_week);
            $end_week = Carbon::parse($end_week);
            if($filter == Booking::LAST_WEEK) {
                $futureHomeBookings = $futureHomeBookings->whereBetween($modelBookingMassage::getTableName(). '.massage_date_time', [$start_week, $end_week])->get();
            }
            if($filter == Booking::LAST_MONTH) {
                $futureHomeBookings = $futureHomeBookings->whereMonth($modelBookingMassage::getTableName(). '.massage_date_time', Carbon::now()->subMonth()->month)->get();
            }
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
        $cancel_earnings = clone $cancelBookings;
        $cancel_earnings = $cancel_earnings->sum('price');
        
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
        
        $packs_earning = Booking::whereNotNull('pack_id')->sum('total_price');
        
        $recevied_amount = $unpaid_amount = 0;
        
        foreach ($allBookings as $key => $booking) {
            $unpaid_amount += $booking->remaining_price;
            foreach ($booking->payment as $key => $payment) {
                $recevied_amount += $payment->paid_amounts;
            }
        }
        
        return $this->returnSuccess(__($this->successMsg['sales.data.found']), ['allBookings' => $allBookings->count(), 'cancelBooking' => $cancelBookings->groupBy('booking_id')->count(), 'pendingBooking' => $pendingBookings,
            'totalMassages' => $massages->count(), 'totalTherapies' => $therapies->count(), 'futureBookings' => $futureBookings, 'todayBookings' => $todayBookings,'topMassages' => $topMassages, 
            'topTherapies' => $topTherapies, 'packEarnings' => $packs_earning, 'cancelBookingValues' => $cancel_earnings, 'paymentRecevied' => $recevied_amount, 'unpaidAmount' => $unpaid_amount]);
    }
    
    public function customerInfo(Request $request) {
        
        $activeUsers = User::where(['is_removed' => User::$notRemoved, 'shop_id' => $request->shop_id])->count();
        $defectedUsers = User::where(['is_removed' => User::$removed, 'shop_id' => $request->shop_id])->count();
        
        return $this->returnSuccess(__($this->successMsg['customer.data.found']), ['activeUsers' => $activeUsers, 'defectedUsers' => $defectedUsers]);
    }
}
