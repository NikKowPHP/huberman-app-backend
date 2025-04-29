<?php

namespace App\Modules\SubscriptionBilling\Services;

use Jose\Component\Core\AlgorithmManager;
use Jose\Component\KeyManagement\JWKFactory;
use Jose\Component\Signature\Algorithm\ES256;
use Jose\Component\Signature\JWSVerifier;
use Jose\Component\Core\Converter\StandardConverter;
use Jose\Component\Signature\Serializer\CompactSerializer;

class AppleSubscriptionService
{
    public function decodeAndVerifyJWS(string $jws): array
    {
        // Documentation: https://developer.apple.com/documentation/appstoreservernotifications/responsebodyv2decodedpayload

        // Step 1: Load the algorithm manager.
        $algorithmManager = new AlgorithmManager([
            new ES256(),
        ]);

        // Step 2: Load the JWS Verifier.
        $jwsVerifier = new JWSVerifier(
            $algorithmManager
        );

        // Step 3: Get the key. Replace with Apple's public key.
        // Fetch the key from apple's server
        // $jwk = JWKFactory::createFromUrl('https://developer.apple.com/path/to/key');
        // For testing purposes, use a local key
        $jwk = JWKFactory::createFromContent('{"kty":"EC",
            "kid":"82F495F70F49C81999490978301F9954",
            "crv":"P-256",
            "x":"KM4Y6iYO0j0H-4z-f-a80y-Uf8t-Y6i-l-Y6iYO0j0",
            "y":"KM4Y6iYO0j0H-4z-f-a80y-Uf8t-Y6i-l-Y6iYO0j0"}');

        // Step 4: Load the serializer
        $serializer = new CompactSerializer(new StandardConverter());

        // Step 5: Deserialize the JWS.
        $jws = $serializer->unserialize($jws);

        // Step 6: Verify the signature.
        $isVerified = $jwsVerifier->verifyWithKey($jws, $jwk, 0);

        if (!$isVerified) {
            throw new \Exception('Invalid JWS signature.');
        }

        // Step 7: Get the payload.
        $payload = $jws->getPayload();

        if (null === $payload) {
            throw new \Exception('Invalid JWS payload.');
        }

        // Step 8: Convert the payload into an array.
        $data = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);

        return $data;
    }

    public function handleDidChangeRenewalStatus(string $jws): void
    {
        $payload = $this->decodeAndVerifyJWS($jws);

        $autoRenewStatus = $payload['data']['autoRenewStatus'] ?? null;
        $productId = $payload['data']['productId'] ?? null;

        if ($autoRenewStatus === false && $productId !== null) {
            $subscription = \App\Modules\SubscriptionBilling\Models\Subscription::where('plan_product_id', $productId)->first();

            if ($subscription) {
                $subscription->update(['status' => 'canceled']);
                \Illuminate\Support\Facades\Event::dispatch(new \App\Events\SubscriptionExpired($subscription));
            }
        }
    }
}
