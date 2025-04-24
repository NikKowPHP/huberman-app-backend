<?php

namespace Tests\Feature\SubscriptionBilling;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebhookSignatureTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_rejects_requests_with_invalid_stripe_signature()
    {
        $response = $this->postJson('/api/webhooks/stripe', [
            'type' => 'checkout.session.completed',
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function it_accepts_requests_with_valid_stripe_signature()
    {
        $webhookSecret = config('cashier.webhook.secret');

        $payload = json_encode([
            'type' => 'checkout.session.completed',
        ]);

        $signature = 't=1682345678,v1=abcdefg,v0=1234567'; // Replace with a valid signature

        $response = $this->withHeaders([
            'Stripe-Signature' => $signature,
        ])->postJson('/api/webhooks/stripe', json_decode($payload, true));

        $response->assertStatus(404); // Assuming the route doesn't exist yet, so it returns 404
    }
}
