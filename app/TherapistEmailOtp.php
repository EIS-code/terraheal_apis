<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use DB;

class TherapistEmailOtp extends BaseModel
{
    protected $fillable = [
        'otp',
        'email',
        'is_send',
        'is_verified',
        'therapist_id',
    ];

    public function validator(array $data, $id = false, $isUpdate = false)
    {
        if ($isUpdate === true && !empty($id)) {
            $emailValidator = ['unique:therapist_email_otps,email,' . $id];
        } else {
            $emailValidator = ['unique:therapist_email_otps'];
        }

        return Validator::make($data, [
            'otp'          => ['max:4'],
            // 'email'        => array_merge(['required', 'string', 'email', 'max:255'], $emailValidator),
            'email'        => ['required', 'email'],
            'is_verified'  => ['in:0,1'],
            'is_send'      => ['in:0,1'],
            'therapist_id' => ['required', 'integer']
        ]);
    }
    
     public function validate(array $data)
    {
        $validator = $this->validator($data);
        if ($validator->fails()) {
            return ['is_validate' => 0, 'msg' => $validator->errors()->first()];
        }

        return ['is_validate' => 1, 'msg' => ''];
    }

    public function updateOtp(int $id, array $data)
    {
        $update = false;

        /* TODO: For check user availability. */
        $getOtpInfo = $this->where('therapist_id', $id)->first();
        if (empty($getOtpInfo)) {
            return ['isError' => true, 'message' => __("We didn't send OTP for this user before.")];
        }

        DB::beginTransaction();

        try {
            $validator = $this->validator($data);
            if ($validator->fails()) {
                return ['isError' => true, 'message' => __($validator->errors()->first())];
            }

            $updateData = [
                'email'       => $data['email'],
                'otp'         => $data['otp'],
                'is_send'     => (!empty($data['is_send']) ? $data['is_send'] : '0'),
                'is_verified' => (isset($data['is_verified'])) ? $data['is_verified'] : $getOtpInfo->is_verified
            ];

            $update     = $this->where(['therapist_id' => $id, 'email' => $getOtpInfo->email])->update($updateData);
        } catch (Exception $e) {
            DB::rollBack();
        }

        if ($update) {
            DB::commit();

            return ['isError' => 'false', 'message' => __('Therapist email otp updated successfully !')];
        } else {
            return ['isError' => 'false', 'message' => __('Something went wrong.')];
        }
    }

    public function setIsVerified(int $id, string $isVarified = '0')
    {
        $isVarified = (!in_array($isVarified, ['0', '1'])) ? '0' : $isVarified;

        return $this->where('id', $id)->update(['is_verified' => $isVarified]);
    }
}
