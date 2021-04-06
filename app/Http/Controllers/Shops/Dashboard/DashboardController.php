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
        $reviews = isset($reviews) ? $reviews : 0;
        $vouchers = Voucher::where('expired_date','>=', Carbon::now()->format('Y-m-d'))->get()->count();
        return $this->returnSuccess(__($this->successMsg['data.found']), ['massages' => $massages, 'therapies' => $therapies, 'reviews' => $reviews,
            'vouchers' => $vouchers]);
    }

    public function salesInfo(Request $request) {

        $allBookings = BookingInfo::with('booking')
                        ->whereHas('booking', function($q) use($request) {
                            $q->where('shop_id', '=', $request->get('shop_id'));
                        })->count();
        $cancelBookings = BookingInfo::with('booking')->where('is_cancelled', BookingInfo::IS_CANCELLED)
                        ->whereHas('booking', function($q) use($request) {
                            $q->where('shop_id', '=', $request->get('shop_id'));
                        })->count();
        $pendingBookings = BookingInfo::with('booking')->where('is_done', BookingInfo::IS_NOT_DONE)
                        ->whereHas('booking', function($q) use($request) {
                            $q->where('shop_id', '=', $request->get('shop_id'));
                        })->count();
        $massages = Massage::with('timing')->where('shop_id', $request->get('shop_id'))->get();
        $therapies = Therapy::where('shop_id', $request->get('shop_id'))->get();

        $todayDate = Carbon::now()->format('Y-m-d');
        $agoDate = Carbon::now()->subDays(7)->format('Y-m-d');
        
        $futureCenterBookings = BookingInfo::with('booking')->where('massage_date', '>=', $todayDate)
                        ->whereHas('booking', function($q) use($request) {
                            $q->where('shop_id', '=', $request->get('shop_id'))
                            ->where('booking_type', Booking::BOOKING_TYPE_IMC);
                        })->get();
        $centerBookings = [];
        foreach ($futureCenterBookings as $index => $bookingInfo) {
            $date = Carbon::createFromTimestampMs($bookingInfo->massage_date)->format('Y-m-d');
            $centerBookings[$index] = [
                'booking_type' => Booking::BOOKING_TYPE_IMC,
                'booking_date' => $date
            ];
        }
        $futureHomeBookings = BookingInfo::with('booking')->where('massage_date', '>=', $todayDate)
                        ->whereHas('booking', function($q) use($request) {
                            $q->where('shop_id', '=', $request->get('shop_id'))
                            ->where('booking_type', Booking::BOOKING_TYPE_HHV);
                        })->get();
        $homeBookings = [];
        foreach ($futureCenterBookings as $index => $bookingInfo) {
            $date = Carbon::createFromTimestampMs($bookingInfo->massage_date)->format('Y-m-d');
            $homeBookings[$index] = [
                'booking_type' => Booking::BOOKING_TYPE_HHV,
                'booking_date' => $date
            ];
        }

        $upcomingBookings = [
            'totalCenterBookings' => $futureCenterBookings->count(),
            'totalHomeBookings' => $futureHomeBookings->count(),
            'centerBookings' => $centerBookings,
            'homeBookings' => $homeBookings
        ];


        $todayBooking = BookingInfo::with('booking')->where('massage_date', '=', $todayDate)
                        ->whereHas('booking', function($q) use($request) {
                            $q->where('shop_id', '=', $request->get('shop_id'));
                        })->count();
        $todayCenterBookings = BookingInfo::with('booking')->where('massage_date', '=', $todayDate)
                        ->whereHas('booking', function($q) use($request) {
                            $q->where('shop_id', '=', $request->get('shop_id'))
                            ->where('booking_type', Booking::BOOKING_TYPE_IMC);
                        })->count();
        $todayHomeBookings = BookingInfo::with('booking')->where('massage_date', '=', $todayDate)
                        ->whereHas('booking', function($q) use($request) {
                            $q->where('shop_id', '=', $request->get('shop_id'))
                            ->where('booking_type', Booking::BOOKING_TYPE_HHV);
                        })->count();
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
        
        $allMassages = [];
        foreach ($massageTimings as $key => $value) {
            $bookingMassages = BookingMassage::with('bookingInfo')->where('massage_timing_id', $value['timing_id'])
                            ->whereHas('bookingInfo', function($q) use($agoDate, $todayDate, $request) {
                                if ($request->filter_type == 1) {
                                    $q->whereBetween('massage_date', array($agoDate, $todayDate));
                                } else {
                                    $q->whereMonth('massage_date', Carbon::now()->subMonth()->month);
                                }
                            })->count();
            array_push($allMassages, ['total' => $bookingMassages, 'massage_name' => $value['massage_name'], 'massage_id' => $value['massage_id']]);
        }
        $topMassages = [];
        foreach ($allMassages as $key => $value) {

            if(isset($topMassages[$key-1]['massage_id']) == $value['massage_id']){
                $topMassages[$key-1]['total'] += $value['total']; 
            } else {
                array_push($topMassages, $value);
            }
        }
        $topTherapies = [];
        foreach ($therapies as $key => $value) {
            $bookingTherapies = BookingMassage::with('bookingInfo')->where('therapy_id', $value['id'])
                     ->whereHas('bookingInfo', function($q) use($agoDate, $todayDate) {
                            $q->whereBetween('massage_date',array($agoDate,$todayDate));
                        })->count();
            array_push($topTherapies, ['total' => $bookingTherapies, 'therapy_name' =>  ['name']]);
        }
        
        return $this->returnSuccess(__($this->successMsg['sales.data.found']), ['allBookings' => $allBookings, 'cancelBooking' => $cancelBookings, 'pendingBooking' => $pendingBookings,
            'totalMassages' => $massages->count(), 'totalTherapies' => $therapies->count(), 'futureBookings' => $upcomingBookings,
            'todayTotalBookings' => $todayBooking, 'todayCenterBooking' => $todayCenterBookings, 'todayHomeBooking' => $todayHomeBookings,
            'topMassages' => $topMassages, 'topTherapies' => $topTherapies]);
    }
    
    public function customerInfo(Request $request) {
        
        $activeUsers = User::where(['is_removed' => User::$notRemoved, 'shop_id' => $request->shop_id])->count();
        $defectedUsers = User::where(['is_removed' => User::$removed, 'shop_id' => $request->shop_id])->count();
        
        return $this->returnSuccess(__($this->successMsg['customer.data.found']), ['activeUsers' => $activeUsers, 'defectedUsers' => $defectedUsers]);
    }
}
