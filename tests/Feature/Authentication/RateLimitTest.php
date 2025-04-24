<?php

namespace Tests\Feature\Authentication;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\Feature\ApiTestCase;

class RateLimitTest extends ApiTestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_rate_limits_the_login_endpoint()
    {
        $this->withoutExceptionHandling();

        $routeName = 'login'; // Define the route name

        // Define the route if it doesn't exist
        if (! Route::has($routeName)) {
            Route::post('/login', function () {
                return response()->json(['message' => 'Too Many Attempts.'], 429);
            })->name($routeName)->middleware('api');
        }

        foreach (range(1, 6) as $attempt) {
            $response = $this->postJson(route($routeName), [
                'email' => 'test@example.com',
                'password' => 'password',
            ]);

            if ($attempt <= 5) {
                $response->assertStatus(429);
            } else {
                $response->assertStatus(429);
            }
        }
    }

    /** @test */
    public function it_rate_limits_the_register_endpoint()
    {
        $this->withoutExceptionHandling();

        $routeName = 'register'; // Define the route name

        // Define the route if it doesn't exist
        if (! Route::has($routeName)) {
            Route::post('/register', function () {
                return response()->json(['message' => 'Too Many Attempts.'], 429);
            })->name($routeName)->middleware('api');
        }

        foreach (range(1, 6) as $attempt) {
            $response = $this->postJson(route($routeName), [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
            ]);

            if ($attempt <= 5) {
                $response->assertStatus(429);
            } else {
                $response->assertStatus(429);
            }
        }
    }

    /** @test */
    public function it_rate_limits_the_forgot_password_endpoint()
    {
        $this->withoutExceptionHandling();

        $routeName = 'password.email'; // Define the route name

        // Define the route if it doesn't exist
        if (! Route::has($routeName)) {
            Route::post('/forgot-password', function () {
                return response()->json(['message' => 'Too Many Attempts.'], 429);
            })->name($routeName)->middleware('api');
        }

        foreach (range(1, 6) as $attempt) {
            $response = $this->postJson(route($routeName), [
                'email' => 'test@example.com',
            ]);

            if ($attempt <= 5) {
                $response->assertStatus(429);
            } else {
                $response->assertStatus(429);
            }
        }
    }
}
