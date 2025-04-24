<?php
// File: app/Modules/SubscriptionBilling/Providers/RouteServiceProvider.php

namespace App\Modules\SubscriptionBilling\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
// use Illuminate\Http\Request; // Uncomment if using RateLimiter
// use Illuminate\Support\Facades\RateLimiter; // Uncomment if using RateLimiter

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The module namespace to assume when generating URLs to actions.
     * @var string
     */
    // protected $moduleNamespace = 'App\Modules\SubscriptionBilling\Http\Controllers';

    /**
     * Called before routes are registered.
     */
    public function boot(): void
    {
        // $this->configureRateLimiting();

        $this->routes(function () {
            $apiRoutesPath = base_path('app/Modules/SubscriptionBilling/routes/api.php');
            if (file_exists($apiRoutesPath)) {
                Route::middleware('api')
                    ->prefix('api/v1') // Adjust prefix (e.g., 'api/v1/billing')
                    ->group($apiRoutesPath);
                    // Note: Webhook routes within api.php might need CSRF exemption.
            }

            $webRoutesPath = base_path('app/Modules/SubscriptionBilling/routes/web.php');
            if (file_exists($webRoutesPath)) {
                Route::middleware('web')
                    // ->namespace($this->moduleNamespace)
                    ->group($webRoutesPath);
            }
        });
    }

    /**
     * Configure the rate limiters.
     */
    // protected function configureRateLimiting(): void
    // {
    //     // ...
    // }
}