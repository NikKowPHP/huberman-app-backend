<?php

namespace App\Modules\TrackingService\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\TrackingService\Contracts\TrackingServiceInterface;
use App\Modules\TrackingService\Services\TrackingService;

class TrackingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(TrackingServiceInterface::class, TrackingService::class);
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
