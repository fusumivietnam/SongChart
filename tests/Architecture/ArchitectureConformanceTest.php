<?php

declare(strict_types=1);

use App\Contracts\Search\SearchCatalog;
use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Providers\Enums\ProviderSyncOperation;
use App\Domain\Providers\Enums\ProviderSyncStatus;
use App\Providers\SearchServiceProvider;
use App\Support\Search\EloquentSearchCatalog;

it('uses canonical production boundaries', function (): void {
    config()->set('songchart.search.demo_enabled', false);
    app()->forgetInstance(SearchCatalog::class);
    (new SearchServiceProvider(app()))->register();

    expect(app(SearchCatalog::class))->toBeInstanceOf(EloquentSearchCatalog::class)
        ->and(EntityType::routePattern())->toContain('artist')
        ->and(ProviderSyncOperation::HealthCheck->value)->toBe('health-check')
        ->and(ProviderSyncStatus::pendingValues())->toBe(['queued', 'running', 'retrying']);
});

it('keeps HTTP controllers outside persistence read and write implementation details', function (): void {
    $contract = json_decode(
        (string) file_get_contents(base_path('docs/project/domain/application-data-boundary.json')),
        true,
        512,
        JSON_THROW_ON_ERROR,
    );

    $signals = array_merge(
        $contract['controller_boundary']['forbidden_write_signals'] ?? [],
        $contract['controller_boundary']['forbidden_direct_read_signals'] ?? [],
    );

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(app_path('Http/Controllers'), FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $source = (string) file_get_contents($file->getPathname());
        foreach ($signals as $signal) {
            expect(str_contains($source, (string) $signal))->toBeFalse(
                $file->getPathname().' crosses application data boundary via '.$signal,
            );
        }
    }
});

it('classifies admin query surfaces as read models and keeps them mutation free', function (): void {
    $contract = json_decode(
        (string) file_get_contents(base_path('docs/project/domain/application-data-boundary.json')),
        true,
        512,
        JSON_THROW_ON_ERROR,
    );

    foreach ($contract['read_models'] ?? [] as $relative) {
        $source = (string) file_get_contents(base_path((string) $relative));

        foreach ($contract['read_model_forbidden_signals'] ?? [] as $signal) {
            expect(str_contains($source, (string) $signal))->toBeFalse(
                $relative.' mutates persistence via '.$signal,
            );
        }
    }
});
