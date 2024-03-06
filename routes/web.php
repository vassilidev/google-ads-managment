<?php

use Illuminate\Support\Facades\Route;

Route::fallback(function () {
    return to_route('filament.admin.auth.login');
});

Route::get('startCron', static function () {
    activity('cron')->log('Start the CRON ' . now());

    Artisan::call('schedule:run');
    Artisan::call('queue:work --stop-when-empty --queue=high,default');

    activity('cron')->log('End the CRON ' . now());

    return response('', 200);
});