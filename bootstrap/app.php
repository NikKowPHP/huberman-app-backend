<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        api: __DIR__.'/../routes/api.php', // Ensure API routes are loaded
        apiPrefix: 'api/v1', // Ensure correct API prefix
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'premium.access' => \App\Http\Middleware\CheckPremiumAccess::class,
            'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class, // Example: Ensure common aliases are present if needed
            'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class, // Example
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class, // Example
            'webhook.stripe' => \App\Http\Middleware\VerifyStripeWebhookSignature::class, // Add existing webhook middleware alias
        ]);

        // Add global middleware if needed, e.g., for API stateful domains
        // $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
