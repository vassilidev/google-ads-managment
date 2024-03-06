<?php

use App\Http\Controllers\CampaignController;
use App\Models\Ad;
use App\Services\GoogleAdsService;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('fetch-campaigns', function () {
   return \Illuminate\Support\Facades\Artisan::call('app:fetch-google-ads-campaign');
});

Route::apiResource('ads', CampaignController::class);

Route::get('/ping', function () {
    $randomAd = Ad::inRandomOrder()->first();

    return app(GoogleAdsService::class)->find($randomAd?->ad_id)
        ? response()->json('Pong !')
        : response()->json('KO', 503);
})->withoutMiddleware(AuthenticateWithBasicAuth::class);