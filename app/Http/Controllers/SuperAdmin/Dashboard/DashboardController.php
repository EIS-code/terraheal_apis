<?php

namespace App\Http\Controllers\SuperAdmin\Dashboard;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use App\Therapy;
use App\Massage;
use App\Therapist;
use App\Shop;
use App\User;
use App\Booking;
use App\BookingInfo;
use DB;

class DashboardController extends BaseController {

    public $successMsg = [
        'dashboard.data.found' => 'Dashboard details found successfully.',
        'sidebar.data.found' => 'Sidebar details found successfully.',
        'centers' => 'All centers found successfully.',
        'center.details' => 'Center details found successfully.',
    ];

    public function getDetails() {

        $massages = Massage::all()->count();
        $therapies = Therapy::all()->count();
        $shops = Shop::all()->count();
        $therapists = Therapist::all()->count();
        $clients = User::all()->count();

        return $this->returnSuccess(__($this->successMsg['dashboard.data.found']), ['massages' => $massages, 'therapies' => $therapies, 'shops' => $shops,
                    'therapists' => $therapists, 'clients' => $clients]);
    }

    public function getSidebarDetails(Request $request) {

        $shopModel = new Shop();
        
        $appUsers = User::all()->count();
        $homeBooking = Booking::where('booking_type', Booking::BOOKING_TYPE_HHV)->get()->count();
        $totalSales = DB::table('booking_massages')
                ->join('booking_infos', 'booking_infos.id', '=', 'booking_massages.booking_info_id')
                ->join('bookings', 'bookings.id', '=', 'booking_infos.booking_id')
                ->select('booking_massages.*', 'booking_infos.*', 'booking_infos.*')
                ->where('booking_infos.is_done', (string) BookingInfo::IS_DONE)
                ->sum('booking_massages.price');
        $totalCost = DB::table('booking_massages')
                ->join('booking_infos', 'booking_infos.id', '=', 'booking_massages.booking_info_id')
                ->join('bookings', 'bookings.id', '=', 'booking_infos.booking_id')
                ->select('booking_massages.*', 'booking_infos.*', 'booking_infos.*')
                ->where('booking_infos.is_done', (string) BookingInfo::IS_DONE)
                ->sum('booking_massages.cost');
        $totalEarning = number_format(($totalSales - $totalCost) / ($totalCost * 100), 2);
        $topItems = $shopModel->getTopItems($request);

        return $this->returnSuccess(__($this->successMsg['sidebar.data.found']), ['appUsers' => $appUsers, 'homeBooking' => $homeBooking,
                    'totalSales' => $totalSales, 'totalEarning' => $totalEarning, 'topItems' => $topItems]);
    }

    public function getCenters() {

        $centers = Shop::with('timetable')->withCount('massages', 'therapies')->get();
        return $this->returnSuccess(__($this->successMsg['centers']), $centers);
    }

}
