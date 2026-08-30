<?php

declare(strict_types=1);

namespace App\Support\Providers\Destinations;

use App\Contracts\Providers\Destinations\VideoDestinationDiscovery;
use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Providers\Destinations\DTO\VideoDestinationCandidate;
use App\Models\Catalog\Recording;
use App\Models\Provider;
use App\Models\ProviderDestination;
use App\Support\Providers\Configuration\ProviderRuntimeConfiguration;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class YouTubeDestinationWorkbench
{
    public function __construct(
        private readonly VideoDestinationDiscovery $discovery,
        private readonly ProviderRuntimeConfiguration $runtimeConfiguration,
    ) {}

    /** @return array{recording:Recording,candidates:list<VideoDestinationCandidate>} */
    public function search(string $recordingId): array
    {
        $this->runtimeConfiguration->apply('youtube');
        $recording = Recording::query()->with('artists')->find($recordingId);
        if (! $recording instanceof Recording) {
            throw new RuntimeException('Canonical Recording was not found.');
        }

        return ['recording' => $recording, 'candidates' => $this->discovery->candidates($recording)];
    }

    public function approve(string $providerId, string $recordingId, string $videoId): ProviderDestination
    {
        $this->runtimeConfiguration->apply('youtube');
        $provider = $this->youtubeProvider($providerId);
        $recording = $this->recording($recordingId);
        $candidate = $this->discovery->verify($videoId, $recording);

        return DB::transaction(static fn (): ProviderDestination => ProviderDestination::query()->updateOrCreate(
            [
                'provider_id' => $provider->getKey(),
                'provider_resource_id' => $candidate->resourceId,
                'entity_type' => EntityType::Recording->value,
                'entity_id' => $recording->getKey(),
            ],
            [
                'url' => $candidate->url,
                'title' => $candidate->title,
                'channel_id' => $candidate->channelId,
                'channel_title' => $candidate->channelTitle,
                'duration_ms' => $candidate->durationMs,
                'is_embeddable' => $candidate->embeddable,
                'privacy_status' => $candidate->privacyStatus,
                'match_score' => $candidate->score,
                'review_state' => 'approved',
                'evidence' => $candidate->evidence,
                'verified_at' => now(),
                'last_checked_at' => now(),
            ],
        ));
    }

    public function reverify(string $destinationId): ProviderDestination
    {
        $this->runtimeConfiguration->apply('youtube');
        $destination = ProviderDestination::query()->with('provider')->find($destinationId);
        if (! $destination instanceof ProviderDestination) {
            throw new RuntimeException('Provider destination was not found.');
        }

        $provider = $destination->getRelation('provider');
        if (! $provider instanceof Provider || $provider->slug !== 'youtube' || ! $provider->is_enabled) {
            throw new RuntimeException('Enabled YouTube provider registry row was not found.');
        }

        $entityType = $destination->getAttribute('entity_type');
        if ($entityType !== EntityType::Recording) {
            throw new RuntimeException('YouTube destination is not attached to a canonical Recording.');
        }

        $recording = $this->recording((string) $destination->getAttribute('entity_id'));
        $candidate = $this->discovery->inspect(
            (string) $destination->getAttribute('provider_resource_id'),
            $recording,
        );
        $checkedAt = now();

        if ($candidate === null) {
            $evidence = $destination->getAttribute('evidence');
            $existingEvidence = is_array($evidence) ? $evidence : [];
            $destination->forceFill([
                'is_embeddable' => false,
                'privacy_status' => null,
                'evidence' => [
                    ...$existingEvidence,
                    'provider' => 'youtube',
                    'availability' => 'unavailable',
                    'privacy_status' => 'unknown',
                    'embeddable' => false,
                ],
                'last_checked_at' => $checkedAt,
            ])->save();

            return $destination->refresh();
        }

        $destination->forceFill([
            'url' => $candidate->url,
            'title' => $candidate->title,
            'channel_id' => $candidate->channelId,
            'channel_title' => $candidate->channelTitle,
            'duration_ms' => $candidate->durationMs,
            'is_embeddable' => $candidate->embeddable,
            'privacy_status' => $candidate->privacyStatus !== '' ? $candidate->privacyStatus : null,
            'match_score' => $candidate->score,
            'evidence' => $candidate->evidence,
            'last_checked_at' => $checkedAt,
        ])->save();

        return $destination->refresh();
    }

    private function youtubeProvider(string $providerId): Provider
    {
        $provider = Provider::query()->find($providerId);
        if (! $provider instanceof Provider || $provider->slug !== 'youtube' || ! $provider->is_enabled) {
            throw new RuntimeException('Enabled YouTube provider registry row was not found.');
        }

        return $provider;
    }

    private function recording(string $recordingId): Recording
    {
        $recording = Recording::query()->with('artists')->find($recordingId);
        if (! $recording instanceof Recording) {
            throw new RuntimeException('Canonical Recording was not found.');
        }

        return $recording;
    }
}
