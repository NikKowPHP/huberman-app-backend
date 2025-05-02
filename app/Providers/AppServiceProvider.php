<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\OfflineDataServiceInterface;
use App\Services\OfflineDataService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(OfflineDataServiceInterface::class, OfflineDataService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
