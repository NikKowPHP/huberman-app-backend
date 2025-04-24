<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cashier Model
    |--------------------------------------------------------------------------
    |
    | This is the model in your application that implements the Billable trait.
    | This model will be used to create subscriptions and perform other
    | billing operations.
    |
    */

    'model' => App\Modules\UserManagement\Models\User::class,

    /*
    |--------------------------------------------------------------------------
    | Stripe API Key
    |--------------------------------------------------------------------------
    |
    | This key will be used to communicate with the Stripe API. You should
    | be able to find your key in the Stripe dashboard.
    |
    */

    'key' => env('STRIPE_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Stripe Secret Key
    |--------------------------------------------------------------------------
    |
    | This secret will be used to communicate with the Stripe API. You should
    | be able to find your secret key in the Stripe dashboard.
    |
    */

    'secret' => env('STRIPE_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Stripe Webhook Secret
    |--------------------------------------------------------------------------
    |
    | This secret will be used to verify that incoming webhooks are actually
    | sent by Stripe. You should be able to define this key in the Stripe
    | dashboard.
    |
    */

    'webhook' => [
        'secret' => env('STRIPE_WEBHOOK_SECRET'),
        'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cashier Path
    |--------------------------------------------------------------------------
    |
    | This is the path that Cashier will use to handle Stripe webhooks. You
    | are free to change this path to anything you like. Make sure to
    | update your Stripe webhook settings to match this path.
    |
    */

    'path' => 'stripe/webhook',

    /*
    |--------------------------------------------------------------------------
    | Currency
    |--------------------------------------------------------------------------
    |
    | This is the default currency that will be used when performing charges
    | and retrieving prices from Stripe. Generally, this should be set to
    | the currency that your application primarily uses.
    |
    */

    'currency' => env('CASHIER_CURRENCY', 'usd'),

    /*
    |--------------------------------------------------------------------------
    | Currency Locale
    |--------------------------------------------------------------------------
    |
    | This is the default locale that will be used when formatting currency
    | values. You are free to set this to any locale that is supported by
    | the "money" PHP extension.
    |
    */

    'currency_locale' => env('CASHIER_CURRENCY_LOCALE', 'en_US'),

    /*
    |--------------------------------------------------------------------------
    | Payment Confirmation Notification
    |--------------------------------------------------------------------------
    |
    | If this setting is enabled, Cashier will automatically send your users
    | a notification when their payment is confirmed. You are free to
    | customize the notification that is sent to your users.
    |
    */

    'payment_notification' => false,

    /*
    |--------------------------------------------------------------------------
    | Queue
    |--------------------------------------------------------------------------
    |
    | This setting controls the queue that is used to send payment confirmed
    | notifications and handle other Stripe related tasks. You are free to
    | change this to any queue you like or set it to null to use the sync
    | queue.
    |
    */

    'queue' => 'default',

    /*
    |--------------------------------------------------------------------------
    | Webhooks
    |--------------------------------------------------------------------------
    |
    | This setting defines the webhooks that you want to listen for from
    | Stripe. You can define any webhooks that are supported by Stripe.
    |
    */

    'webhooks' => [
        'model' => \App\Models\User::class,
    ],

];
