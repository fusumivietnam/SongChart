<?php

declare(strict_types=1);

use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Providers\Catalog\DTO\ProviderPayload;
use App\Support\Providers\Ingestion\ProviderImportPlanBuilder;
use App\Support\Providers\Ingestion\ProviderImportPreviewBuilder;
use App\Support\Providers\Normalization\DefaultNormalizedProviderEntityValidator;
use App\Support\Providers\Normalization\MusicBrainzProviderMapper;
use App\Support\Providers\Normalization\ProviderSpecificMapperRegistry;

it('builds a deterministic governed plan with zero direct canonical mutations', function (): void {
    $payload = new ProviderPayload(
        providerSlug: 'musicbrainz',
        entityType: EntityType::Recording,
        externalId: 'RECORDING-MBID',
        data: [
            'title' => 'Example Song',
            'length' => 181000,
            'isrcs' => ['USAAA2600001'],
        ],
        receivedAt: new DateTimeImmutable('2026-08-26T00:00:00+00:00'),
    );
    $previewBuilder = new ProviderImportPreviewBuilder(
        new ProviderSpecificMapperRegistry([new MusicBrainzProviderMapper]),
        new DefaultNormalizedProviderEntityValidator,
    );
    $preview = $previewBuilder->build($payload);
    $builder = new ProviderImportPlanBuilder;

    $first = $builder->build($payload, $preview);
    $second = $builder->build($payload, $preview);

    expect($first->operation)->toBe('recording-lookup')
        ->and($first->externalId)->toBe('recording-mbid')
        ->and($first->executable)->toBeTrue()
        ->and($first->counts['direct_canonical_mutations'])->toBe(0)
        ->and($first->fingerprint)->toBe($second->fingerprint)
        ->and(strlen($first->fingerprint))->toBe(64);
});
