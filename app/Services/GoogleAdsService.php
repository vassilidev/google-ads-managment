<?php

namespace App\Services;

use App\Jobs\HandleCampaignLogs;
use App\Models\Ad;
use Google\Ads\GoogleAds\Lib\OAuth2TokenBuilder;
use Google\Ads\GoogleAds\Lib\V15\GoogleAdsClient;
use Google\Ads\GoogleAds\Lib\V15\GoogleAdsClientBuilder;
use Google\Ads\GoogleAds\Lib\V15\GoogleAdsServerStreamDecorator;
use Google\Ads\GoogleAds\Util\FieldMasks;
use Google\Ads\GoogleAds\Util\V15\ResourceNames;
use Google\Ads\GoogleAds\V15\Common\MaximizeConversions;
use Google\Ads\GoogleAds\V15\Resources\Campaign;
use Google\Ads\GoogleAds\V15\Resources\CampaignBudget;
use Google\Ads\GoogleAds\V15\Services\CampaignBudgetOperation;
use Google\Ads\GoogleAds\V15\Services\CampaignOperation;
use Google\Ads\GoogleAds\V15\Services\GoogleAdsRow;
use Google\Ads\GoogleAds\V15\Services\MutateCampaignBudgetsRequest;
use Google\Ads\GoogleAds\V15\Services\MutateCampaignsRequest;
use Google\ApiCore\ApiException;

class GoogleAdsService
{
    public GoogleAdsClient $googleAdsClient;

    public int $customerId = 2759958378;

    private $googleAdsServiceClient;
    private $googleAdsClientV2;

    public function __construct()
    {
        $oAuth2Credential = (new OAuth2TokenBuilder())
            ->fromFile(base_path('ads.ini'))
            ->build();

        $this->googleAdsClient = (new GoogleAdsClientBuilder())
            ->fromFile(base_path('ads.ini'))
            ->withOAuth2Credential($oAuth2Credential)
            ->usingGapicV2Source(false)
            ->build();

        $this->googleAdsClientV2 = (new GoogleAdsClientBuilder())
            ->fromFile(base_path('ads.ini'))
            ->withOAuth2Credential($oAuth2Credential)
            ->usingGapicV2Source(true)
            ->build();


        $this->googleAdsServiceClientv1 = $this->googleAdsClient->getGoogleAdsServiceClient();
        $this->googleAdsServiceClientV2 = $this->googleAdsClient->getGoogleAdsServiceClient();
    }

    /**
     * @throws ApiException
     */
    public function getCampaigns(): bool
    {
        /** @var GoogleAdsServerStreamDecorator $stream */
        $stream = $this->googleAdsServiceClientV2->searchStream($this->customerId, $this->getCampaignQuery());

        foreach ($stream->iterateAllElements() as $googleAdsCampaignRow) {
            $this->handleLogs($googleAdsCampaignRow->getCampaign());
        }

        return true;
    }

    public function handleLogs($campaign): void
    {
        $budgetId = str_replace("customers/" . $this->customerId . "/campaignBudgets/", "", $campaign->getCampaignBudget());

        $budgets = $this->googleAdsServiceClientv1->searchStream($this->customerId, $this->getCampaignBudgetQuery($budgetId));

        foreach ($budgets->iterateAllElements() as $campaignBudgetRow) {
            /** @var GoogleAdsRow $campaignBudgetRow */
            $this->handleCampaignLog($campaign, $campaignBudgetRow);
        }
    }

    private function getCampaignQuery(?string $campaignId = ''): string
    {
        $where = '';

        if ($campaignId) {
            $where .= 'WHERE campaign.id = ' . $campaignId;
        }

        return 'SELECT 
                      campaign.id, 
                      campaign.name, 
                      campaign.maximize_conversions.target_cpa_micros, 
                      campaign.campaign_budget 
                    FROM 
                      campaign ' . $where;
    }

    private function getCampaignBudgetQuery($budgetId): string
    {
        return 'SELECT 
                  segments.conversion_action, 
                  metrics.value_per_conversion, 
                  campaign_budget.amount_micros 
                FROM 
                  campaign_budget 
                WHERE 
                  campaign_budget.id = ' . $budgetId;
    }

    private function handleCampaignLog(Campaign $campaign, $budget): void
    {
        Ad::updateOrCreate([
            'ad_id' => $campaign->getId(),
        ], [
            'name'       => $campaign->getName(),
            'target_cpa' => $campaign->getMaximizeConversions()?->getTargetCpaMicros() / 1000000,
            'budget'     => $budget->getCampaignBudget()->getAmountMicros() / 1000000,
        ]);
    }

    /**
     * Update a specific campaign with modified values.
     *
     * @param int $campaignId ID of the campaign to update.
     * @param array $data
     * @return mixed
     * @throws ApiException
     */
    public function updateCampaign(int $campaignId, array $data = []): mixed
    {
        $googleAdCampaign = $this->find($campaignId);

        if (!$googleAdCampaign) {
            return false;
        }

        $hasOperation = false;

        $campaignServiceClient = $this->googleAdsClientV2->getCampaignServiceClient();

        $campaign = new Campaign();
        $campaign->setResourceName(ResourceNames::forCampaign($this->customerId, $campaignId));

        if (!empty($data['name'])) {
            $hasOperation = true;

            $campaign->setName($data['name']);
        }

        if (!empty($data['cpa_target'])) {
            $hasOperation = true;

            $campaign->setMaximizeConversions((new MaximizeConversions())->setTargetCpaMicros($data['cpa_target'] * 1000000));
        }

        if (!empty($data['budget'])) {
            $budget = new CampaignBudget();

            $budget->setResourceName($googleAdCampaign->getCampaignBudget());
            $budget->setAmountMicros($data['budget'] * 1_000_000);

            $budgetOperation = new CampaignBudgetOperation();
            $budgetOperation->setUpdate($budget);
            $budgetOperation->setUpdateMask(FieldMasks::allSetFieldsOf($budget));

            $campaignBudgetServiceClient = $this->googleAdsClientV2->getCampaignBudgetServiceClient();
            $campaignBudgetServiceClient->mutateCampaignBudgets(
                MutateCampaignBudgetsRequest::build($this->customerId, [$budgetOperation])
            );
        }

        if ($hasOperation) {
            $campaignOperation = new CampaignOperation();

            $campaignOperation->setUpdate($campaign);
            $campaignOperation->setUpdateMask(FieldMasks::allSetFieldsOf($campaign));

            return $campaignServiceClient->mutateCampaigns(
                MutateCampaignsRequest::build($this->customerId, [$campaignOperation])
            );
        }

        return null;
    }

    /**
     * @throws ApiException
     */
    public function find(mixed $campaignId = ''): ?Campaign
    {
        $stream = $this->googleAdsServiceClientv1->searchStream($this->customerId, $this->getCampaignQuery($campaignId));

        foreach ($stream->iterateAllElements() as $googleAdsRow) {
            $campaign = $googleAdsRow->getCampaign();
        }

        return $campaign ?? null;
    }
}