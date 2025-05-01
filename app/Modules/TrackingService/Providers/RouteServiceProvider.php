<?php

namespace App\Modules\TrackingService\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request; // Add this use statement
use Illuminate\Cache\RateLimiting\Limit; // Add this use statement
use Illuminate\Support\Facades\RateLimiter; // Add this use statement


class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home'; // Or wherever your app's home is

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        // No rate limiting configuration needed here if done globally

        $this->routes(function () {
            Route::middleware('api') // Apply general API middleware
                ->prefix('api/v1/tracking') // Apply the version and module prefix
                ->group(base_path('app/Modules/TrackingService/routes/api.php'));
        });
    }

     /**
      * Configure the rate limiters for the application.
      * (Optional: Define specific rate limiters if needed for this module)
      */
    // protected function configureRateLimiting(): void
    // {
    //     RateLimiter::for('tracking_api', function (Request $request) {
    //         return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
    //     });
    // }
}