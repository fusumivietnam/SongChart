<?php

declare(strict_types=1);

use App\Providers\AppServiceProvider;
use App\Providers\AuthorizationServiceProvider;
use App\Providers\DiscoveryServiceProvider;
use App\Providers\ExtensionServiceProvider;
use App\Providers\FortifyServiceProvider;
use App\Providers\ProviderServiceProvider;
use App\Providers\SearchServiceProvider;

return [
    AppServiceProvider::class,
    AuthorizationServiceProvider::class,
    DiscoveryServiceProvider::class,
    ProviderServiceProvider::class,
    SearchServiceProvider::class,
    ExtensionServiceProvider::class,
    FortifyServiceProvider::class,
];
