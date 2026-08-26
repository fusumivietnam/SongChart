<?php

declare(strict_types=1);

namespace App\Support\Providers\Ingestion;

use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Providers\Catalog\DTO\ProviderPayload;
use App\Domain\Providers\Ingestion\DTO\ProviderImportPlan;
use App\Domain\Providers\Ingestion\DTO\ProviderImportPreview;
use InvalidArgumentException;

final class ProviderImportPlanBuilder
{
    public function build(ProviderPayload $payload, ProviderImportPreview $preview): ProviderImportPlan
    {
        $operation = match ([$payload->providerSlug, $payload->entityType]) {
            ['musicbrainz', EntityType::Artist] => 'artist-lookup',
            ['musicbrainz', EntityType::Recording] => 'recording-lookup',
            default => throw new InvalidArgumentException('No governed import operation is registered for this provider/entity pair.'),
        };

        $reviewReasons = array_map(
            static fn (array $issue): string => $issue['path'].': '.$issue['message'],
            $preview->issues,
        );

        $counts = [
            'identifiers' => $preview->counts['identifiers'] ?? 0,
            'fields' => $preview->counts['fields'] ?? 0,
            'relationships' => $preview->counts['relationships'] ?? 0,
            'rich_evidence' => ($preview->counts['media_assets'] ?? 0)
                + ($preview->counts['destinations'] ?? 0)
                + ($preview->counts['availability'] ?? 0)
                + ($preview->counts['classifications'] ?? 0)
                + ($preview->counts['metrics'] ?? 0),
            'review_items' => count($reviewReasons),
            'direct_canonical_mutations' => 0,
        ];

        $fingerprint = hash('sha256', json_encode([
            'provider_slug' => $payload->providerSlug,
            'entity_type' => $payload->entityType->value,
            'external_id' => strtolower(trim($payload->externalId)),
            'operation' => $operation,
            'payload_hash' => $payload->hash(),
            'preview' => $preview->toArray(),
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));

        return new ProviderImportPlan(
            providerSlug: $payload->providerSlug,
            entityType: $payload->entityType,
            externalId: strtolower(trim($payload->externalId)),
            operation: $operation,
            executable: $preview->valid,
            counts: $counts,
            reviewReasons: $reviewReasons,
            fingerprint: $fingerprint,
        );
    }
}
