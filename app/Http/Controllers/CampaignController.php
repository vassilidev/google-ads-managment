<?php

namespace App\Http\Controllers;

use App\Http\Requests\Api\UpdateAdRequest;
use App\Models\Ad;
use App\Services\GoogleAdsService;
use Google\ApiCore\ApiException;
use Illuminate\Http\JsonResponse;

class CampaignController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $ads = Ad::all();

        return response()->json($ads);
    }

    /**
     * Display the specified resource.
     */
    public function show(Ad $ad): JsonResponse
    {
        $ad->load('activities');

        return response()->json($ad);
    }

    /**
     * Update the specified resource in storage.
     * @throws ApiException
     */
    public function update(UpdateAdRequest $request, Ad $ad): JsonResponse
    {
        $validatedData = $request->validated();

        $service = app(GoogleAdsService::class);

        $campaign = $service->updateCampaign($ad->ad_id, $validatedData);

        if ($campaign) {
            return response()->json($campaign);
        }

        return response()->json('', 400);
    }
}
