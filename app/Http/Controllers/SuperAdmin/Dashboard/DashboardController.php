<?php

namespace App\Http\Controllers\SuperAdmin\Dashboard;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use App\Therapist;
use App\Shop;
use App\User;
use App\Booking;
use App\BookingInfo;
use App\BookingMassage;
use App\Service;
use App\ShopService;
use App\ServicePricing;
use App\ServiceImage;
use DB;

class DashboardController extends BaseController {

    public $successMsg = [
        'dashboard.data.found' => 'Dashboard details found successfully.',
        'sidebar.data.found' => 'Sidebar details found successfully.',
        'centers' => 'All centers found successfully.',
        'center.details' => 'Center details found successfully.',
    ];

    public function getDetails() {

        $massages = Service::where('service_type', Service::MASSAGE)->get()->count();
        $therapies = Service::where('service_type', Service::THERAPY)->get()->count();
        $shops = Shop::all()->count();
        $therapists = Therapist::all()->count();
        $clients = User::all()->count();

        // Get client satisfactions.
        $clientStatisfaction         = User::getStatisfactions();
        $clientStatisfactionLastWeek = User::getStatisfactions(true);

        // Get earnings.
        $earnings = Shop::getEarnings();

        return $this->returnSuccess(
                __($this->successMsg['dashboard.data.found']),
                [
                    'massages' => $massages,
                    'therapies' => $therapies,
                    'shops' => $shops,
                    'therapists' => $therapists,
                    'clients' => $clients,
                    'client_statisfaction' => $clientStatisfaction,
                    'client_statisfaction_last_week' => $clientStatisfactionLastWeek,
                    'earnings' => $earnings
                ]
            );
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

    public function getCenters(Request $request) {
        $search  = $request->get('search', null);

        $centers = Shop::with('timetable');

        if (!empty($search)) {
            $centers->where(function($query) use($search) {
                $query->where('name', 'LIKE', '%' . $search . '%')
                      ->orWhere('owner_name', 'LIKE', '%' . $search . '%')
                      ->orWhere('owner_mobile_number', 'LIKE', '%' . $search . '%')
                      ->orWhere('owner_email', 'LIKE', '%' . $search . '%');
            });
        }

        $centers = $centers->get();

        foreach ($centers as $key => $center) {
            $massages = ShopService::with('service')->where('shop_id', $center->id)
                                    ->whereHas('service', function($q) {
                                        $q->where('service_type', Service::MASSAGE);
                                    })->count();

            $center->total_massages = $massages;

            $therapies = ShopService::with('service')->where('shop_id', $center->id)
                                    ->whereHas('service', function($q) {
                                        $q->where('service_type', Service::THERAPY);
                                    })->count();

            $center->total_therapies = $therapies;
        }

        return $this->returnSuccess(__($this->successMsg['centers']), $centers);
    }

}
