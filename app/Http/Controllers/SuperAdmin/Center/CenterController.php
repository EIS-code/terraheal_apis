<?php

namespace App\Http\Controllers\SuperAdmin\Center;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use App\Therapist;
use App\Shop;
use App\Booking;
use App\BookingInfo;
use DB;
use App\Voucher;
use App\VoucherShop;
use App\Pack;
use App\PackShop;
use App\Receptionist;
use App\Manager;

class CenterController extends BaseController {

    public $successMsg = [
        'center.details' => 'Center details found successfully.',
    ];
    public $errorMsg = [
        'center.not.found' => 'Center not found.',
    ];

    public function getSoldVoucher(Request $request) {

        $voucherModel = new Voucher();
        $voucherShopModel = new VoucherShop();

        $vouchers = $voucherModel->getVoucherQuery()->where($voucherShopModel::getTableName() . '.shop_id', $request->center_id)->get();

        return $vouchers;
    }

    public function getSoldPacks(Request $request) {

        $packModel = new Pack();
        $packShopModel = new PackShop();

        $packs = $packModel->getPackQuery()->where($packShopModel::getTableName() . '.shop_id', $request->shop_id)->get();

        return $packs;
    }

    public function getEarning(Request $request) {

        $totalSales = DB::table('booking_massages')
                ->join('booking_infos', 'booking_infos.id', '=', 'booking_massages.booking_info_id')
                ->join('bookings', 'bookings.id', '=', 'booking_infos.booking_id')
                ->select('booking_massages.*', 'booking_infos.*', 'booking_infos.*')
                ->where(['booking_infos.is_done' => (string) BookingInfo::IS_DONE, 'bookings.shop_id' => $request->center_id])
                ->sum('booking_massages.price');
        $totalCost = DB::table('booking_massages')
                ->join('booking_infos', 'booking_infos.id', '=', 'booking_massages.booking_info_id')
                ->join('bookings', 'bookings.id', '=', 'booking_infos.booking_id')
                ->select('booking_massages.*', 'booking_infos.*', 'booking_infos.*')
                ->where(['booking_infos.is_done' => (string) BookingInfo::IS_DONE, 'bookings.shop_id' => $request->center_id])
                ->sum('booking_massages.cost');
        $totalEarning = number_format(($totalSales - $totalCost) / ($totalCost * 100), 2);
        return $totalEarning;
    }

    public function getCenterDetails(Request $request) {

        $shopModel = new Shop();
        $shop = Shop::find($request->center_id);
        if (empty($shop)) {
            return $this->returnError($this->errorMsg['center.not.found']);
        } 
        $massages = $shopModel->getMassages($request)->count();
        $therapies = $shopModel->getTherapies($request)->count();
        $homeBooking = Booking::where(['booking_type' => Booking::BOOKING_TYPE_HHV, 'shop_id' => $request->center_id])->get()->count();
        $centerBooking = Booking::where(['booking_type' => Booking::BOOKING_TYPE_IMC, 'shop_id' => $request->center_id])->get()->count();
        $vouchers = $this->getSoldVoucher($request)->count();
        $packs = $this->getSoldPacks($request)->count();
        $totalBookings = Booking::where('shop_id', $request->center_id)->get()->count();
        $cancelledBookings = DB::table('bookings')
                ->join('booking_infos', 'booking_infos.booking_id', '=', 'bookings.id')
                ->where('booking_infos.is_cancelled', (string) BookingInfo::IS_CANCELLED)->get()->count();
        $earning = $this->getEarning($request);
        $topItems = $shopModel->getTopItems($request);
        $therapists = Therapist::where('shop_id', $request->center_id)->get()->count();
        $receptionists = Receptionist::where('shop_id', $request->center_id)->get()->count();
        $managers = Manager::where('shop_id', $request->center_id)->get()->count();
        $staff = $therapists + $receptionists + $managers;

        return $this->returnSuccess(__($this->successMsg['center.details']), ['massages' => $massages, 'therapies' => $therapies, 'services' => $massages + $therapies,
                    'home_visits' => $homeBooking, 'center_visits' => $centerBooking, 'vouchers' => $vouchers, 'packs' => $packs, 'earning' => $earning,
                    'totalBookings' => $totalBookings, 'cancelledBookings' => $cancelledBookings, 'staff' => $staff, 'shop' => $shop, 'topItems' => $topItems]);
    }

}
