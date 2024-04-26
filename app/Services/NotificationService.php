<?php


namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\WebPushConfig;
use Kreait\Firebase\Messaging\Notification;


class NotificationService
{
    public function sendPushNotification($args, $multiple = false)
    {

        // $firebaseCredentialsPath = config('firebase.credentials_path');
        $fullPath = base_path("config/firebase_credentials.json");

        $notification = Notification::fromArray([
            'title' => $args['title'],
            'body' => $args['body'],
            'icon' => 'https://www.thebellevuemanila.com/wp-content/uploads/2022/08/fav.png',
            'click_action' => 'https://www.facebook.com',
            'link' => 'https://www.google.com',
            // 'image' => 'https://www.thebellevuemanila.com/wp-content/uploads/2022/08/fav.png',
        ]);


        // $messaging->send($message);

        // return;
        $firebase = (new Factory)
            ->withServiceAccount($fullPath);

        $messaging = $firebase->createMessaging();

        // $subscribed[] = $deviceToken;
        // $result = $messaging->subscribeToTopic('tasks', $subscribed);

        // $message = CloudMessage::withTarget('token', $deviceTokenIOS)
        //     ->withNotification($notification)
        //     // ->withData(['click_action' => 'https://www.facebook.com'])
        //     ->withDefaultSounds()
        //     ->withApnsConfig(
        //         ApnsConfig::new()
        //             ->withSound('bingbong.aiff')
        //             ->withBadge(1)
        //     );


        if ($multiple) {
            // $message = CloudMessage::new()->withNotification($notification)
            //     // ->withData(['click_action' => 'https://www.facebook.com'])
            //     ->withDefaultSounds()
            //     ->withApnsConfig(
            //         ApnsConfig::new()
            //             ->withSound('bingbong.aiff')
            //             ->withBadge(1)
            //     );; // Any instance of Kreait\Messaging\Message

            // $sendReport = $messaging->sendMulticast($message, $args['targetDevices']);
            $config = WebPushConfig::fromArray([
                'notification' => [
                    'title' => $args['title'],
                    'body' => $args['body'],
                    'icon' => 'https://www.thebellevuemanila.com/wp-content/uploads/2022/08/fav.png',
                    'link' => $args['link'],
                ],
                'fcm_options' => [
                    'link' => $args['link'],
                ],
            ]);

            $message = CloudMessage::new()->withWebPushConfig($config);
        } else {

            $message = CloudMessage::withTarget('token', $args['targetDevice'])
                ->withNotification($notification)
                // ->withData(['click_action' => 'https://www.facebook.com'])
                ->withDefaultSounds()
                ->withApnsConfig(
                    ApnsConfig::new()
                        ->withSound('bingbong.aiff')
                        ->withBadge(1)
                );

            $sendReport = $messaging->send($message);
        }

        // $message = CloudMessage::fromArray([
        //     'notification' => [
        //         'title' => 'Hello from Firebase!',
        //         'body' => 'This is a test notification.'
        //     ],
        //     'topic' => 'global'
        // ]);

        // $messaging->send($message);

        return response()->json(['message' => 'Push notification sent successfully']);
    }
}
