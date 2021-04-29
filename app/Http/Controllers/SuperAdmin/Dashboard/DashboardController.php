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
use App\BookingMassage;
use App\MassagePrice;
use App\TherapiesPrices;
use DB;

class DashboardController extends BaseController {

    public $successMsg = [
        'data.found' => 'Dashboard details found successfully.',
    ];

    public function getDetails() {

        $massages = Massage::all()->count();
        $therapies = Therapy::all()->count();
        $shops = Shop::where('is_admin', Shop::IS_ADMIN)->get()->count();
        $therapists = Therapist::all()->count();
        $clients = User::all()->count();

        return $this->returnSuccess(__($this->successMsg['data.found']), ['massages' => $massages, 'therapies' => $therapies, 'shops' => $shops,
                    'therapists' => $therapists, 'clients' => $clients]);
    }

    public function getSidebarDetails(Request $request) {

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

        $massageModel = new Massage();
        $therapyModel = new Therapy();

        $massageModel->setMysqlStrictFalse();
        $therapyModel->setMysqlStrictFalse();

        $service = $request->service ? $request->service : Booking::MASSAGES;
        if ($service == Booking::MASSAGES) {
            $getTopItems = $massageModel->select(Massage::getTableName() . ".id", Massage::getTableName() . ".name", Massage::getTableName() . ".icon", BookingMassage::getTableName() . '.price', DB::raw('SUM(' . BookingMassage::getTableName() . '.price) As totalEarning'))
                    ->leftJoin(MassagePrice::getTableName(), Massage::getTableName() . '.id', '=', MassagePrice::getTableName() . '.massage_id')
                    ->leftJoin(BookingMassage::getTableName(), MassagePrice::getTableName() . '.id', '=', BookingMassage::getTableName() . '.massage_prices_id')
                    ->whereNotNull(BookingMassage::getTableName() . '.id')
                    ->groupBy(Massage::getTableName() . '.id')
                    ->orderBy(DB::RAW('SUM(' . BookingMassage::getTableName() . '.price)'), 'DESC')
                    ->get();
        }
        if ($service == Booking::THERAPIES) {
            $getTopItems = $therapyModel->select(Therapy::getTableName() . ".id", Therapy::getTableName() . ".name", Therapy::getTableName() . ".image", BookingMassage::getTableName() . '.price', DB::raw('SUM(' . BookingMassage::getTableName() . '.price) As totalEarning'))
                    ->leftJoin(TherapiesPrices::getTableName(), Therapy::getTableName() . '.id', '=', TherapiesPrices::getTableName() . '.therapy_id')
                    ->leftJoin(BookingMassage::getTableName(), TherapiesPrices::getTableName() . '.id', '=', BookingMassage::getTableName() . '.therapy_timing_id')
                    ->whereNotNull(BookingMassage::getTableName() . '.id')
                    ->groupBy(Therapy::getTableName() . '.id')
                    ->orderBy(DB::RAW('SUM(' . BookingMassage::getTableName() . '.price)'), 'DESC')
                    ->get();
        }

        $massageModel->setMysqlStrictTrue();
        $therapyModel->setMysqlStrictTrue();

        return $this->returnSuccess(__($this->successMsg['data.found']), ['appUsers' => $appUsers, 'homeBooking' => $homeBooking,
                    'totalSales' => $totalSales, 'totalEarning' => $totalEarning, 'getTopItems' => $getTopItems]);
    }
}
