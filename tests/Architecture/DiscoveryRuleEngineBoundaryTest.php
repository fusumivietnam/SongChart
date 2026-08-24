<?php

declare(strict_types=1);

use App\Domain\Discovery\Contracts\DiscoveryEntityMapper;
use App\Domain\Discovery\Contracts\DiscoveryRuleEngine;
use App\Support\Discovery\CanonicalDiscoveryEntityMapper;
use App\Support\Discovery\CanonicalDiscoveryFieldRegistry;

it('exposes the stage 16.1 rule engine through typed contracts', function (): void {
    expect(interface_exists(DiscoveryRuleEngine::class))->toBeTrue()
        ->and(interface_exists(DiscoveryEntityMapper::class))->toBeTrue()
        ->and(class_exists(CanonicalDiscoveryFieldRegistry::class))->toBeTrue()
        ->and(class_exists(CanonicalDiscoveryEntityMapper::class))->toBeTrue();
});

it('keeps the discovery rule engine free from provider and raw sql coupling', function (): void {
    $ruleFiles = array_merge(
        glob(app_path('Application/Discovery/Rules/*.php')) ?: [],
        [app_path('Support/Discovery/CanonicalDiscoveryFieldRegistry.php')],
    );
    $providerNeutralFiles = array_merge(
        $ruleFiles,
        [app_path('Support/Discovery/CanonicalDiscoveryEntityMapper.php')],
    );

    foreach ($providerNeutralFiles as $file) {
        $source = (string) file_get_contents($file);
        expect(str_contains($source, 'App\\Contracts\\Providers'), $file.' must not depend on provider contracts')->toBeFalse()
            ->and(str_contains($source, 'App\\Models\\Providers'), $file.' must not depend on provider models')->toBeFalse();
    }

    foreach ($ruleFiles as $file) {
        $source = (string) file_get_contents($file);
        expect(str_contains($source, 'DB::'), $file.' must not execute raw database queries')->toBeFalse()
            ->and(str_contains($source, 'whereRaw('), $file.' must not compile raw SQL predicates')->toBeFalse()
            ->and(str_contains($source, 'selectRaw('), $file.' must not compile raw SQL selections')->toBeFalse();
    }
});
