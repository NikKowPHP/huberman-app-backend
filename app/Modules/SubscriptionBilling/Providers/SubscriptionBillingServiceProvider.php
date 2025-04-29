<?php

namespace App\Modules\SubscriptionBilling\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\SubscriptionBilling\Contracts\AppStoreServerApiClientInterface;
use App\Modules\SubscriptionBilling\Contracts\SubscriptionServiceInterface;
use App\Modules\SubscriptionBilling\Services\SubscriptionService;
use GuzzleHttp\Client;
use App\Modules\SubscriptionBilling\Services\AppleSubscriptionService;

class SubscriptionBillingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(SubscriptionServiceInterface::class, SubscriptionService::class);
        $this->app->bind(AppStoreServerApiClientInterface::class, function ($app) {
            return new AppStoreServerApiClient(new Client());
        });
        $this->app->bind(AppleSubscriptionService::class, AppleSubscriptionService::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
