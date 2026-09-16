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

it('keeps product telemetry bounded, privacy-minimized and consumer-owned', function (): void {
    $contract = json_decode(
        (string) file_get_contents(base_path('docs/project/product/product-event-contract.json')),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    expect($contract['schema_version'])->toBe(2)
        ->and($contract['authority'])->toBe('product_telemetry_contract')
        ->and($contract['defaults']['canonical_authority'])->toBeFalse()
        ->and($contract['defaults']['raw_ip_allowed'])->toBeFalse()
        ->and($contract['defaults']['raw_user_agent_allowed'])->toBeFalse()
        ->and($contract['defaults']['request_payload_capture_allowed'])->toBeFalse()
        ->and($contract['defaults']['user_identifier_allowed'])->toBeFalse()
        ->and($contract['defaults']['session_identifier_allowed'])->toBeFalse()
        ->and($contract['defaults']['query_value_allowed'])->toBeFalse()
        ->and($contract['defaults']['external_analytics_dependency'])->toBeFalse()
        ->and($contract['storage_decision'])->toBe('daily_aggregate_only_no_user_or_query_level_rows');

    foreach (['search.performed', 'search.zero_result'] as $eventName) {
        $event = $contract['events'][$eventName] ?? null;

        expect($event)->toBeArray()
            ->and($event['status'])->toBe('active_aggregate_only')
            ->and($event['producer'])->not->toBeEmpty()
            ->and($event['purpose'])->not->toBeEmpty()
            ->and($event['pii_class'])->toBe('anonymous_aggregate_no_user_or_query_identifier')
            ->and($event['allowed_fields'])->toBeArray()->not->toBeEmpty()
            ->and($event['forbidden_fields'])->toContain(
                'query',
                'normalized_query',
                'query_hash',
                'user_id',
                'session_id',
                'raw_ip',
                'raw_user_agent',
                'full_request_payload',
            )
            ->and($event['retention'])->not->toBeEmpty()
            ->and($event['consumer'])->not->toBeEmpty()
            ->and($event['primary_metric'])->not->toBeEmpty();
    }

    expect($contract['persistence']['owner'])->toBe('product-telemetry')
        ->and($contract['persistence']['table'])->toBe('product_search_daily_aggregates')
        ->and($contract['persistence']['raw_event_rows'])->toBeFalse()
        ->and($contract['persistence']['disable_switch'])->not->toBeEmpty()
        ->and($contract['persistence']['degradation'])->not->toBeEmpty()
        ->and($contract['derived_measurements']['consumer'])->toContain('ProductSignalSummary.php')
        ->and($contract['derived_measurements']['default_window_days'])->toBe(28)
        ->and($contract['derived_measurements']['maximum_window_days'])->toBe(90)
        ->and($contract['derived_measurements']['retention']['status'])->toBe('insufficient_evidence')
        ->and($contract['derived_measurements']['retention']['forbidden_proxies'])->toContain(
            'aggregate_search_growth',
            'saved_item_count',
            'browser_local_recent_state',
        );
});

it('does not silently promote speculative product events or tracking dimensions', function (): void {
    $contract = json_decode(
        (string) file_get_contents(base_path('docs/project/product/product-event-contract.json')),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    expect($contract['events']['entity.viewed']['status'])->toBe('candidate_not_approved')
        ->and($contract['events']['relationship.clicked']['status'])->toBe('deferred_no_demonstrated_producer_consumer')
        ->and($contract['events']['provider.clicked']['status'])->toBe('deferred_no_demonstrated_producer_consumer')
        ->and($contract['events']['favorite.added']['status'])->toBe('deferred_mvp_exists_but_no_accepted_measurement_consumer')
        ->and($contract['events']['favorite.removed']['status'])->toBe('deferred_mvp_exists_but_no_accepted_measurement_consumer')
        ->and($contract['events']['collection.updated']['status'])->toBe('deferred_no_named_user_collections_mvp');

    expect($contract['future_expansion_gate']['requires_new_review_for'])
        ->toContain(
            'query-level demand evidence',
            'user or anonymous identifiers',
            'session linkage',
            'entity-level view history',
            'external analytics delivery',
            'new retention-sensitive dimensions',
            'cohort retention measurement',
        );
});

it('keeps the search controller on the application recorder boundary', function (): void {
    $controller = (string) file_get_contents(app_path('Http/Controllers/Search/SearchController.php'));

    expect($controller)
        ->toContain('RecordSearchProductSignal')
        ->not->toContain('DB::')
        ->not->toContain('product_search_daily_aggregates');
});
