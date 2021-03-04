<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use App\Therapy;
use App\Massage;
use App\Review;
use App\BookingInfo;
use Carbon\Carbon;

class DashboardController extends BaseController {

    public $errorMsg = [
    ];
    public $successMsg = [
        'no.data.found' => 'No data found'
    ];

    public function getDetails(Request $request) {
        $massages = Massage::where('shop_id', $request->get('shop_id'))->get()->count();
        $therapies = Therapy::where('shop_id', $request->get('shop_id'))->get()->count();
        $reviews = Review::with('user')->where('is_delete', '0')
                        ->whereHas('user', function($q) use($request) {
                            $q->where('shop_id', '=', $request->get('shop_id'));
                        })->avg('rating');
        $reviews = isset($reviews) ? $reviews : 0;
        return ['massages' => $massages, 'therapies' => $therapies, 'reviews' => $reviews];
    }

    public function salesInfo(Request $request) {

        $allBookings = BookingInfo::with('booking')
                        ->whereHas('booking', function($q) use($request) {
                            $q->where('shop_id', '=', $request->get('shop_id'));
                        })->count();
        $cancelBookings = BookingInfo::with('booking')->where('is_cancelled', '1')
                        ->whereHas('booking', function($q) use($request) {
                            $q->where('shop_id', '=', $request->get('shop_id'));
                        })->count();
        $pendingBookings = BookingInfo::with('booking')->where('is_done', '0')
                        ->whereHas('booking', function($q) use($request) {
                            $q->where('shop_id', '=', $request->get('shop_id'));
                        })->count();
        $massages = Massage::where('shop_id', $request->get('shop_id'))->get()->count();
        $therapies = Therapy::where('shop_id', $request->get('shop_id'))->get()->count();

        $todayDate = Carbon::now()->format('Y-m-d');
        $futureBookings = BookingInfo::with('booking')->where('massage_date', '>=', $todayDate)
                        ->whereHas('booking', function($q) use($request) {
                            $q->where('shop_id', '=', $request->get('shop_id'));
                        })->count();
        $futureCenterBookings = BookingInfo::with('booking')->where('massage_date', '>=', $todayDate)
                        ->whereHas('booking', function($q) use($request) {
                            $q->where('shop_id', '=', $request->get('shop_id'))
                            ->where('booking_type', '1');
                        })->get();
        $centerBookings = [];
        foreach ($futureCenterBookings as $index => $bookingInfo) {
            $date = Carbon::createFromTimestampMs($bookingInfo->massage_date)->format('Y-m-d');
            $centerBookings[$index] = [
                'booking_type' => 1,
                'booking_date' => $date
            ];
        }
        $futureHomeBookings = BookingInfo::with('booking')->where('massage_date', '>=', $todayDate)
                        ->whereHas('booking', function($q) use($request) {
                            $q->where('shop_id', '=', $request->get('shop_id'))
                            ->where('booking_type', '2');
                        })->get();
        $homeBookings = [];
        foreach ($futureCenterBookings as $index => $bookingInfo) {
            $date = Carbon::createFromTimestampMs($bookingInfo->massage_date)->format('Y-m-d');
            $homeBookings[$index] = [
                'booking_type' => 2,
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
                            ->where('booking_type', '1');
                        })->count();
        $todayHomeBookings = BookingInfo::with('booking')->where('massage_date', '=', $todayDate)
                        ->whereHas('booking', function($q) use($request) {
                            $q->where('shop_id', '=', $request->get('shop_id'))
                            ->where('booking_type', '2');
                        })->count();

        return ['allBookings' => $allBookings, 'cancelBooking' => $cancelBookings, 'pendingBooking' => $pendingBookings,
            'totalMassages' => $massages, 'totalTherapies' => $therapies, 'futureBookings' => $upcomingBookings,
            'todayTotalBookings' => $todayBooking, 'todayCenterBooking' => $todayCenterBookings, 'todayHomeBooking' => $todayHomeBookings];
    }

}
