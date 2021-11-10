<?php

namespace App\Jobs;

use App\Notification as modalNotification;
use Log;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;

class BaseNotification extends Job
{
    protected $deviceToken;

    protected $title;

    protected $description;

    protected $dataPayload;

    protected $downstreamResponse;

    protected $sendTo;

    protected $sendFrom;

    public function __construct(string $deviceToken, string $title, string $description = NULL, array $dataPayload = [], string $sendTo = '0', string $sendFrom = '0')
    {
        $this->title         = $title;

        $this->description   = $description;

        $this->dataPayload   = $dataPayload;

        $this->sendTo        = $sendTo;

        $this->deviceToken   = $deviceToken;

        $this->sendFrom      = $sendFrom;
    }

    public function storeNotification()
    {
        $notification = [
            'title'         => $this->title,
            'payload'       => json_encode($this->dataPayload),
            'message'       => $this->description,
            'device_token'  => $this->deviceToken,
            'is_success'    => modalNotification::IS_SUCCESS,
            'apns_id'       => env('FCM_SERVER_KEY'),
            'error_infos'   => json_encode($this->downstreamResponse->tokensWithError()),
            'send_to'       => $this->sendTo,
            'send_from'     => $this->sendFrom
        ];

        $create = modalNotification::create($notification);

        if (!$create) {
            Log::error(json_encode(['Notification store error logs : ' => ['create' => $create, 'notification' => $notification]]));
        }
    }

    protected function send()
    {
        if (empty($this->deviceToken)) {
            return false;
        }

        if (empty($this->title)) {
            return false;
        }

        $optionBuilder          = new OptionsBuilder();

        $optionBuilder->setTimeToLive(60 * 20);

        $notificationBuilder    = new PayloadNotificationBuilder($this->title);

        if (!empty($this->description)) {
            $notificationBuilder->setBody($this->description);
        }

        $notificationBuilder->setSound('default');

        $option                 = $optionBuilder->build();

        $notification           = $notificationBuilder->build();

        $data                   = [];

        if (!empty($this->dataPayload)) {
            $dataBuilder = new PayloadDataBuilder();

            $dataBuilder->addData($this->dataPayload);

            $data = $dataBuilder->build();
        }

        $this->downstreamResponse = FCM::sendTo($this->deviceToken, $option, $notification, $data);

        $this->storeNotification();
    }
}
