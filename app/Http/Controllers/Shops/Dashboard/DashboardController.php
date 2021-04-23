<?php

namespace App\Http\Controllers\Shops\Dashboard;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use App\Therapy;
use App\Massage;
use App\Review;
use App\BookingInfo;
use Carbon\Carbon;
use App\BookingMassage;
use App\User;
use App\Booking;
use App\Voucher;
use App\Pack;

class DashboardController extends BaseController {

     public $successMsg = [
        
         'data.found' => 'Data found successfully.',
         'sales.data.found' => 'Sales data found successfully.',
         'customer.data.found' => 'Customers data found successfully.',
         
    ];
     
    public function getDetails(Request $request) {
        $massages = Massage::where('shop_id', $request->get('shop_id'))->get()->count();
        $therapies = Therapy::where('shop_id', $request->get('shop_id'))->get()->count();
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
    
        $futureCenterBookings = BookingInfo::with('booking')
                        ->whereHas('booking', function($q) use($request) {
                            $q->where('shop_id', '=', $request->get('shop_id'))
                            ->where('booking_type', Booking::BOOKING_TYPE_IMC);
                        });
        if($when == Booking::BOOKING_TODAY) {
            $futureCenterBookings = $futureCenterBookings->where('massage_date', '=', $todayDate)->get();
        }
        if($when == Booking::BOOKING_FUTURE) {
            $futureCenterBookings = $futureCenterBookings->where('massage_date', '>=', $todayDate)->get();
        }
                        
        $centerBookings = [];
        foreach ($futureCenterBookings as $index => $bookingInfo) {
            $date = Carbon::createFromTimestampMs($bookingInfo->massage_date)->format('Y-m-d');
            $centerBookings[$index] = [
                'booking_type' => Booking::BOOKING_TYPE_IMC,
                'booking_date' => strtotime($date) * 1000
            ];
        }
        $futureHomeBookings = BookingInfo::with('booking')
                        ->whereHas('booking', function($q) use($request) {
                            $q->where('shop_id', '=', $request->get('shop_id'))
                            ->where('booking_type', Booking::BOOKING_TYPE_HHV);
                        });
                        
        if($when == Booking::BOOKING_TODAY) {
            $futureHomeBookings = $futureHomeBookings->where('massage_date', '=', $todayDate)->get();
        }
        if($when == Booking::BOOKING_FUTURE) {
            $futureHomeBookings = $futureHomeBookings->where('massage_date', '>=', $todayDate)->get();
        }
                        
        $homeBookings = [];
        foreach ($futureHomeBookings as $index => $bookingInfo) {
            $date = Carbon::createFromTimestampMs($bookingInfo->massage_date)->format('Y-m-d');
            $homeBookings[$index] = [
                'booking_type' => Booking::BOOKING_TYPE_HHV,
                'booking_date' => strtotime($date) * 1000
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
    
    public function topMassages(Request $request, $massages, $todayDate, $agoDate) {
        
        $massageTimings = [];
        foreach ($massages as $index => $massage) {
            foreach ($massage->timing as $index => $timing) {
                $massageTiming = [
                    'timing_id' => $timing->id,
                    'massage_name' => $massage->name,
                    'time' => $timing->time,
                    'massage_id' => $timing->massage_id
                ];
                array_push($massageTimings, $massageTiming);
            }
        }
        
        $filter = $request->filter_type ? $request->filter_type : 0;
        $allMassages = [];
        foreach ($massageTimings as $key => $value) {
            $bookingMassages = BookingMassage::with('bookingInfo')->where('massage_timing_id', $value['timing_id'])
                            ->whereHas('bookingInfo', function($q) use($agoDate, $todayDate, $filter) {
                                if ($filter == 0) {
                                    $q->whereBetween('massage_date', array($agoDate, $todayDate));
                                } else {
                                    $q->whereMonth('massage_date', Carbon::now()->subMonth()->month);
                                }
                            })->count();
            array_push($allMassages, ['total' => $bookingMassages, 'massage_name' => $value['massage_name'], 'massage_id' => $value['massage_id']]);
        }
        $allMassages = collect($allMassages)->groupBy('massage_id');
        
        $topMassages = [];
        foreach ($allMassages as $key => $value) {
            $total = 0;
            foreach ($value as $key => $massage) {
                $total += $massage['total'];
            }
            $data = [
                'total' => $total,
                'massage_name' => $value[0]['massage_name'], 
                'massage_id' => $value[0]['massage_id']
            ];
            array_push($topMassages, $data);
        }
        
        rsort($topMassages);
        return $topMassages;
    }
    
    public function topTherapies(Request $request, $therapies, $todayDate, $agoDate) {
        
        $filter = $request->filter_type ? $request->filter_type : 0;
        $therapyTimings = [];
        foreach ($therapies as $index => $therapy) {
            foreach ($therapy->timing as $index => $timing) {
                $therapyTiming = [
                    'timing_id' => $timing->id,
                    'therapy_name' => $therapy->name,
                    'time' => $timing->time,
                    'therapy_id' => $timing->therapy_id
                ];
                array_push($therapyTimings, $therapyTiming);
            }
        }
        
        $allTherapies = [];
        foreach ($therapyTimings as $key => $value) {
            $bookingMassages = BookingMassage::with('bookingInfo')->where('therapy_timing_id', $value['timing_id'])
                            ->whereHas('bookingInfo', function($q) use($agoDate, $todayDate, $filter) {
                                if ($filter == 0) {
                                    $q->whereBetween('massage_date', array($agoDate, $todayDate));
                                } else {
                                    $q->whereMonth('massage_date', Carbon::now()->subMonth()->month);
                                }
                            })->count();
            array_push($allTherapies, ['total' => $bookingMassages, 'therapy_name' => $value['therapy_name'], 'therapy_id' => $value['therapy_id']]);
        }
        $allTherapies = collect($allTherapies)->groupBy('therapy_id');
        
        $topTherapies = [];
        foreach ($allTherapies as $key => $value) {
            $total = 0;
            foreach ($value as $key => $therapy) {
                $total += $therapy['total'];
            }
            $data = [
                'total' => $total,
                'therapy_name' => $value[0]['therapy_name'], 
                'therapy_id' => $value[0]['therapy_id']
            ];
            array_push($topTherapies, $data);
        }

        rsort($topTherapies);
        return $topTherapies;
    }

    public function salesInfo(Request $request) {

        $todayDate = Carbon::now()->format('Y-m-d');
        $agoDate = Carbon::now()->subDays(7)->format('Y-m-d');
        
        $allBookings = $this->allBookings($request);
        
        $cancelBookings = $this->cancelBookings($request);
        
        $pendingBookings = $this->pendingBookings($request);
        
        $futureBookings = $this->bookings($request, $todayDate, Booking::BOOKING_FUTURE);
        
        $todayBookings = $this->bookings($request, $todayDate, Booking::BOOKING_TODAY);
        
        $massages = Massage::with('timing')->where('shop_id', $request->get('shop_id'))->get();
        
        $therapies = Therapy::with('timing')->where('shop_id', $request->get('shop_id'))->get();

        $topMassages = $this->topMassages($request, $massages, $todayDate, $agoDate);
        
        $topTherapies = $this->topTherapies($request, $therapies, $todayDate, $agoDate);
        
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
