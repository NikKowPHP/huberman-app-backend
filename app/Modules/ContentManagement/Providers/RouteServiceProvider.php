<?php
// File: app/Modules/ContentManagement/Providers/RouteServiceProvider.php

namespace App\Modules\ContentManagement\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
// use Illuminate\Http\Request; // Uncomment if using RateLimiter
// use Illuminate\Support\Facades\RateLimiter; // Uncomment if using RateLimiter

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The module namespace.
     * @var string
     */
    // protected $moduleNamespace = 'App\Modules\ContentManagement\Http\Controllers';

    /**
     * Called before routes are registered.
     */
    public function boot(): void
    {
        // $this->configureRateLimiting();

        $this->routes(function () {
            $apiRoutesPath = base_path('app/Modules/ContentManagement/routes/api.php');
            if (file_exists($apiRoutesPath)) {
                Route::middleware('api')
                    ->prefix('api/v1') // Likely keep global v1 prefix for /protocols, /episodes
                    ->group($apiRoutesPath);
            }

            $webRoutesPath = base_path('app/Modules/ContentManagement/routes/web.php');
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