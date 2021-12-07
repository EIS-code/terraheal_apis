<?php

namespace App;

use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends BaseModel
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title', 'message', 'payload', 'device_token', 'is_success', 'apns_id', 'error_infos', 'send_to', 'send_from', 'is_read', 'created_at', 'updated_at'
    ];

    /**
     * The attributes that should be appends to model object.
     *
     * @var array
     */
    public $appends = ['total_notifications', 'total_read_notifications', 'total_unread_notifications'];

    const IS_READ   = '1';
    const IS_UNREAD = '0';
    public $isRead = [
        self::IS_READ   => 'Read',
        self::IS_UNREAD => 'Unread'
    ];

    const IS_SUCCESS = '1';
    const IS_NOT_SUCCESS = '0';
    public $isSuccess = [
        self::IS_SUCCESS => 'Yes',
        self::IS_NOT_SUCCESS => 'No'
    ];

    const SEND_TO_NONE          = '0';
    const SEND_TO_SUPERADMIN    = '1';
    const SEND_TO_CLIENT_APP    = '2';
    const SEND_TO_MANAGER_APP   = '3';
    const SEND_TO_MANAGER_EXE   = '4';
    const SEND_TO_SHOP_APP      = '5';
    const SEND_TO_SHOP_EXE      = '6';
    public $sendTo = [
        self::SEND_TO_NONE          => 'None',
        self::SEND_TO_SUPERADMIN    => 'Superadmin',
        self::SEND_TO_CLIENT_APP    => 'Client APP',
        self::SEND_TO_MANAGER_APP   => 'Manager APP',
        self::SEND_TO_MANAGER_EXE   => 'Manager EXE',
        self::SEND_TO_SHOP_APP      => 'Shop APP',
        self::SEND_TO_SHOP_EXE      => 'Shop EXE'
    ];

    const SEND_FROM_NONE          = '0';
    const SEND_FROM_SUPERADMIN    = '1';
    const SEND_FROM_CLIENT_APP    = '2';
    const SEND_FROM_MANAGER_APP   = '3';
    const SEND_FROM_MANAGER_EXE   = '4';
    const SEND_FROM_SHOP_APP      = '5';
    const SEND_FROM_SHOP_EXE      = '6';
    public $sendFrom = [
        self::SEND_FROM_NONE          => 'None',
        self::SEND_FROM_SUPERADMIN    => 'Superadmin',
        self::SEND_FROM_CLIENT_APP    => 'Client APP',
        self::SEND_FROM_MANAGER_APP   => 'Manager APP',
        self::SEND_FROM_MANAGER_EXE   => 'Manager EXE',
        self::SEND_FROM_SHOP_APP      => 'Shop APP',
        self::SEND_FROM_SHOP_EXE      => 'Shop EXE'
    ];

    public function validator(array $data, $returnBoolsOnly = false)
    {
        $validator = Validator::make($data, [
            'device_token'  => ['required', 'string'],
            'send_to'       => ['nullable', 'in:' . implode(",", array_keys($this->sendTo))],
            'send_from'     => ['nullable', 'in:' . implode(",", array_keys($this->sendFrom))],
            'is_success'    => ['nullable', 'in:' . implode(",", array_keys($this->isSuccess))],
            'is_read'       => ['nullable', 'in:' . implode(",", array_keys($this->isRead))]
        ]);

        if ($returnBoolsOnly === true) {
            if ($validator->fails()) {
                \Session::flash('error', $validator->errors()->first());
            }

            return !$validator->fails();
        }

        return $validator;
    }

    public function getIsReadAttribute($value)
    {
        if (empty($value) || !array_key_exists($value, $this->isRead)) {
            return $value;
        }

        return $this->isRead[$value];
    }

    public function getIsSuccessAttribute($value)
    {
        if (empty($value) || !array_key_exists($value, $this->isSuccess)) {
            return $value;
        }

        return $this->isSuccess[$value];
    }

    public function notifications($isAll = false, $isRead = self::IS_UNREAD, $isSuccess = self::IS_SUCCESS)
    {
        if ($isAll) {
            return $this->whereIn('is_read', [self::IS_READ, self::IS_UNREAD]);
        } else {
            return $this->where('is_read', $isRead)->where('is_success', $isSuccess);
        }
    }

    public function getTotalReadNotificationsAttribute()
    {
        return $this->notifications(false, self::IS_READ)->count();
    }

    public function getTotalNotificationsAttribute()
    {
        return $this->notifications(true)->count();
    }

    public function getTotalUnreadNotificationsAttribute()
    {
        return $this->notifications()->count();
    }
    
    public function read($id) {
        
        $notification = $this->find($id);
        if(empty($notification)) {
            return ['isError' => true, 'message' => 'Notification not found !'];
        }
        $notification->update(['is_read' => self::IS_READ]);
        return $notification;
    }
}
