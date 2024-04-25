<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\NotificationService;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\WebPushConfig;
use Kreait\Firebase\Messaging\Notification;

class PushNotificationController extends Controller
{

    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function newTask()
    {

        $target = User::get()->where('id', 1)->first();

        $targetDevices = $target->deviceTokens;

        // Decode the JSON string into a PHP array
        $data = json_decode($targetDevices, true);

        // Extract tokens using Laravel collection methods
        $tokens = collect($data)->pluck('token')->toArray();


        $args['title'] = "New Issue Reported";
        $args['body'] = "Network: No Internet";
        $args['targetDevices'] = $tokens;

        $this->notificationService->sendPushNotification($args, true);
    }
}
