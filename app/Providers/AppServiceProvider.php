<?php

namespace App\Providers;

use App\Modules\ProtocolEngine\Policies\ReminderPolicy;
use App\Modules\SubscriptionBilling\Contracts\SubscriptionServiceInterface;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::after(function ($user, $ability, $result, $model) {
            return $result === false; // Deny access if policy returns false
        });

        // Bind the ReminderPolicy, injecting the SubscriptionServiceInterface
        Gate::define('create', function ($user) {
            return app(ReminderPolicy::class)->create($user);
        });
    }
}
