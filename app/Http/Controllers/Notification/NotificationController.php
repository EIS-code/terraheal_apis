<?php

namespace App\Http\Controllers\Notification;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Http\Request;
use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use FCM;


class NotificationController extends BaseController
{
   public function sendNotification(Request $request) {

        $optionBuilder = new OptionsBuilder();
        $optionBuilder->setTimeToLive(60 * 20);

        $notificationBuilder = new PayloadNotificationBuilder('Hello');
        $notificationBuilder->setBody('This is my first notification')
                ->setSound('default');

        $dataBuilder = new PayloadDataBuilder();
        $dataBuilder->addData(['a_data' => 'my_data']);

        $option = $optionBuilder->build();
        $notification = $notificationBuilder->build();
        $data = $dataBuilder->build();

        $token = "dZtn96Kcsk5wrnIzEF5L7L:APA91bG5RKmv7FeMzKILKfmFuaa5nAPLqvpzfBiUxPrKeTy7QNHRc6Y23nNoQ0ySv8T_W9KxqOoRnHfRMsjqWh83oYvglnFNWlri4z-nSTmq3n-XqjYo2QRIgU-TS1-TGNIr4J3aIYcV";

        $downstreamResponse = FCM::sendTo($token, $option, $notification, $data);

        $downstreamResponse->numberSuccess();
        $downstreamResponse->numberFailure();
        $downstreamResponse->numberModification();

        // return Array - you must remove all this tokens in your database
        $downstreamResponse->tokensToDelete();

        // return Array (key : oldToken, value : new token - you must change the token in your database)
        $downstreamResponse->tokensToModify();

        // return Array - you should try to resend the message to the tokens in the array
        $downstreamResponse->tokensToRetry();

        // return Array (key:token, value:error) - in production you should remove from your database the tokens
        $downstreamResponse->tokensWithError();
        dd($downstreamResponse);
    }
}
