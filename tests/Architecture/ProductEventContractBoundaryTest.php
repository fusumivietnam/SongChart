<?php

declare(strict_types=1);

it('keeps product telemetry bounded, privacy-minimized and consumer-owned', function (): void {
    $path = base_path('docs/project/product/product-event-contract.json');
    $contract = json_decode((string) file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);

    expect($contract['schema_version'])->toBe(1)
        ->and($contract['authority'])->toBe('product_telemetry_contract')
        ->and($contract['defaults']['canonical_authority'])->toBeFalse()
        ->and($contract['defaults']['raw_ip_allowed'])->toBeFalse()
        ->and($contract['defaults']['raw_user_agent_allowed'])->toBeFalse()
        ->and($contract['defaults']['request_payload_capture_allowed'])->toBeFalse()
        ->and($contract['defaults']['external_analytics_dependency'])->toBeFalse()
        ->and($contract['storage_decision'])->toBe('deferred_until_consumer_and_retention_evidence');

    foreach (['search.performed', 'search.zero_result'] as $eventName) {
        $event = $contract['events'][$eventName] ?? null;

        expect($event)->toBeArray()
            ->and($event['status'])->toBe('approved_contract_only')
            ->and($event['producer'])->not->toBeEmpty()
            ->and($event['purpose'])->not->toBeEmpty()
            ->and($event['pii_class'])->not->toBeEmpty()
            ->and($event['allowed_fields'])->toBeArray()->not->toBeEmpty()
            ->and($event['forbidden_fields'])->toContain('raw_ip', 'raw_user_agent', 'full_request_payload')
            ->and($event['retention'])->toBe('not_yet_activated')
            ->and($event['consumer'])->not->toBeEmpty()
            ->and($event['primary_metric'])->not->toBeEmpty();
    }
});

it('does not silently promote speculative product events', function (): void {
    $contract = json_decode(
        (string) file_get_contents(base_path('docs/project/product/product-event-contract.json')),
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    expect($contract['events']['entity.viewed']['status'])->toBe('candidate_not_approved')
        ->and($contract['events']['relationship.clicked']['status'])->toBe('deferred_no_demonstrated_producer_consumer')
        ->and($contract['events']['provider.clicked']['status'])->toBe('deferred_no_demonstrated_producer_consumer')
        ->and($contract['events']['favorite.added']['status'])->toBe('deferred_until_favorites_mvp_exists')
        ->and($contract['events']['favorite.removed']['status'])->toBe('deferred_until_favorites_mvp_exists')
        ->and($contract['events']['collection.updated']['status'])->toBe('deferred_until_user_collections_mvp_exists');

    expect($contract['activation_gate']['required_before_persistence'])
        ->toContain(
            'explicit retention window',
            'normalized-query privacy policy',
            'repository-owned persistence owner',
            'derived metric consumer',
            'PostgreSQL-backed verification',
            'disable/degradation path',
        );
});
