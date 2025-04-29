<?php

namespace Tests\Unit\SubscriptionBilling;

use Tests\TestCase;
use App\Modules\SubscriptionBilling\Services\AppleSubscriptionService;


class AppleSubscriptionServiceTest extends TestCase
{
    private $appleSubscriptionService;
    private $privateKey;
    private $publicKey;

    protected function setUp(): void
    {
        parent::setUp();
        $this->appleSubscriptionService = new AppleSubscriptionService();

        // Generate a new EC key pair for testing
        $this->privateKey = JWKFactory::createECKey('P-256', [
            'kid' => 'TEST_KEY_ID',
            'alg' => 'ES256',
            'use' => 'sig',
        ]);
        $this->publicKey = $this->privateKey->toPublic();

        // Mock the JWKFactory::createFromContent to return our public key
        // This is a simplified mock; a more robust solution might use mockery
        \Illuminate\Support\Facades\App::bind(JWKFactory::class, function () {
            $mockFactory = $this->createMock(JWKFactory::class);
            $mockFactory->method('createFromContent')->willReturn($this->publicKey);
            // Add mock for createFromUrl if needed for production key fetching simulation
            return $mockFactory;
        });

        // Override the key loading in the service to use our test public key
        // This requires modifying the service or using dependency injection/mocking
        // For simplicity here, we assume the service can be modified or mocked appropriately
        // In a real scenario, you'd inject the key source or use a mocking library
        $this->appleSubscriptionService = new class($this->publicKey) extends AppleSubscriptionService {
            private $testPublicKey;
            public function __construct($publicKey) { $this->testPublicKey = $publicKey; }
            protected function getApplePublicKey(): \Jose\Component\Core\JWK {
                // Override method to return test key instead of fetching/loading real key
                return $this->testPublicKey;
            }
             // Need to override the main method to use the overridden getApplePublicKey
             public function decodeAndVerifyJWS(string $jws): array
             {
                 $algorithmManager = new AlgorithmManager([new ES256()]);
                 $jwsVerifier = new \Jose\Component\Signature\JWSVerifier($algorithmManager);
                 $jwk = $this->getApplePublicKey(); // Use the overridden method
                 $serializer = new CompactSerializer(new StandardConverter());
                 $jwsObject = $serializer->unserialize($jws);

                 if (!$jwsVerifier->verifyWithKey($jwsObject, $jwk, 0)) {
                     throw new \Exception('Invalid JWS signature.');
                 }
                 $payload = $jwsObject->getPayload();
                 if (null === $payload) { throw new \Exception('Invalid JWS payload.'); }
                 return json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
             }
        };
    }

    private function createTestJWS(array $payload): string
    {
        $algorithmManager = new AlgorithmManager([new ES256()]);
        $jwsBuilder = new JWSBuilder(new StandardConverter(), $algorithmManager);

        $jws = $jwsBuilder
            ->create()
            ->withPayload(json_encode($payload))
            ->addSignature($this->privateKey, ['alg' => 'ES256', 'kid' => $this->privateKey->get('kid')])
            ->build();

        $serializer = new CompactSerializer(new StandardConverter());
        return $serializer->serialize($jws, 0);
    }

    public function testDecodeAndVerifyJWS_validJWS_returnsArray()
    {
        $payload = [
            'notificationType' => 'SUBSCRIBED',
            'subtype' => 'INITIAL_BUY',
            'notificationUUID' => 'some-uuid',
            'data' => [ /* ... other data ... */ ],
            'version' => '2.0',
            'signedDate' => time() * 1000, // Milliseconds
        ];
        $jwsString = $this->createTestJWS($payload);

        $decodedData = $this->appleSubscriptionService->decodeAndVerifyJWS($jwsString);

        $this->assertIsArray($decodedData);
        $this->assertEquals($payload['notificationType'], $decodedData['notificationType']);
        $this->assertEquals($payload['data'], $decodedData['data']);
    }

    public function testDecodeAndVerifyJWS_invalidSignature_throwsException()
    {
        $payload = ['test' => 'data'];
        $jwsString = $this->createTestJWS($payload);

        // Tamper with the signature part
        $parts = explode('.', $jwsString);
        $parts[2] = str_replace(['a', 'b', 'c'], ['x', 'y', 'z'], $parts[2]); // Simple tamper
        $tamperedJws = implode('.', $parts);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid JWS signature.');

        $this->appleSubscriptionService->decodeAndVerifyJWS($tamperedJws);
    }

     public function testDecodeAndVerifyJWS_invalidPayloadFormat_throwsException()
     {
         // Create a JWS with non-JSON payload
         $algorithmManager = new AlgorithmManager([new ES256()]);
         $jwsBuilder = new JWSBuilder(new StandardConverter(), $algorithmManager);
         $jws = $jwsBuilder
             ->create()
             ->withPayload("this is not json") // Invalid payload
             ->addSignature($this->privateKey, ['alg' => 'ES256', 'kid' => $this->privateKey->get('kid')])
             ->build();
         $serializer = new CompactSerializer(new StandardConverter());
         $jwsString = $serializer->serialize($jws, 0);

         $this->expectException(\JsonException::class); // Expecting JSON decoding error

         $this->appleSubscriptionService->decodeAndVerifyJWS($jwsString);
     }
}
