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
use App\ShopPaymentDetail;
use App\ShopDocument;
use App\Libraries\CommonHelper;
use App\Constant;
use Illuminate\Support\Facades\Storage;
use App\User;
use App\TherapistUserRating;

class CenterController extends BaseController {

    public $successMsg = [
        'center.details' => 'Center details found successfully.',
        'center.booking.details' => 'Center bookings details found successfully.',
        'center.therapists.details' => 'Center therapists details found successfully.',
        'center.add.details' => 'Center details added successfully.',
        'company.add.details' => 'Company details added successfully.',
        'owner.add.details' => 'Owner details added successfully.',
        'payment.add.details' => 'Payment details added successfully.',
        'payment.agreement.add' => 'Payment agreement details added successfully.',
        'documents.upload' => 'Center documents uploaded successfully.',
        'center.vouchers' => 'Vouchers added successfully.',
        'center.packs' => 'Packs added successfully.',
        'center.constant' => 'Center constant details found successfully.',
        'center.constant.add' => 'Center constant details added successfully.',
        'image' => 'Image deleted successfully.',
        'center.users' => 'Users data found successfully.',
    ];
    public $errorMsg = [
        'center.not.found' => 'Center not found.',
        'image.not.found' => 'Image not found.',
    ];

    public function getSoldVoucher(Request $request) {

        $voucherModel = new Voucher();
        $voucherShopModel = new VoucherShop();

        $vouchers = $voucherModel->getVoucherQuery()->where($voucherShopModel::getTableName() . '.shop_id', $request->shop_id)->get();

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
                ->where(['booking_infos.is_done' => (string) BookingInfo::IS_DONE, 'bookings.shop_id' => $request->shop_id])
                ->sum('booking_massages.price');
        $totalCost = DB::table('booking_massages')
                ->join('booking_infos', 'booking_infos.id', '=', 'booking_massages.booking_info_id')
                ->join('bookings', 'bookings.id', '=', 'booking_infos.booking_id')
                ->select('booking_massages.*', 'booking_infos.*', 'booking_infos.*')
                ->where(['booking_infos.is_done' => (string) BookingInfo::IS_DONE, 'bookings.shop_id' => $request->shop_id])
                ->sum('booking_massages.cost');
        $totalEarning = $totalCost == 0 ? 0 : number_format(($totalSales - $totalCost) / ($totalCost * 100), 2);
        return $totalEarning;
    }

    public function getCenterDetails(Request $request) {

        $shopModel = new Shop();
        $shop = Shop::find($request->shop_id);
        if (empty($shop)) {
            return $this->returnError($this->errorMsg['center.not.found']);
        } 
        $massages = $shopModel->getMassages($request)->count();
        $therapies = $shopModel->getTherapies($request)->count();
        $therapists = Therapist::where('shop_id', $request->shop_id)->get()->count();
        $clients = User::with('shop:id,name','city:id,name','country:id,name')->where('shop_id', $request->shop_id)->get()->count();
        
//        $homeBooking = Booking::where(['booking_type' => Booking::BOOKING_TYPE_HHV, 'shop_id' => $request->shop_id])->get()->count();
//        $centerBooking = Booking::where(['booking_type' => Booking::BOOKING_TYPE_IMC, 'shop_id' => $request->shop_id])->get()->count();
//        $vouchers = $this->getSoldVoucher($request)->count();
//        $packs = $this->getSoldPacks($request)->count();
//        $totalBookings = Booking::where('shop_id', $request->shop_id)->get()->count();
//        $cancelledBookings = DB::table('bookings')
//                ->join('booking_infos', 'booking_infos.booking_id', '=', 'bookings.id')
//                ->where('booking_infos.is_cancelled', (string) BookingInfo::IS_CANCELLED)->get()->count();
//        $earning = $this->getEarning($request);
//        $topItems = $shopModel->getTopItems($request);
//        $receptionists = Receptionist::where('shop_id', $request->shop_id)->get()->count();
//        $managers = Manager::where('shop_id', $request->shop_id)->get()->count();
//        $staff = $therapists + $receptionists + $managers;

        return $this->returnSuccess(__($this->successMsg['center.details']), ['massages' => $massages, 'therapies' => $therapies, 'therapists' => $therapists,'clients' => $clients, 'shop' => $shop]);
    }
    
    public function getCenterBookings(Request $request) {
        
        $dateFilter = !empty($request->date_filter) ? $request->date_filter : Booking::TODAY;
        $booking = DB::table('booking_massages')
                ->join('booking_infos', 'booking_infos.id', '=', 'booking_massages.booking_info_id')
                ->join('bookings', 'bookings.id', '=', 'booking_infos.booking_id')
                ->select('booking_massages.*', 'booking_infos.*', 'booking_infos.*')
                ->where('bookings.shop_id', $request->shop_id);
        
        $now = Carbon::now();
        if ($dateFilter == Booking::TODAY) {
            $booking->where('booking_infos.massage_date', Carbon::today()->format('Y-m-d'));
        }
        if ($dateFilter == Booking::YESTERDAY) {
            $booking->where('booking_infos.massage_date', $now->subDays(1));
        }
        if ($dateFilter == Booking::THIS_WEEK) {
            $weekStartDate = $now->startOfWeek()->format('Y-m-d');
            $weekEndDate = $now->endOfWeek()->format('Y-m-d');

            $booking->whereBetween('booking_infos.massage_date', [$weekStartDate, $weekEndDate]);
        }
        if ($dateFilter == Booking::THIS_MONTH) {
            $booking->whereMonth('booking_infos.massage_date', $now->month);
        }
        $center = clone $booking;
        
        $homeVisit = $booking->where('bookings.booking_type' , Booking::BOOKING_TYPE_HHV)->get()->count();
        $centerVisit = $center->where('bookings.booking_type' , Booking::BOOKING_TYPE_IMC)->get()->count();
        
        return $this->returnSuccess(__($this->successMsg['center.booking.details']), ['homeVisit' => $homeVisit, 'centerVisit' => $centerVisit]);
    }
    
    public function getUsers(Request $request) {
        
        $dateFilter = !empty($request->date_filter) ? $request->date_filter : Booking::TODAY;
        $shopId = $request->shop_id;
        $appUsers = DB::table('booking_massages')
                ->join('booking_infos', 'booking_infos.id', '=', 'booking_massages.booking_info_id')
                ->join('bookings', 'bookings.id', '=', 'booking_infos.booking_id')
                ->join('users', 'users.id', '=', 'bookings.user_id')
                ->select('booking_massages.*', 'booking_infos.*', 'booking_infos.*')
                ->where('bookings.book_platform', (string) Booking::BOOKING_PLATFORM_APP);
                
        $guestUsers = User::where('is_guest', (string) User::IS_GUEST);
        $registeredUsers = User::where('is_guest', (string) User::IS_NOT_GUEST);
        
        if(!empty($shopId)) {
            $appUsers->where('users.shop_id', $shopId);
            $guestUsers->where('shop_id', $shopId);
            $registeredUsers->where('shop_id', $shopId);
        }
        
        if(!empty($dateFilter)) {
            
            $now = Carbon::now();
            if ($dateFilter == Booking::YESTERDAY) {
                $appUsers->whereDate('users.created_at', Carbon::yesterday()->format('Y-m-d'));
                $guestUsers->whereDate('created_at', Carbon::yesterday()->format('Y-m-d'));
                $registeredUsers->whereDate('created_at', Carbon::yesterday()->format('Y-m-d'));
            }
            if ($dateFilter == Booking::TODAY) {
                $appUsers->whereDate('users.created_at', $now->format('Y-m-d'));
                $guestUsers->whereDate('created_at', $now->format('Y-m-d'));
                $registeredUsers->whereDate('created_at', $now->format('Y-m-d'));
            }
            if ($dateFilter == Booking::THIS_WEEK) {
                $weekStartDate = $now->startOfWeek()->format('Y-m-d');
                $weekEndDate = $now->endOfWeek()->format('Y-m-d');

                $appUsers->whereDate('users.created_at', '>=', $weekStartDate)->whereDate('users.created_at', '<=', $weekEndDate);
                $guestUsers->whereDate('created_at', '>=', $weekStartDate)->whereDate('created_at', '<=', $weekEndDate);
                $registeredUsers->whereDate('created_at', '>=', $weekStartDate)->whereDate('created_at', '<=', $weekEndDate);
            }
            if ($dateFilter == Booking::THIS_MONTH) {
                $appUsers->whereMonth('users.created_at', $now->month)
                     ->whereYear('users.created_at', $now->year);
                $guestUsers->whereMonth('created_at', $now->month)
                     ->whereYear('created_at', $now->year);
                $registeredUsers->whereMonth('created_at', $now->month)
                     ->whereYear('created_at', $now->year);
            }
        }
        
        $appUsers = $appUsers->get()
                ->groupBy('bookings.user_id')->count();
        return $this->returnSuccess(__($this->successMsg['center.users']), ['appUsers' => $appUsers, 'guestUsers' => $guestUsers->get()->count(), 'registeredUsers' => $registeredUsers->get()->count()]);
    }
    
    public function addFeaturedImages(Request $request, $centerId) {
        
        $imgData = [];
        if (!empty($request->featured_images)) {
            $featuredModel = new ShopFeaturedImage();

            $checkImage = $featuredModel->validateImages($request->featured_images);
            if ($checkImage->fails()) {
                return $checkImage;
            }
            if ($request->hasfile('featured_images')) {
                foreach ($request->file('featured_images') as $file) {
                    $name = $file->getClientOriginalExtension();
                    $fileName = mt_rand(). time() . '_' . $centerId . '.' . $name;
                    $storeFile = $file->storeAs($featuredModel->storageFolderName, $fileName, $featuredModel->fileSystem);

                    if ($storeFile) {
                        $image['image'] = $fileName;
                        $image['shop_id'] = $centerId;
                    }
                    $check = $featuredModel->validator($image);
                    if ($check->fails()) {
                        return $check;
                    }
                    $featuredModel->create($image);
                    $imgData[] = $image;
                }
            }
        }
        return $imgData;
    }

    public function addGallery(Request $request, $centerId) {
        
        $imgData = [];
        if(!empty($request->gallery)) {
            
            $galleryModel = new ShopGallary();

            $checkImage = $galleryModel->validateImages($request->gallery);
            if ($checkImage->fails()) {
                return $this->returnError($checkImage->errors()->first(), NULL, true);
            }
            if($request->hasfile('gallery')) {
        }
            foreach($request->file('gallery') as $file)
            {
                $name = $file->getClientOriginalExtension();
                $fileName = mt_rand(). time() . '_' . $centerId . '.' . $name;
                $storeFile = $file->storeAs($galleryModel->storageFolderName, $fileName, $galleryModel->fileSystem);

                if ($storeFile) {
                    $gallery['image'] = $fileName;
                    $gallery['shop_id'] = $centerId;
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
    
    public function addOrUpdateTimeTable(Request $request, $centerId) {

        $shopHours = [];
        $data = $request->all();
        if (!empty($data['timetable'])) {
            $shopHourModel = new ShopHour();

            foreach ($data['timetable']['open_at'] as $key => $value) {
                $time = Carbon::createFromTimestampMs($value);
                $data['timetable']['open_at'][$key] = $time->format("H:i:s");
            }
            foreach ($data['timetable']['close_at'] as $key => $value) {
                $time = Carbon::createFromTimestampMs($value);
                $data['timetable']['close_at'][$key] = $time->format("H:i:s");
                $timeTable = [
                    'day_name' => (string) $key,
                    'is_open' => (string) ShopHour::IS_OPEN,
                    'open_at' => $data['timetable']['open_at'][$key],
                    'close_at' => $data['timetable']['close_at'][$key],
                    'shop_id' => $centerId,
                ];
                $check = $shopHourModel->validator($timeTable);
                if ($check->fails()) {
                    return $check;
                }
                $shopHourModel->updateOrCreate($timeTable, $timeTable);
                $shopHours[] = $timeTable;
            }
        }
        return $shopHours;
    }

    public function addCenterDetails(Request $request) {

        DB::beginTransaction();
        try {
            $centerId = null;
            if (!empty($request->shop_id)) {
                $center = Shop::find($request->shop_id);
                if (empty($center)) {
                    return $this->returnSuccess(__($this->errorMsg['center.not.found']));
                }
                $centerId = $center->id;
            }
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

            $checks = $shopModel->validator($data, $centerId);
            if ($checks->fails()) {
                return $this->returnError($checks->errors()->first(), NULL, true);
            }
            if(!empty($centerId)) {
                $center->update($data);
            } else {            
                $center = $shopModel->create($data);
            }
            $centerId = $center->id;
            $featuredImages = $this->addFeaturedImages($request, $centerId);
            if(!is_array($featuredImages)) {
                return $this->returnError($featuredImages->errors()->first(), NULL, true);
            }
            $gallery = $this->addGallery($request, $centerId);
            if(!is_array($gallery)) {
                return $this->returnError($gallery->errors()->first(), NULL, true);
            }
            $timetable = $this->addOrUpdateTimeTable($request, $centerId);
            if(!is_array($timetable)) {
                return $this->returnError($timetable->errors()->first(), NULL, true);
            }
            DB::commit();
            $centerDetails = Shop::with('company','featuredImages','gallery','timetable')->where('id',$centerId)->get();
            return $this->returnSuccess(__($this->successMsg['center.add.details']),$centerDetails);
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
            'shop_id' => $data['shop_id'] ? $data['shop_id'] : null
        ];

        $checks = $companyModel->validator($company);
        if ($checks->fails()) {
            return $this->returnError($checks->errors()->first(), NULL, true);
        }
        $companyData = $companyModel->updateOrCreate(['shop_id' => $request->shop_id], $company);
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
            'shop_id' => $request->shop_id
        ];
        
        $checks = $shopModel->validatorOwner($ownerData, $request->shop_id, true);
        if ($checks->fails()) {
            return $this->returnError($checks->errors()->first(), NULL, true);
        }
        
        $center = $shopModel->find($request->shop_id);
        $center->update($ownerData);
        
        return $this->returnSuccess(__($this->successMsg['owner.add.details']), $center);
    }
    
    public function addPaymentDetails(Request $request) {
        
        $paymentModel = new ShopPaymentDetail();
        
        $paymentDetails = [
            'iban' => $request->iban,
            'paypal_secret' => $request->paypal_secret,
            'paypal_client_id' => $request->paypal_client_id,
            'google_pay_number' => $request->google_pay_number,
            'apple_pay_number' => $request->apple_pay_number,
            'shop_id' => $request->shop_id 
        ];
        $checks = $paymentModel->validator($paymentDetails);
        if ($checks->fails()) {
            return $this->returnError($checks->errors()->first(), NULL, true);
        }
        $payment = $paymentModel->updateOrCreate(['shop_id' => $request->shop_id], $paymentDetails);
        
        return $this->returnSuccess(__($this->successMsg['payment.add.details']), $payment);
    }
    
    public function addPaymentAgreement(Request $request) {
        
        $paymentModel = new ShopPaymentDetail();
        
        $paymentDetails = [
            'sales_percentage' => $request->sales_percentage,
            'inital_amount' => $request->inital_amount,
            'fixed_amount' => $request->fixed_amount,
            'shop_id' => $request->shop_id
        ];
        $checks = $paymentModel->validateAgreement($paymentDetails);
        if ($checks->fails()) {
            return $this->returnError($checks->errors()->first(), NULL, true);
        }
        
        $payment = $paymentModel->updateOrCreate(['shop_id' => $request->shop_id], $paymentDetails);
        return $this->returnSuccess(__($this->successMsg['payment.agreement.add']), $payment);
    }
    
    public function uploadDocuments(Request $request) {
        
        $docModel = new ShopDocument();
        $data = $request->all();
        $checkImages = $docModel->validateImages($data);
        if ($checkImages->fails()) {
            return $this->returnError($checkImages->errors()->first(), NULL, true);
        }
        if($request->hasfile('franchise_contact')) {
            $image = CommonHelper::uploadImage($data['franchise_contact'], $docModel->storageFolderNameFranchise, $docModel->fileSystem, $data['shop_id']);
            $imgData['franchise_contact'] = $image ? $image : null;
        }
        if($request->hasfile('id_passport')) {
            $image = CommonHelper::uploadImage($data['id_passport'], $docModel->storageFolderNameIdPassport, $docModel->fileSystem, $data['shop_id']);
            $imgData['id_passport'] = $image ? $image : null;
        }
        if($request->hasfile('registration')) {
            $image = CommonHelper::uploadImage($data['registration'], $docModel->storageFolderNameRegistration, $docModel->fileSystem, $data['shop_id']);
            $imgData['registration'] = $image ? $image : null;
        }
        $imgData['shop_id'] = $data['shop_id'];
        $checks = $docModel->validator($imgData);
        if ($checks->fails()) {
            return $this->returnError($checks->errors()->first(), NULL, true);
        }
        
        $documents = $docModel->updateOrCreate(['shop_id' => $request->shop_id], $imgData);
        return $this->returnSuccess(__($this->successMsg['documents.upload']), $documents);
    }
    
    public function addVouchers(Request $request) {
        
        DB::beginTransaction();
        try {
            if(!empty($request->vouchers)) {
                $voucherModel = new VoucherShop();
                foreach ($request->vouchers as $key => $voucher) {
                    $data = [
                        'voucher_id' => $voucher,
                        'shop_id' => $request->shop_id
                    ];
                    $checks = $voucherModel->validator($data);
                    if ($checks->fails()) {
                        return $this->returnError($checks->errors()->first(), NULL, true);
                    }
                    $voucherModel->updateOrCreate($data,$data);
                    $vouchers[] = $data;
                }
                DB::commit();
                return $this->returnSuccess(__($this->successMsg['center.vouchers']), $vouchers);
            }
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollback();
            throw $e;
        }
    }
    
    public function addPacks(Request $request) {
        
        DB::beginTransaction();
        try {
            if(!empty($request->packs)) {
                $packModel = new PackShop();
                foreach ($request->packs as $key => $pack) {
                    $data = [
                        'pack_id' => $pack,
                        'shop_id' => $request->shop_id
                    ];
                    $checks = $packModel->validator($data);
                    if ($checks->fails()) {
                        return $this->returnError($checks->errors()->first(), NULL, true);
                    }
                    $packModel->updateOrCreate($data,$data);
                    $packs[] = $data;
                }
                DB::commit();
                return $this->returnSuccess(__($this->successMsg['center.packs']), $packs);
            }
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollback();
            throw $e;
        }
    }
    
    public function getConstants() {
        
        $constants = Constant::all();
        return $this->returnSuccess(__($this->successMsg['center.constant']), $constants);
    }
    
    public function addConstants(Request $request) {
        
        $constModel = new Constant();
        $data = $request->all();
        
        $checks = $constModel->validator($data);
        if ($checks->fails()) {
            return $this->returnError($checks->errors()->first(), NULL, true);
        }
        
        $constants = $constModel->create($data);
        return $this->returnSuccess(__($this->successMsg['center.constant.add']), $constants);
    }
    
    public function deleteFeaturedImages(Request $request) {
     
        DB::beginTransaction();
        try {
            $featuredModel = new ShopFeaturedImage();
            $image = $featuredModel->where(['id' => $request->image_id, 'shop_id' => $request->shop_id])->first();
            if(empty($image)) {
                return $this->returnSuccess(__($this->errorMsg['image.not.found']));
            }
            $pathinfo = pathinfo($image->image);
            if (empty($pathinfo['filename'])) {
                return $this->returnSuccess(__($this->errorMsg['image.not.found']));
            }
            $storageFolderNameRegistration = (str_ireplace("\\", "/", $featuredModel->storageFolderName));
            $imagePath = $storageFolderNameRegistration . $pathinfo['filename'].'.'.$pathinfo['extension'];
            if (Storage::disk($featuredModel->fileSystem)->exists($imagePath)) {
                    Storage::disk($featuredModel->fileSystem)->delete($imagePath);
            }
            $image->delete();
            DB::commit();
            return $this->returnSuccess(__($this->successMsg['image']));
            
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollback();
            throw $e;
        }    
    }
    
    public function deleteGalleryImages(Request $request) {
     
        DB::beginTransaction();
        try {
            $galleryModel = new ShopGallary();
            $image = $galleryModel->where(['id' => $request->image_id, 'shop_id' => $request->shop_id])->first();
            if(empty($image)) {
                return $this->returnSuccess(__($this->errorMsg['image.not.found']));
            }
            $pathinfo = pathinfo($image->image);
            if (empty($pathinfo['filename'])) {
                return $this->returnSuccess(__($this->errorMsg['image.not.found']));
            }
            $storageFolderNameRegistration = (str_ireplace("\\", "/", $galleryModel->storageFolderName));
            $imagePath = $storageFolderNameRegistration . $pathinfo['filename'].'.'.$pathinfo['extension'];
            if (Storage::disk($galleryModel->fileSystem)->exists($imagePath)) {
                    Storage::disk($galleryModel->fileSystem)->delete($imagePath);
            }
            $image->delete();
            DB::commit();
            return $this->returnSuccess(__($this->successMsg['image']));
            
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollback();
            throw $e;
        }    
    }
    
    public function getTherapists(Request $request) {
        
        $therapists = Therapist::withCount('selectedMassages', 'selectedTherapies')->where('shop_id', $request->shop_id)->get();
        
        foreach ($therapists as $key => $therapist) {

            $ratings = TherapistUserRating::where(['model_id' => $therapist->id, 'model' => 'App\Therapist'])->get();

            $cnt = $rates = $avg = 0;
            if ($ratings->count() > 0) {
                foreach ($ratings as $i => $rating) {
                    $rates += $rating->rating;
                    $cnt++;
                }
                $avg = $rates / $cnt;
            }
            $therapist['average'] = number_format($avg, 2);
        }
        $therapists = $therapists->sortByDesc('average');
        $therapists = $therapists->toArray();
        
        return $this->returnSuccess(__($this->successMsg['center.therapists.details']), array_values($therapists));
    }
}
