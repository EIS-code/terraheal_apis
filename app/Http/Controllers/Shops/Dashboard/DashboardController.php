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
use App\UserPack;
use App\UserVoucherPrice;

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
    
    public function getServices(Request $request, $type) {
        
        $modelBooking = new Booking();
        $modelBookingMassage = new BookingMassage();
        $modelBookingInfo = new BookingInfo();
        $serviceModel = new Service();
        $servicePriceModel = new ServicePricing();
        
        $filter_val = isset($request->filter) ? $request->filter : Booking::TODAY;
        
        $bookings = $modelBooking->select(DB::RAW($modelBooking::getTableName() . '.*, ' . $modelBookingInfo::getTableName() . '.*, ' . $modelBookingMassage::getTableName() . '.*, ' . $servicePriceModel::getTableName() . '.*, ' . $serviceModel::getTableName() . '.*'))
                ->join($modelBookingInfo::getTableName(), $modelBooking::getTableName() . '.id', '=', $modelBookingInfo::getTableName() . '.booking_id')
                ->join($modelBookingMassage::getTableName(), $modelBookingInfo::getTableName() . '.id', '=', $modelBookingMassage::getTableName() . '.booking_info_id')
                ->leftJoin($servicePriceModel::getTableName(), $modelBookingMassage::getTableName() . '.service_pricing_id', '=', $servicePriceModel::getTableName() . '.id')
                ->leftJoin($serviceModel::getTableName(), $servicePriceModel::getTableName() . '.service_id', '=', $serviceModel::getTableName() . '.id')
                ->where($modelBooking::getTableName().'.shop_id', $request->get('shop_id'));
        
        if($type == Booking::MASSAGES) {
            $bookings->where($serviceModel::getTableName() . '.service_type', Booking::MASSAGES)
                    ->whereNull($modelBooking::getTableName() . '.pack_id')
                    ->whereNull($modelBooking::getTableName() . '.voucher_id');
        }
        if($type == Booking::MASSAGES) {
            $bookings->where($serviceModel::getTableName() . '.service_type', Booking::THERAPIES)
                    ->whereNull($modelBooking::getTableName() . '.pack_id')
                    ->whereNull($modelBooking::getTableName() . '.voucher_id');
        }
        if($type == Booking::PACKS) {
            $bookings->whereNotNull($modelBooking::getTableName() . '.pack_id')
                    ->whereNull($modelBooking::getTableName() . '.voucher_id');
        }
        if($type == Booking::VOUCHERS) {
            $bookings->whereNull($modelBooking::getTableName() . '.pack_id')
                    ->whereNotNull($modelBooking::getTableName() . '.voucher_id');
        }
        
        if($filter_val == Booking::TODAY) {
            $date = Carbon::now()->format('Y-m-d');
            $bookings->whereDate($modelBookingMassage::getTableName() . '.massage_date_time', $date);
        }
        if($filter_val == Booking::YESTERDAY) {
            $date = Carbon::yesterday()->format('Y-m-d');
            $bookings->whereDate($modelBookingMassage::getTableName() . '.massage_date_time', $date);
        }
        if($filter_val == Booking::THIS_MONTH) {
            $date = Carbon::now();
            $bookings->whereMonth($modelBookingMassage::getTableName() . '.massage_date_time', $date->month)
                    ->whereYear($modelBookingMassage::getTableName() . '.massage_date_time', $date->year);
        }
        if($filter_val == Booking::LAST_7_DAYS) {
            
            $now = Carbon::now();
            $todayDate = $now->format('Y-m-d');
            $agoDate = $now->subDays(7)->format('Y-m-d');
            
            $bookings->whereDate($modelBookingMassage::getTableName() . '.massage_date_time', '<=' , $todayDate)
                    ->whereDate($modelBookingMassage::getTableName() . '.massage_date_time', '>=' , $agoDate);
        }
        if($filter_val == Booking::LAST_14_DAYS) {
            
            $now = Carbon::now();
            $todayDate = $now->format('Y-m-d');
            $agoDate = $now->subDays(14)->format('Y-m-d');
            
            $bookings->whereDate($modelBookingMassage::getTableName() . '.massage_date_time', '<=' , $todayDate)
                    ->whereDate($modelBookingMassage::getTableName() . '.massage_date_time', '>=' , $agoDate);
        }
        if($filter_val == Booking::LAST_30_DAYS) {
            
            $now = Carbon::now();
            $todayDate = $now->format('Y-m-d');
            $agoDate = $now->subDays(30)->format('Y-m-d');
            
            $bookings->whereDate($modelBookingMassage::getTableName() . '.massage_date_time', '<=' , $todayDate)
                    ->whereDate($modelBookingMassage::getTableName() . '.massage_date_time', '>=' , $agoDate);
        }
        if($filter_val == Booking::CUSTOM) {
            
            $date = $date = Carbon::createFromTimestampMs($request->date);
            $bookings->whereDate($modelBookingMassage::getTableName() . '.massage_date_time', $date);
        }
        $bookings = $bookings->get();
        
//        dd($bookings);
        $earnings = 0;
        foreach ($bookings as $key => $value) {
            $earnings += $value->price;
        }
        
        return $earnings;
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
        
        $massage_earnings = $this->getServices($request, Booking::MASSAGES);
        $therapies_earnings = $this->getServices($request, Booking::THERAPIES);
        $voucher_earnings = $this->getServices($request, Booking::VOUCHERS);
        $pack_earnings = $this->getServices($request, Booking::PACKS);
        $voucher_pack_earnings = $voucher_earnings + $pack_earnings;
        $total = $massage_earnings + $therapies_earnings + $voucher_pack_earnings;
        
        $services = [
            'total' => $total,
            'massage' => $total > 0 ? round(($massage_earnings * 100) / $total): 0,
            'therapies' => $total > 0 ? round(($therapies_earnings * 100) / $total) : 0,
            'voucher_packs' => $total > 0 ? round(($voucher_pack_earnings * 100) / $total) : 0
        ];
        
        return $this->returnSuccess(__($this->successMsg['sales.data.found']), ['allBookings' => $allBookings->count(), 'cancelBooking' => $cancelBookings->groupBy('booking_id')->count(), 'pendingBooking' => $pendingBookings,
            'totalMassages' => $massages->count(), 'totalTherapies' => $therapies->count(), 'futureBookings' => $futureBookings, 'todayBookings' => $todayBookings,'topMassages' => $topMassages, 
            'topTherapies' => $topTherapies, 'packEarnings' => $packs_earning, 'cancelBookingValues' => $cancel_earnings, 'paymentRecevied' => $recevied_amount, 'unpaidAmount' => $unpaid_amount, 'services' => $services]);
    }
    
    public function getPacks(Request $request, $type) {

        $filter = $request->filter ? $request->filter : UserPack::TODAY;
        
        $packs = UserPack::with('pack', 'user')->whereHas('user', function($q) use($request) {
                    $q->where('shop_id', $request->shop_id);
                })->where('purchase_platform', $type);
                
        $now = Carbon::now();
        
        if ($filter == UserPack::TODAY) {
            $packs->whereDate('purchase_date', $now->format('Y-m-d'));
        }
        if ($filter == UserPack::YESTERDAY) {
            $packs->whereDate('purchase_date', $now->subDays(1));
        }
        if ($filter == UserPack::THIS_WEEK) {
            $weekStartDate = $now->startOfWeek()->format('Y-m-d');
            $weekEndDate = $now->endOfWeek()->format('Y-m-d');
            $packs->whereDate('purchase_date', '>=', $weekStartDate)->whereDate('purchase_date', '<=', $weekEndDate);
        }
        if ($filter == UserPack::CURRENT_MONTH) {
            $packs->whereMonth('purchase_date', $now->month);
        }
        if ($filter == UserPack::LAST_7_DAYS) {
            $todayDate = $now->format('Y-m-d');
            $agoDate = $now->subDays(7)->format('Y-m-d');           
            $packs->whereDate('purchase_date', '>=', $agoDate)->whereDate('purchase_date', '<=', $todayDate);
        }
        if ($filter == UserPack::LAST_14_DAYS) {
            $todayDate = $now->format('Y-m-d');
            $agoDate = $now->subDays(14)->format('Y-m-d');
            $packs->whereDate('purchase_date', '>=', $agoDate)->whereDate('purchase_date', '<=', $todayDate);
        }
        if ($filter == UserPack::LAST_30_DAYS) {
            $todayDate = $now->format('Y-m-d');
            $agoDate = $now->subDays(30)->format('Y-m-d');
            $packs->whereDate('purchase_date', '>=', $agoDate)->whereDate('purchase_date', '<=', $todayDate);
        }
        if ($filter == UserPack::CUSTOM) {
            $date = Carbon::createFromTimestampMs($request->date);
            $packs->whereDate('purchase_date', $date);
        }

        $packs = $packs->get();
        $earnings = 0;
        foreach ($packs as $key => $value) {
            $earnings += $value->pack->pack_price;
        }
        return $earnings;
    }

    public function customerInfo(Request $request) {
        
        $app_packs = $this->getPacks($request, UserPack::APP);
        $web_packs = $this->getPacks($request, UserPack::WEB);
        $center_packs = $this->getPacks($request, UserPack::CENTER);
        $total_pack_earnings = $app_packs + $web_packs + $center_packs;
        
        $app_pack_earnings = $total_pack_earnings > 0 ? ($app_packs * 100) / $total_pack_earnings : 0;
        $web_pack_earnings = $total_pack_earnings > 0 ? ($web_packs * 100) / $total_pack_earnings : 0;
        $center_pack_earnings = $total_pack_earnings > 0 ? ($center_packs * 100) / $total_pack_earnings : 0;
        
        $total_pack_sold = UserPack::with('user')->whereHas('user', function($q) use($request) {
                    $q->where('shop_id', $request->shop_id);
                })->get()->groupBy('pack_id')->count();
                
        $packData = [
            'total_pack_sold' => $total_pack_sold,
            'sold_through_app' => $app_packs,
            'sold_through_web' => $web_packs,
            'sold_through_center' => $center_packs,
            'app_pack_earnings' => $app_pack_earnings.'%',
            'web_pack_earnings' => $web_pack_earnings.'%',
            'center_pack_earnings' => $center_pack_earnings.'%',
        ];
        
        $total_sold_vouchers = UserVoucherPrice::with('user')->whereHas('user', function($q) use($request) {
                    $q->where('shop_id', $request->shop_id);
                })->get()->groupBy('voucher_id')->count();
                
        $total_sold = UserVoucherPrice::with('user')->whereHas('user', function($q) use($request) {
                    $q->where('shop_id', $request->shop_id);
                })->get()->sum('total_value');
                
        $total_used = UserVoucherPrice::with('user')->whereHas('user', function($q) use($request) {
                    $q->where('shop_id', $request->shop_id);
                })->get()->sum('used_value');
                
        $total_unused = UserVoucherPrice::with('user')->whereHas('user', function($q) use($request) {
                    $q->where('shop_id', $request->shop_id);
                })->get()->sum('available_value');
                
        $voucherData = [
            'total_sold_vouchers' => $total_sold_vouchers,
            'total_sold' => $total_sold,
            'total_used' => $total_used,
            'total_unused' => $total_unused
        ];

        $activeUsers = User::where(['is_removed' => User::$notRemoved, 'shop_id' => $request->shop_id])->count();
        $defectedUsers = User::where(['is_removed' => User::$removed, 'shop_id' => $request->shop_id])->count();
        
        return $this->returnSuccess(__($this->successMsg['customer.data.found']), ['activeUsers' => $activeUsers, 'defectedUsers' => $defectedUsers, 
            'packData' => $packData, 'voucherData' => $voucherData]);
    }
}
