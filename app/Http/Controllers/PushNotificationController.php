<?php

namespace App\Http\Controllers;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\WebPushConfig;
use Kreait\Firebase\Messaging\Notification;

class PushNotificationController extends Controller
{
    public function sendPushNotification()
    {
        // $firebaseCredentialsPath = config('firebase.credentials_path');
        $fullPath = base_path("config/firebase_credentials.json");

        $deviceToken = 'd9YrP-btyq6sZmaDs936jA:APA91bHOM2F-Ik5YWOKKpKeo1Y22pCSSmklkSH079ot0BOt1FWW9kJHaTNMoQ7MqFpvZFxfe1baMSYPFVAwb1Hi5iZSBf3Xyq04YXnT4mqKYlWvbS7IBxesxYw9QZinpqJ5HTs5S4Nzv';
        $deviceTokenIOS = 'e1Zv3Z7UzTynQuK3VFnKPz:APA91bGG-emLpB6F1DtqiDy9Wi6pzI-H0hkXhUfo7zcrGgnLaLq4giLh-htHNq9bq0R79VvKB0fqwmmpDNRlJMYBjytvRLI9NpUzgJ3VceEeDkVNWeqb3eGRfjM4Byf1Lvy1MSrAce-d';
        $deviceTokenMZ = 'fwp4voqGF0ebS3Eb3rtP5R:APA91bG1FjQRYULIlGDNgZoQy6xCcQ2VGmCxW-QX8GcgvbTuiYBuoM-7BzM-RLP77HHQA__OovFLLVNj4yoqxI5M187f6tAyQ058OlfsUK7oMP3xGPL8rAXFOA-EfnE1nmeDw8Ctv1zS';

        $title = 'New Issue';
        $body = "A new issue has been reported to the MIS Department.";
        $imageUrl = 'https://picsum.photos/400/200';


        $notification = Notification::fromArray([
            'title' => $title,
            'body' => $body,
            'icon' => $imageUrl,
            'click_action' => 'https://www.facebook.com',
            'image' => $imageUrl,
        ]);


        // $messaging->send($message);

        // return;
        $firebase = (new Factory)
            ->withServiceAccount($fullPath);

        $messaging = $firebase->createMessaging();

        $subscribed[] = $deviceTokenIOS;
        $subscribed[] = $deviceTokenMZ;

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

        $message = CloudMessage::new()->withNotification($notification)
            // ->withData(['click_action' => 'https://www.facebook.com'])
            ->withDefaultSounds()
            ->withApnsConfig(
                ApnsConfig::new()
                    ->withSound('bingbong.aiff')
                    ->withBadge(1)
            );; // Any instance of Kreait\Messaging\Message

        $sendReport = $messaging->sendMulticast($message, $subscribed);


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
