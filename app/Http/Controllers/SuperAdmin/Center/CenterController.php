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
use Illuminate\Support\Facades\Hash;
use App\ShopGallary;
use App\ShopHour;
use Carbon\Carbon;
use App\ShopCompany;
use App\ShopFeaturedImage;

class CenterController extends BaseController {

    public $successMsg = [
        'center.details' => 'Center details found successfully.',
        'center.add.details' => 'Center details added successfully.',
        'company.add.details' => 'Company details added successfully.',
        'owner.add.details' => 'Owner details added successfully.',
    ];
    public $errorMsg = [
        'center.not.found' => 'Center not found.',
        'something.wrong' => 'Something goes wrong!!',
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
    
    public function addOrUpdateDetails(Request $request) {
        $data = $request->all();
        $shopModel = new Shop();
        
        $data['country_id'] = $data['location']['country_id'] ? $data['location']['country_id'] : null;
        $data['province_id'] = $data['location']['province_id'] ? $data['location']['province_id'] : null;
        $data['city_id'] = $data['location']['city_id'] ? $data['location']['city_id'] : null;
        $data['longitude'] = $data['location']['longitude'] ? $data['location']['longitude'] : null;
        $data['latitude'] = $data['location']['latitude'] ? $data['location']['latitude'] : null;
        $data['zoom'] = $data['location']['zoom'] ? $data['location']['zoom'] : null;
        $data['pin_code'] = $data['location']['pin_code'] ? $data['location']['pin_code'] : null;
        $data['address'] = $data['location']['address'] ? $data['location']['address'] : null;
        $data['address2'] = $data['location']['address2'] ? $data['location']['address2'] : null;
        $data['shop_password'] = Hash::make($data['shop_password']);
        $data['manager_password'] = Hash::make($data['manager_password']);
        unset($data['location']);

        $checks = $shopModel->validator($data);
        if ($checks->fails()) {
            return $checks;
        }
        $center = $shopModel->create($data);
        return $center;
    }
    
    public function addFeaturedImages(Request $request, $center) {
        
        $featuredModel = new ShopFeaturedImage();

        $checkImage = $featuredModel->validateImages($request->featured_images);
        if ($checkImage->fails()) {
            return $this->returnError($checkImage->errors()->first(), NULL, true);
        }
        if($request->hasfile('featured_images')) {
            foreach($request->file('featured_images') as $file)
            {
                $name = $file->getClientOriginalName();
                $fileName = time() . '_' . $name;
                $storeFile = $file->storeAs($featuredModel->storageFolderName, $fileName, $featuredModel->fileSystem);

                if ($storeFile) {
                    $image['image'] = $fileName;
                    $image['shop_id'] = $center->id;
                } 
                $check = $featuredModel->validator($image);
                if ($check->fails()) {
                    return $check;
                }
                $featuredModel->create($image);
                $imgData[] = $image;
            }
        }
        return $imgData;
    }
    
    public function addGallery(Request $request, $center) {
        
        $galleryModel = new ShopGallary();

        $checkImage = $galleryModel->validateImages($request->gallery);
        if ($checkImage->fails()) {
            return $this->returnError($checkImage->errors()->first(), NULL, true);
        }
        if($request->hasfile('gallery')) {
            foreach($request->file('gallery') as $file)
            {
                $name = $file->getClientOriginalName();
                $fileName = time() . '_' . $name;
                $storeFile = $file->storeAs($galleryModel->storageFolderName, $fileName, $galleryModel->fileSystem);

                if ($storeFile) {
                    $gallery['image'] = $fileName;
                    $gallery['shop_id'] = $center->id;
                } 
                $check = $galleryModel->validator($gallery);
                if ($check->fails()) {
                    return $check;
                }
                $galleryModel->create($gallery);
                $imgData[] = $gallery;
            }
        }
        return $imgData;
    }
    
    public function addOrUpdateTimeTable(Request $request, $center) {
        
        $shopHourModel = new ShopHour();
        $data = $request->all();
        
        foreach ($data['timetable']['open_at'] as $key => $value) {
            $time = Carbon::createFromTimestampMs($value);
            $data['timetable']['open_at'][$key] = $time->format("h:i:s");
        }
        foreach ($data['timetable']['close_at'] as $key => $value) {
            $time = Carbon::createFromTimestampMs($value);
            $data['timetable']['close_at'][$key] = $time->format("h:i:s");
            $timeTable = [
               'day_name' => (string) $key,
               'is_open' => (string) ShopHour::IS_OPEN,
               'open_at' => $data['timetable']['open_at'][$key],
               'close_at' => $data['timetable']['close_at'][$key],
               'shop_id' => $center->id,
            ];
            $check = $shopHourModel->validator($timeTable);
            if ($check->fails()) {
                return $check;
            }
            $shopHourModel->create($timeTable);
            $shopHours[] = $timeTable;
        }
        return $shopHours;
    }
    
    public function addCenterDetails(Request $request) {

        DB::beginTransaction();
        try {

            $center = $this->addOrUpdateDetails($request);
            if(!is_array($center)) {
                return $this->returnError($center->errors()->first(), NULL, true);
            }
            $featuredImages = $this->addFeaturedImages($request, $center);
            if(!is_array($featuredImages)) {
                return $this->returnError($featuredImages->errors()->first(), NULL, true);
            }
            $gallery = $this->addGallery($request, $center);
            if(!is_array($gallery)) {
                return $this->returnError($gallery->errors()->first(), NULL, true);
            }
            $timetable = $this->addOrUpdateTimeTable($request, $center);
            if(!is_array($timetable)) {
                return $this->returnError($timetable->errors()->first(), NULL, true);
            }
            DB::commit();
            return $this->returnSuccess(__($this->successMsg['center.add.details']), [$center, $featuredImages, $gallery, $timetable]);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollback();
            throw $e;
        }
    }
    
    public function addOrUpdateCompanyDetails(Request $request) {
        
        $data = $request->all();
        $companyModel =  new ShopCompany();
        $company = [
            'name' => $data['name'],
            'nif' => $data['nif'],
            'address' => $data['location']['address'] ? $data['location']['address'] : null,
            'city_id' => $data['location']['city_id'] ? $data['location']['city_id'] : null,
            'province_id' => $data['location']['province_id'] ? $data['location']['province_id'] : null,
            'country_id' => $data['location']['country_id'] ? $data['location']['country_id'] : null,
            'longitude' => $data['location']['longitude'] ? $data['location']['longitude'] : null,
            'latitude' => $data['location']['latitude'] ? $data['location']['latitude'] : null,
            'zoom' => $data['location']['zoom'] ? $data['location']['zoom'] : null,
            'shop_id' => $data['center_id'] ? $data['center_id'] : null
        ];

        $checks = $companyModel->validator($company);
        if ($checks->fails()) {
            return $this->returnError($checks->errors()->first(), NULL, true);
        }
        $companyData = $companyModel->updateOrCreate($company);
        return $this->returnSuccess(__($this->successMsg['company.add.details']), $companyData);
    }

    public function addOwnerDetails(Request $request) {
     
        $data = $request->all();
        $shopModel = new Shop();
        $ownerData = [
            'owner_name' => $data['owner_name'],
            'owner_surname' => $data['owner_surname'],
            'owner_email' => $data['owner_email'],
            'owner_mobile_number' => $data['owner_mobile_number'],
            'owner_mobile_number_alternative' => $data['owner_mobile_number_alternative'],
            'finacial_situation' => $data['finacial_situation'],
            'shop_id' => $request->center_id
        ];
        
        $checks = $shopModel->validatorOwner($ownerData, $request->center_id, true);
        if ($checks->fails()) {
            return $this->returnError($checks->errors()->first(), NULL, true);
        }
        
        $center = $shopModel->find($request->center_id);
        $center->update($ownerData);
        
        return $this->returnSuccess(__($this->successMsg['owner.add.details']), $center);
    }
    
    
}
