<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Modules\Authentication\Providers\RouteServiceProvider::class,
    App\Modules\UserManagement\Providers\RouteServiceProvider::class,
    App\Modules\SubscriptionBilling\Providers\RouteServiceProvider::class,
    App\Modules\ContentManagement\Providers\RouteServiceProvider::class,
    App\Modules\NotesService\Providers\RouteServiceProvider::class,
    App\Modules\ProtocolEngine\Providers\RouteServiceProvider::class,
    App\Modules\SubscriptionBilling\Providers\SubscriptionBillingServiceProvider::class,
];
