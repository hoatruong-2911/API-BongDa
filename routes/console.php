<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');



Schedule::command('bookings:cancel-expired-deposits')->everyFiveMinutes();

Schedule::call(function () {
    \App\Http\Controllers\Api\OrderController::cancelExpiredOrders();
})->everyFiveMinutes();
