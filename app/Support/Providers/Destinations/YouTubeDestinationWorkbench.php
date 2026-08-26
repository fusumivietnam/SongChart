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
        $provider = Provider::query()->find($providerId);
        if (! $provider instanceof Provider || $provider->slug !== 'youtube' || ! $provider->is_enabled) {
            throw new RuntimeException('Enabled YouTube provider registry row was not found.');
        }
        $recording = Recording::query()->with('artists')->find($recordingId);
        if (! $recording instanceof Recording) {
            throw new RuntimeException('Canonical Recording was not found.');
        }
        $candidate = $this->discovery->verify($videoId, $recording);
        if (! $candidate->embeddable) {
            throw new RuntimeException('Selected YouTube video is not embeddable and cannot be approved as the primary Stage 17.7 destination.');
        }

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
}
