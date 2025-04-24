<?php

namespace App\Modules\Authentication\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The module namespace to assume when generating URLs to actions.
     *
     * @var string
     */
    // protected $moduleNamespace = 'App\Modules\Authentication\Http\Controllers'; // Optional: If controllers are namespaced under the module

    /**
     * Called before routes are registered.
     *
     * Register any model bindings or pattern based filters.
     *
     * @return void
     */
    public function boot(): void
    {
        // $this->configureRateLimiting(); // Uncomment if rate limiting specific to this module is needed

        $this->routes(function () {
            Route::middleware('api')
                // Define a prefix specific to authentication routes if desired
                // e.g., ->prefix('api/v1/auth')
                // Or use the global prefix defined elsewhere
                ->prefix('api/v1') // Assuming global v1 prefix for now
                ->group(base_path('app/Modules/Authentication/routes/api.php'));

            // Uncomment if web routes exist for this module
            // Route::middleware('web')
            //     ->group(base_path('app/Modules/Authentication/routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    // protected function configureRateLimiting(): void
    // {
    //     RateLimiter::for('api', function (Request $request) {
    //         return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
    //     });
    // }
}