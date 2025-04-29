<?php

namespace Tests\Unit\SubscriptionBilling;

use Tests\TestCase;

class GooglePubSubTest extends TestCase
{
    /**
     * Test that Pub/Sub message is correctly decoded and parsed.
     *
     * @return void
     */
    public function testPubSubMessageDecodingAndParsing()
    {
        $encodedData = base64_encode(json_encode(['test' => 'data']));
        $payload = [
            'message' => [
                'data' => $encodedData,
            ],
        ];

        $data = base64_decode($payload['message']['data']);
        $message = json_decode($data, true);

        $this->assertEquals(['test' => 'data'], $message);
    }
}
