<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use App\Voucher;
use App\VoucherShop;
use App\UserVoucher;
use App\UserVoucherPrice;
use Carbon\Carbon;
use App\Pack;
use App\PackShop;
use App\PackService;
use App\UserPack;
use DB;

class SuperAdminController extends BaseController {

    public $successMsg = [
        'voucher.add' => 'Voucher created successfully!',
        'voucher.update' => 'Voucher updated successfully!',
        'voucher.get' => 'Vouchers found successfully!',
        'voucher.share' => 'Voucher shared successfully!',
        'voucher.purchase' => 'Voucher purchased successfully!',
        'voucher.add.services' => 'Services added to voucher successfully!',
        'pack.add' => 'Pack created successfully!',
        'pack.share' => 'Pack shared successfully!',
        'pack.get' => 'Packs found successfully!',
        'pack.purchase' => 'Pack purchased successfully!',
    ];

    public function addVoucher(Request $request) {

        $model = new Voucher();
        $data = $request->all();
        $data['number'] = generateRandomString();
        $data['expired_date'] = Carbon::createFromTimestampMs($data['expired_date']);

        $checks = $model->validator($data);
        if ($checks->fails()) {
            return $this->returnError($checks->errors()->first(), NULL, true);
        }
        
        /* For profile Image */
        if ($request->hasFile('image')) {
            $checkImage = $model->validateImage($data);
            if ($checkImage->fails()) {
                unset($data['image']);

                return $this->returnError($checkImage->errors()->first(), NULL, true);
            }
            $fileName = time() . '.' . $data['image']->getClientOriginalExtension();
            $storeFile = $data['image']->storeAs($model->profilePhotoPath, $fileName, $model->fileSystem);

            if ($storeFile) {
                $data['image'] = $fileName;
            }
        }
        $voucher = $model->create($data);

        return $this->returnSuccess(__($this->successMsg['voucher.add']), $voucher);
    }

    public function updateVoucher(Request $request) {

        $voucher = Voucher::find($request->voucher_id);
        $data = $request->all();
        $data['expired_date'] = Carbon::createFromTimestampMs($data['expired_date']);
        
        /* For profile Image */
        if ($request->hasFile('image')) {
            $checkImage = $model->validateImage($data);
            if ($checkImage->fails()) {
                unset($data['image']);

                return $this->returnError($checkImage->errors()->first(), NULL, true);
            }
            $fileName = time() . '.' . $data['image']->getClientOriginalExtension();
            $storeFile = $data['image']->storeAs($model->profilePhotoPath, $fileName, $model->fileSystem);

            if ($storeFile) {
                $data['image'] = $fileName;
            }
        }
        
        $voucher->update($data);

        return $this->returnSuccess(__($this->successMsg['voucher.update']), $voucher);
    }

    public function getVouchers() {

        $vouchers = Voucher::all();
        return $this->returnSuccess(__($this->successMsg['voucher.get']), $vouchers);
    }

    public function shareVoucher(Request $request) {

        $model = new VoucherShop();
        $data = $request->all();

        $checks = $model->validator($data);
        if ($checks->fails()) {
            return $this->returnError($checks->errors()->first(), NULL, true);
        }
        $voucher = $model->create($data);

        return $this->returnSuccess(__($this->successMsg['voucher.share']), $voucher);
    }

    public function addServicesToVoucher(Request $request) {

        DB::beginTransaction();
        try {
            $model = new UserVoucher();
            foreach ($request->services as $key => $service) {

                $service['user_voucher_price_id'] = $request->user_voucher_price_id;
                $checks = $model->validator($service);
                if ($checks->fails()) {
                    return $this->returnError($checks->errors()->first(), NULL, true);
                }
                $voucherServices[] = UserVoucher::create($service);
            }
            DB::commit();
            return $this->returnSuccess(__($this->successMsg['voucher.add.services']), $voucherServices);
            
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function purchaseVoucher(Request $request) {

        $model = new UserVoucherPrice();
        $data = $request->all();
        $voucher = Voucher::find($request->voucher_id);
        $data['total_value'] = $voucher->price;
        $data['purchase_date'] = Carbon::now()->format('Y-m-d');

        $checks = $model->validator($data);
        if ($checks->fails()) {
            return $this->returnError($checks->errors()->first(), NULL, true);
        }

        $purchaseVoucher = $model->create($data);
        return $this->returnSuccess(__($this->successMsg['voucher.purchase']), $purchaseVoucher);
    }

    public function addPack(Request $request) {

        DB::beginTransaction();
        try {
            $model = new Pack();
            $data = $request->all();
            $data['number'] = generateRandomString();
            $data['expired_date'] = Carbon::createFromTimestampMs($data['expired_date']);
            
            $checks = $model->validator($data);
            if ($checks->fails()) {
                return $this->returnError($checks->errors()->first(), NULL, true);
            }

            /* For profile Image */
            if ($request->hasFile('image')) {
                $checkImage = $model->validateImage($data);
                if ($checkImage->fails()) {
                    unset($data['image']);

                    return $this->returnError($checkImage->errors()->first(), NULL, true);
                }
                $fileName = time() . '.' . $data['image']->getClientOriginalExtension();
                $storeFile = $data['image']->storeAs($model->profilePhotoPath, $fileName, $model->fileSystem);

                if ($storeFile) {
                    $data['image'] = $fileName;
                }
            }
            $pack = $model->create($data);
            $packServiceModel = new PackService();
            if(!empty($data['massage_id'])) {
                foreach ($data['massage_id'] as $key => $value) {
                    $service = [
                        'massage_id' => $value,
                        'massage_timing_id' => $data['massage_timing_id'][$key],
                        'pack_id' => $pack->id
                    ];
                    $checks = $packServiceModel->validator($service);
                    if ($checks->fails()) {
                        return $this->returnError($checks->errors()->first(), NULL, true);
                    }
                    $packServiceModel->create($service);
                }
            }
            if(!empty($data['therapy_id'])) {
                foreach ($data['therapy_id'] as $key => $value) {
                    $service = [
                        'therapy_id' => $value,
                        'therapy_timing_id' => $data['therapy_timing_id'][$key],
                        'pack_id' => $pack->id
                    ];
                    $checks = $packServiceModel->validator($service);
                    if ($checks->fails()) {
                        return $this->returnError($checks->errors()->first(), NULL, true);
                    }
                    $packServiceModel->create($service);
                }
            }
            DB::commit();
            return $this->returnSuccess(__($this->successMsg['pack.add']), $pack);
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollback();
            throw $e;
        }
    }
    
    public function sharePack(Request $request) {

        $model = new PackShop();
        $data = $request->all();

        $checks = $model->validator($data);
        if ($checks->fails()) {
            return $this->returnError($checks->errors()->first(), NULL, true);
        }
        $pack = $model->create($data);

        return $this->returnSuccess(__($this->successMsg['pack.share']), $pack);
    }
    
    public function getPacks(){
        
        $packs = Pack::with('services')->get();
        return $this->returnSuccess(__($this->successMsg['pack.get']), $packs);
    }
    
    public function purchasePack(Request $request) {
        
        $model = new UserPack();
        $data = $request->all();
        $data['purchase_date'] = Carbon::now()->format('Y-m-d');

        $checks = $model->validator($data);
        if ($checks->fails()) {
            return $this->returnError($checks->errors()->first(), NULL, true);
        }
        
        $purchasePack = $model->create($data);
        return $this->returnSuccess(__($this->successMsg['pack.purchase']), $purchasePack);
    }
}
