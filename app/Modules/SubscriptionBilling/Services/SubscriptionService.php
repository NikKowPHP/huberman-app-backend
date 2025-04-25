<?php

namespace App\Modules\SubscriptionBilling\Services;

use App\Modules\SubscriptionBilling\Contracts\SubscriptionServiceInterface;
use App\Modules\SubscriptionBilling\Models\Subscription; // Added import
use Illuminate\Support\Facades\Cache;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use NotImplementedException;

class SubscriptionService implements SubscriptionServiceInterface
{
    public function userHasActivePremiumSubscription($user): bool
    {
        $cacheKey = "user:{$user->id}:premium_subscription";
        return Cache::tags(["user:{$user->id}"])->remember(
            $cacheKey,
            now()->addMinutes(60), // Cache duration
            function () use ($user) {
                // Check for active or trialing status associated with a 'Premium' plan
                return Subscription::query()
                    ->where('user_id', $user->id)
                    ->whereIn('stripe_status', ['active', 'trialing']) // Use stripe_status as per model/migration
                    ->whereHas('plan', function ($query) {
                        $query->where('name', 'like', 'Premium%'); // Match 'Premium Monthly' or 'Premium Annual'
                    })
                    ->exists();
            }
        );
    }

    public function getUserPlan($user)
    {
        return null;
    }

    public function handleWebhook($payload, $signature)
    {
        // This likely handles Stripe webhooks via Cashier or custom logic
        // Keep this separate from Apple/Google specific handling unless refactoring later
        throw new NotImplementedException("Stripe webhook handling not fully implemented here.");
    }

    public function getSubscriptionStatus($user)
    {
        return null;
    }
  /**
     * Handles the processing of a verified Apple App Store Server Notification V2 payload.
     *
     * @param string $signedPayload The JWS payload received from Apple.
     * @return array The decoded and verified payload data.
     * @throws \Exception If verification fails or payload is invalid.
     */
    public function handleAppleNotification(string $signedPayload): array
    {
        try {
            // 1. Decode header to get kid (Key ID) and alg
            // Note: A more robust library might offer a dedicated header parsing function.
            // This basic split assumes a standard JWS structure.
            list($header64) = explode('.', $signedPayload);
            $headerJson = JWT::urlsafeB64Decode($header64);
            $header = json_decode($headerJson);

            if (!$header || !isset($header->kid) || !isset($header->alg)) {
                throw new \InvalidArgumentException('Invalid JWS header.');
            }

            $kid = $header->kid;
            $alg = $header->alg; // e.g., "ES256"

            // 2. Fetch the correct Apple Public Key using the kid
            $publicKey = $this->getApplePublicKey($kid);
            if (!$publicKey) {
                throw new \RuntimeException("Could not find Apple public key for kid: {$kid}");
            }

            // 3. Decode and Verify the JWS Signature
            // The Key object tells the library how to interpret the key material
            $decodedPayload = JWT::decode($signedPayload, new Key($publicKey, $alg));

            // 4. Basic Payload Validation (Optional but Recommended)
            // Add checks for issuer (iss), audience (aud), expiry (exp) if needed,
            // though the library might handle 'exp' automatically.
            // Example:
            // if ($decodedPayload->iss !== 'https://apple.com') { ... }

            Log::info('Apple Notification Received and Verified:', ['payload' => (array)$decodedPayload]);

            // 5. TODO: Process the notification based on $decodedPayload->notificationType
            // Example structure:
            // switch ($decodedPayload->notificationType) {
            //     case 'SUBSCRIBED':
            //         $this->handleAppleSubscribed($decodedPayload->data);
            //         break;
            //     case 'DID_RENEW':
            //         $this->handleAppleRenewed($decodedPayload->data);
            //         break;
            //     // ... other notification types
            // }

            // Clear relevant user cache after processing state changes
            // Cache::tags(["user:{userId}"])->flush(); // Need userId from payload processing

            return (array) $decodedPayload;

        } catch (\Firebase\JWT\ExpiredException $e) {
            Log::error('Apple JWS verification failed: Expired token.', ['error' => $e->getMessage()]);
            throw new \Exception('Apple JWS verification failed: Expired token.');
        } catch (\Exception $e) {
            // Catches SignatureInvalidException, BeforeValidException, and others from JWT::decode
            // Also catches our own exceptions above.
            Log::error('Apple JWS verification failed.', ['error' => $e->getMessage()]);
            throw new \Exception('Apple JWS verification failed: ' . $e->getMessage());
        }
    }

    /**
     * Fetches and caches Apple's public keys.
     *
     * @param string $kid The Key ID from the JWS header.
     * @return string|null The PEM-encoded public key or null if not found.
     */
    private function getApplePublicKey(string $kid): ?string
    {
        $cacheKey = 'apple_public_keys_jwkset';
        // Cache for a reasonable time, e.g., 24 hours, as keys don't change often
        $jwkSet = Cache::remember($cacheKey, now()->addHours(24), function () {
            try {
                // Fetch Apple's public key set (JWKS)
                // Use the production URL. Add sandbox URL handling if needed.
                $response = Http::timeout(15)->get('https://appleid.apple.com/auth/keys');
                $response->throw(); // Throw exception on HTTP errors
                return $response->json();
            } catch (\Exception $e) {
                Log::error('Failed to fetch Apple public keys.', ['error' => $e->getMessage()]);
                // Return null or an empty array to prevent caching failures repeatedly
                return null;
            }
        });

        if (!$jwkSet || !isset($jwkSet['keys']) || !is_array($jwkSet['keys'])) {
             Log::error('Invalid Apple JWKSet received or cached.');
             Cache::forget($cacheKey); // Clear bad cache
             return null;
        }

        // Find the key with the matching kid
        foreach ($jwkSet['keys'] as $key) {
            if (isset($key['kid']) && $key['kid'] === $kid) {
                // Convert the JWK key components (n, e) to PEM format
                // This requires a library or manual conversion logic.
                // Using a simplified placeholder - REAL IMPLEMENTATION NEEDED HERE.
                // Libraries like 'web-token/jwt-framework' or 'spomky-labs/jose' can handle JWK conversion.
                // For now, returning a placeholder.
                // TODO: Implement proper JWK to PEM conversion based on $key['n'] and $key['e'] for RSA or $key['x'], $key['y'] for EC.
                // Example using a hypothetical conversion function:
                // return $this->convertJwkToPem($key);

                // Placeholder - **REPLACE THIS WITH ACTUAL JWK to PEM CONVERSION**
                Log::warning("Placeholder used for Apple public key conversion for kid: {$kid}. Implement actual JWK to PEM conversion.");
                // Returning null forces failure until conversion is implemented
                return null;
            }
        }

        Log::error("Apple public key not found for kid: {$kid}");
        return null;
    }

    // TODO: Implement JWK to PEM conversion function if not using a library that handles it directly.
    // private function convertJwkToPem(array $jwk): string { ... }

    // TODO: Implement specific handlers like handleAppleSubscribed, handleAppleRenewed, etc.
    // private function handleAppleSubscribed(object $data) { ... }
    // private function handleAppleRenewed(object $data) { ... }
// --- NEW CODE END ---

}
