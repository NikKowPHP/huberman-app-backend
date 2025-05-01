<?php

namespace App\Modules\TrackingService\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\TrackingService\Contracts\TrackingServiceInterface;
use App\Modules\TrackingService\Services\TrackingService;

class TrackingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(
            TrackingServiceInterface::class,
            TrackingService::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // No boot actions needed for this service binding
    }
}