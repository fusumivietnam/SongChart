<?php

declare(strict_types=1);

use App\Domain\Discovery\Contracts\DiscoveryChannelRepository;
use App\Domain\Discovery\Contracts\DiscoveryFieldRegistry;
use App\Domain\Discovery\Contracts\DiscoveryProjectionReader;

it('keeps discovery behind typed contracts', function (): void {
    expect(interface_exists(DiscoveryChannelRepository::class))->toBeTrue()
        ->and(interface_exists(DiscoveryFieldRegistry::class))->toBeTrue()
        ->and(interface_exists(DiscoveryProjectionReader::class))->toBeTrue();
});

it('keeps discovery domain free from provider and eloquent dependencies', function (): void {
    $files = array_merge(
        glob(app_path('Domain/Discovery/*.php')) ?: [],
        glob(app_path('Domain/Discovery/*/*.php')) ?: [],
    );

    foreach ($files as $file) {
        $source = (string) file_get_contents($file);
        expect(str_contains($source, 'Illuminate\\Database\\Eloquent'))->toBeFalse()
            ->and(str_contains($source, 'App\\Models\\Providers'))->toBeFalse()
            ->and(str_contains($source, 'App\\Contracts\\Providers'))->toBeFalse()
            ->and(str_contains($source, 'whereRaw('))->toBeFalse()
            ->and(str_contains($source, 'selectRaw('))->toBeFalse();
    }
});

it('keeps frontend discovery on projection contracts instead of rule execution', function (): void {
    $contracts = json_decode((string) file_get_contents(base_path('docs/project/domain/domain-contracts.json')), true, 512, JSON_THROW_ON_ERROR);

    expect($contracts['discovery']['boundary']['public_request_executes_rules'] ?? true)->toBeFalse()
        ->and($contracts['discovery']['projection']['frontend_executes_rules'] ?? true)->toBeFalse()
        ->and($contracts['discovery']['rules']['raw_sql_allowed'] ?? true)->toBeFalse();
});
