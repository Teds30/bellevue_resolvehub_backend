<?php

use App\Http\Controllers\PushNotificationController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('test', function () {
    event(new \App\Events\MyEvent('hello world'));
    // return $newEvent;
    return "Event has been sent!";
});

Route::get('/send-notification', [PushNotificationController::class, 'sendPushNotification']);
