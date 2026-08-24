<?php

declare(strict_types=1);

it('keeps discovery projection pipeline provider neutral and request path independent', function (): void {
    $paths = [
        app_path('Application/Discovery/Projections/DefaultDiscoveryProjectionPipeline.php'),
        app_path('Support/Discovery/EloquentDiscoveryEntitySource.php'),
        app_path('Support/Discovery/DatabaseDiscoveryProjectionStore.php'),
    ];

    foreach ($paths as $path) {
        $source = file_get_contents($path);
        expect($source === false)->toBeFalse();
        if ($source === false) {
            continue;
        }

        foreach (['App\\Domain\\Providers', 'ProviderAdapter', 'Http::', 'request()'] as $forbidden) {
            expect(str_contains($source, $forbidden))->toBeFalse();
        }
    }
});
