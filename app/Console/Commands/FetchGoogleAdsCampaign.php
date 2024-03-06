<?php

namespace App\Console\Commands;

use App\Services\GoogleAdsService;
use Google\ApiCore\ApiException;
use Illuminate\Console\Command;

class FetchGoogleAdsCampaign extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-google-ads-campaign';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch and update Ads in the Database';

    /**
     * Execute the console command.
     * @throws ApiException
     */
    public function handle(): int
    {
        $this->info('Fetching Ads from Google SDK API...');

        app(GoogleAdsService::class)->getCampaigns();

        $this->info('Fetching Done !');

        return self::SUCCESS;
    }
}
