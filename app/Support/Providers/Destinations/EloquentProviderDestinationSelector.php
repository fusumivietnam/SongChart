<?php

declare(strict_types=1);

namespace App\Support\Providers\Destinations;

use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Providers\Destinations\DTO\ProviderDestinationSnapshot;
use App\Domain\Providers\Destinations\ProviderDestinationPreference;
use App\Domain\Providers\Enums\ProviderStatus;
use App\Models\Provider;
use App\Models\ProviderDestination;
use Carbon\CarbonImmutable;
use DateTimeInterface;

final readonly class EloquentProviderDestinationSelector
{
    public function __construct(private ProviderDestinationPreference $preference) {}

    public function select(EntityType $entityType, string $entityId): ?SelectedProviderDestination
    {
        $destinations = ProviderDestination::query()
            ->where('entity_type', $entityType->value)
            ->where('entity_id', $entityId)
            ->with('provider')
            ->get();

        /** @var list<ProviderDestinationSnapshot> $snapshots */
        $snapshots = [];
        /** @var array<string, ProviderDestination> $modelsById */
        $modelsById = [];

        foreach ($destinations as $destination) {
            if (! $destination instanceof ProviderDestination) {
                continue;
            }

            $provider = $destination->getRelation('provider');
            if (! $provider instanceof Provider) {
                continue;
            }

            $privacyStatus = $destination->getAttribute('privacy_status');
            $url = $destination->getAttribute('url');
            $verifiedAt = $destination->getAttribute('verified_at');
            $lastCheckedAt = $destination->getAttribute('last_checked_at');

            $snapshot = new ProviderDestinationSnapshot(
                id: (string) $destination->getKey(),
                providerKey: $provider->slug,
                providerApproved: $provider->status === ProviderStatus::Approved,
                providerEnabled: $provider->is_enabled,
                reviewState: (string) $destination->getAttribute('review_state'),
                privacyStatus: is_string($privacyStatus) ? $privacyStatus : null,
                embeddable: $destination->getAttribute('is_embeddable') === true,
                url: is_string($url) ? $url : null,
                resourceId: (string) $destination->getAttribute('provider_resource_id'),
                matchScore: (int) $destination->getAttribute('match_score'),
                verifiedAt: $verifiedAt instanceof DateTimeInterface ? $verifiedAt : null,
                lastCheckedAt: $lastCheckedAt instanceof DateTimeInterface ? $lastCheckedAt : null,
            );

            $snapshots[] = $snapshot;
            $modelsById[$snapshot->id] = $destination;
        }

        $now = CarbonImmutable::now();
        $selected = $this->preference->select($snapshots, $now);
        if ($selected === null || ! isset($modelsById[$selected->id])) {
            return null;
        }

        return new SelectedProviderDestination(
            destination: $modelsById[$selected->id],
            fresh: $this->preference->isFresh($selected, $now),
            canEmbed: $this->preference->canEmbed($selected, $now),
        );
    }
}
