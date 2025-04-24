<?php
// File: app/Modules/UserManagement/Providers/RouteServiceProvider.php

namespace App\Modules\UserManagement\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
// use Illuminate\Http\Request; // Uncomment if using RateLimiter
// use Illuminate\Support\Facades\RateLimiter; // Uncomment if using RateLimiter

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The module namespace to assume when generating URLs to actions.
     * Adjust if your controllers are namespaced differently within the module.
     * @var string
     */
    // protected $moduleNamespace = 'App\Modules\UserManagement\Http\Controllers';

    /**
     * Called before routes are registered.
     * Register any model bindings or pattern based filters.
     */
    public function boot(): void
    {
        // $this->configureRateLimiting(); // Uncomment if rate limiting specific to this module is needed

        $this->routes(function () {
            // Define API routes for the module
            // Ensure the path to the routes file is correct
            $apiRoutesPath = base_path('app/Modules/UserManagement/routes/api.php');
            if (file_exists($apiRoutesPath)) {
                Route::middleware('api')
                    ->prefix('api/v1') // Adjust prefix as needed (e.g., 'api/v1/user')
                    ->group($apiRoutesPath);
            }

            // Define Web routes for the module (if any)
            // Ensure the path to the routes file is correct
            $webRoutesPath = base_path('app/Modules/UserManagement/routes/web.php');
            if (file_exists($webRoutesPath)) {
                Route::middleware('web')
                    // ->namespace($this->moduleNamespace) // Uncomment if using namespace property
                    ->group($webRoutesPath);
            }
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    // protected function configureRateLimiting(): void
    // {
    //     RateLimiter::for('api', function (Request $request) {
    //         return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
    //     });
    // }
}