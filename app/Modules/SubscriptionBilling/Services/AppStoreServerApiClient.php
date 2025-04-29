<?php

namespace App\Modules\SubscriptionBilling\Services;

use App\Modules\SubscriptionBilling\Contracts\AppStoreServerApiClientInterface;
use GuzzleHttp\Client;

class AppStoreServerApiClient implements AppStoreServerApiClientInterface
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param string $transactionId
     * @return mixed
     */
    public function getTransactionInfo(string $transactionId)
    {
        $url = config('services.appstore.url') . '/transactions/' . $transactionId;

        $response = $this->client->get($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . config('services.appstore.jwt'),
                'Content-Type' => 'application/json',
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @param string $receiptData
     * @return mixed
     */
    public function verifyReceipt(string $receiptData)
    {
        $url = config('services.appstore.url') . '/verifyReceipt';

        $response = $this->client->post($url, [
            'headers' => [
                'Authorization' => 'Bearer ' . config('services.appstore.jwt'),
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'receipt-data' => $receiptData,
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }
}
