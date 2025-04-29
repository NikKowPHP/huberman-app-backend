<?php

namespace App\Modules\SubscriptionBilling\Contracts;

interface AppStoreServerApiClientInterface
{
    /**
     * @param string $transactionId
     * @return mixed
     */
    public function getTransactionInfo(string $transactionId);

    /**
     * @param string $receiptData
     * @return mixed
     */
    public function verifyReceipt(string $receiptData);
}
