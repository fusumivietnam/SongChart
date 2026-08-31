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
        return $this->project($entityType, $entityId)->selection;
    }

    public function project(EntityType $entityType, string $entityId): PublicProviderDestinationProjection
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
        /** @var array<string, true> $issueCodes */
        $issueCodes = [];
        $now = CarbonImmutable::now();

        foreach ($destinations as $destination) {
            $provider = $destination->getRelation('provider');
            if (! $provider instanceof Provider) {
                continue;
            }

            $snapshot = $this->snapshot($destination, $provider);
            $snapshots[] = $snapshot;
            $modelsById[$snapshot->id] = $destination;

            foreach ($this->preference->publicEligibilityIssues($snapshot, $now) as $issue) {
                $issueCodes[$issue] = true;
            }
        }

        $selected = $this->preference->select($snapshots, $now);
        if ($selected === null || ! isset($modelsById[$selected->id])) {
            $reasons = array_keys($issueCodes);
            sort($reasons);

            return new PublicProviderDestinationProjection(
                state: PublicProviderDestinationProjection::STATE_NO_SELECTION,
                selection: null,
                reasonCodes: $reasons,
            );
        }

        $fresh = $this->preference->isFresh($selected, $now);
        $canEmbed = $this->preference->canEmbed($selected, $now);
        $selection = new SelectedProviderDestination(
            destination: $modelsById[$selected->id],
            fresh: $fresh,
            canEmbed: $canEmbed,
        );

        return new PublicProviderDestinationProjection(
            state: $canEmbed
                ? PublicProviderDestinationProjection::STATE_PLAYABLE
                : PublicProviderDestinationProjection::STATE_OUTBOUND_ONLY,
            selection: $selection,
            reasonCodes: [
                $canEmbed
                    ? PublicProviderDestinationProjection::REASON_SELECTED_EMBEDDABLE
                    : PublicProviderDestinationProjection::REASON_SELECTED_OUTBOUND_ONLY,
            ],
        );
    }

    private function snapshot(ProviderDestination $destination, Provider $provider): ProviderDestinationSnapshot
    {
        $providerStatus = $provider->getAttribute('status');
        $privacyStatus = $destination->getAttribute('privacy_status');
        $url = $destination->getAttribute('url');
        $verifiedAt = $destination->getAttribute('verified_at');
        $lastCheckedAt = $destination->getAttribute('last_checked_at');

        return new ProviderDestinationSnapshot(
            id: (string) $destination->getKey(),
            providerKey: $provider->slug,
            providerApproved: $providerStatus instanceof ProviderStatus
                && $providerStatus === ProviderStatus::Approved,
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
    }
}
