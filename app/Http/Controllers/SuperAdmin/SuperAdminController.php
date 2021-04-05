<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use App\Voucher;
use App\VoucherShop;
use App\UserVoucher;
use App\UserVoucherPrice;
use Carbon\Carbon;

class SuperAdminController extends BaseController {

    public $successMsg = [
        'voucher.add' => 'Voucher created successfully!',
        'voucher.update' => 'Voucher updated successfully!',
        'voucher.get' => 'Vouchers found successfully!',
        'voucher.share' => 'Voucher shared successfully!',
        'voucher.purchase' => 'Voucher purchased successfully!',
        'voucher.add.services' => 'Services added to voucher successfully!',
    ];

    public function addVoucher(Request $request) {

        $model = new Voucher();
        $data = $request->all();
        $data['number'] = generateRandomString();

        $checks = $model->validator($data);
        if ($checks->fails()) {
            return $this->returnError($checks->errors()->first(), NULL, true);
        }
        $voucher = $model->create($data);

        return $this->returnSuccess(__($this->successMsg['voucher.add']), $voucher);
    }
    public function updateVoucher(Request $request) {

        $voucher = Voucher::find($request->voucher_id);
        $voucher->update($request->all());

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

        $model = new UserVoucher();
        $data = $request->all();
        $checks = $model->validator($data);
        if ($checks->fails()) {
            return $this->returnError($checks->errors()->first(), NULL, true);
        }

        foreach ($data['services'] as $key => $service) {

            $service['user_voucher_price_id'] = $data['user_voucher_price_id'];
            $voucherServices[] = UserVoucher::create($service);
        }
        return $this->returnSuccess(__($this->successMsg['voucher.add.services']), $voucherServices);
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

}
