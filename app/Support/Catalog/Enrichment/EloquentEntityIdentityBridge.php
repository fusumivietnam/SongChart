<?php

declare(strict_types=1);

namespace App\Support\Catalog\Enrichment;

use App\Contracts\Catalog\EntityIdentityBridge;
use App\Domain\Catalog\Enrichment\IdentityBridgeSnapshot;
use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Catalog\Enums\VerificationState;
use App\Models\Catalog\ExternalIdentifier;
use App\Models\Provider;
use App\Models\ProviderDestination;

final readonly class EloquentEntityIdentityBridge implements EntityIdentityBridge
{
    public function for(EntityType $type, string $entityId): IdentityBridgeSnapshot
    {
        $identifierRows = ExternalIdentifier::query()
            ->where('entity_type', $type->value)
            ->where('entity_id', $entityId)
            ->orderByDesc('is_primary')
            ->orderBy('namespace')
            ->get();

        $identifiers = [];
        $sources = [];
        foreach ($identifierRows as $identifier) {
            $verification = $identifier->getAttribute('verification_state');
            $verificationValue = $verification instanceof VerificationState ? $verification->value : (string) $verification;
            $namespace = (string) $identifier->getAttribute('namespace');
            $value = (string) $identifier->getAttribute('value');
            $identifiers[] = [
                'namespace' => $namespace,
                'value' => $value,
                'primary' => (bool) $identifier->getAttribute('is_primary'),
                'verified' => $verificationValue,
            ];
            $sources[$this->sourceKeyForNamespace($namespace)] = true;
        }

        $destinationRows = ProviderDestination::query()
            ->where('entity_type', $type->value)
            ->where('entity_id', $entityId)
            ->with('provider')
            ->orderByDesc('verified_at')
            ->get();

        $destinations = [];
        foreach ($destinationRows as $destination) {
            $providerRelation = $destination->getRelation('provider');
            $provider = $providerRelation instanceof Provider ? $providerRelation : null;
            $providerSlug = $provider !== null ? (string) $provider->getAttribute('slug') : 'unknown';
            $sources['provider:'.$providerSlug] = true;
            $url = $destination->getAttribute('url');
            $destinations[] = [
                'provider' => $providerSlug,
                'resource_id' => (string) $destination->getAttribute('provider_resource_id'),
                'url' => is_string($url) ? $url : null,
                'review_state' => (string) $destination->getRawOriginal('review_state'),
            ];
        }

        unset($sources['unknown']);

        return new IdentityBridgeSnapshot($identifiers, $destinations, count($sources));
    }

    private function sourceKeyForNamespace(string $namespace): string
    {
        if ($namespace === 'provider:musicbrainz' || str_starts_with($namespace, 'musicbrainz_') || $namespace === 'isrc') {
            return 'provider:musicbrainz';
        }

        if (str_starts_with($namespace, 'youtube_')) {
            return 'provider:youtube';
        }

        return $namespace !== '' ? 'identifier:'.$namespace : 'unknown';
    }
}
