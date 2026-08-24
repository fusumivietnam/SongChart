<?php

declare(strict_types=1);

use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Providers\Catalog\DTO\ProviderImportContext;
use App\Domain\Providers\Catalog\DTO\ProviderPayload;
use App\Domain\Providers\Catalog\Enums\ProviderRequestFailureKind;
use App\Support\Providers\Catalog\InMemoryProviderCatalogAdapterRegistry;
use App\Support\Providers\Catalog\NullProviderCatalogAdapter;

it('registers catalog adapters by provider slug', function (): void {
    $registry = new InMemoryProviderCatalogAdapterRegistry([
        new NullProviderCatalogAdapter('musicbrainz'),
    ]);

    expect($registry->slugs())->toBe(['musicbrainz'])
        ->and($registry->for('musicbrainz'))->not->toBeNull()
        ->and($registry->for('unknown'))->toBeNull();
});

it('rejects duplicate provider catalog adapters', function (): void {
    expect(fn () => new InMemoryProviderCatalogAdapterRegistry([
        new NullProviderCatalogAdapter('musicbrainz'),
        new NullProviderCatalogAdapter('musicbrainz'),
    ]))->toThrow(InvalidArgumentException::class);
});

it('keeps payload hashing deterministic and normalization provider agnostic', function (): void {
    $payload = new ProviderPayload(
        providerSlug: 'musicbrainz',
        entityType: EntityType::Artist,
        externalId: 'mbid-1',
        data: ['name' => 'Radiohead'],
        receivedAt: new DateTimeImmutable('2026-08-04T00:00:00+00:00'),
    );

    $adapter = new NullProviderCatalogAdapter('musicbrainz');
    $normalized = $adapter->normalize($payload);

    expect($payload->hash())->toHaveLength(64)
        ->and($normalized->externalId)->toBe('mbid-1')
        ->and($normalized->entityType)->toBe(EntityType::Artist)
        ->and($normalized->data->toArray()['name'])->toBe(['presence' => 'provided', 'value' => 'Radiohead']);
});

it('classifies retryable provider failures explicitly', function (): void {
    expect(ProviderRequestFailureKind::RateLimited->retryable())->toBeTrue()
        ->and(ProviderRequestFailureKind::Timeout->retryable())->toBeTrue()
        ->and(ProviderRequestFailureKind::Authentication->retryable())->toBeFalse();
});

it('carries bounded import context without transport details', function (): void {
    $context = new ProviderImportContext(
        runId: '01KZ60TEST',
        entityType: EntityType::Artist,
        externalId: 'mbid-1',
        pageSize: 25,
    );

    expect($context->pageSize)->toBe(25)
        ->and($context->externalId)->toBe('mbid-1');
});
