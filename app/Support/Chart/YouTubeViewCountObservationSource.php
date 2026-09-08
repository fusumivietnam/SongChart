<?php

declare(strict_types=1);

namespace App\Support\Chart;

use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Chart\DTO\ChartMetricObservation;
use App\Models\ProviderDestination;
use App\Support\Providers\Configuration\ProviderCredentialResolver;
use App\Support\Providers\Configuration\ProviderRuntimeConfiguration;
use App\Support\Providers\Destinations\YouTubeQuotaGuard;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final readonly class YouTubeViewCountObservationSource
{
    public const METRIC = 'youtube_video_view_count';
    public const METRIC_UNIT = 'views';
    public const SEMANTICS_VERSION = 'youtube-view-count-2026-08-24';

    public function __construct(
        private YouTubeQuotaGuard $quota,
        private ProviderCredentialResolver $credentials,
        private ProviderRuntimeConfiguration $runtimeConfiguration,
    ) {}

    /** @return list<ChartMetricObservation> */
    public function fetchApproved(): array
    {
        $this->runtimeConfiguration->apply('youtube');
        $this->assertConfigured();

        $destinations = ProviderDestination::query()
            ->where('entity_type', EntityType::Recording->value)
            ->where('review_state', 'approved')
            ->where('privacy_status', 'public')
            ->whereHas('provider', static fn ($query) => $query->where('slug', 'youtube')->where('is_enabled', true))
            ->orderBy('provider_resource_id')
            ->get(['id', 'entity_id', 'provider_resource_id']);

        $observations = [];
        foreach ($destinations->chunk(50) as $chunk) {
            $byVideo = $chunk->keyBy(fn (ProviderDestination $destination): string => (string) $destination->provider_resource_id);
            $ids = $byVideo->keys()->all();
            if ($ids === []) {
                continue;
            }

            $this->quota->consume('videos.list');
            $fetchedAt = new DateTimeImmutable('now', new DateTimeZone('UTC'));
            $response = $this->client()->get('/youtube/v3/videos', [
                'part' => 'statistics,status',
                'id' => implode(',', $ids),
                'key' => $this->apiKey(),
            ])->throw()->json();

            foreach ((array) ($response['items'] ?? []) as $item) {
                if (! is_array($item) || ! is_string($item['id'] ?? null)) {
                    continue;
                }
                $videoId = $item['id'];
                $destination = $byVideo->get($videoId);
                $status = is_array($item['status'] ?? null) ? $item['status'] : [];
                $statistics = is_array($item['statistics'] ?? null) ? $item['statistics'] : [];
                $rawCount = $statistics['viewCount'] ?? null;

                if (! $destination instanceof ProviderDestination || ($status['privacyStatus'] ?? null) !== 'public') {
                    continue;
                }
                if (! is_string($rawCount) || preg_match('/^\d+$/', $rawCount) !== 1 || strlen($rawCount) > 18) {
                    throw new RuntimeException('YouTube viewCount is missing or outside SongChart integer bounds.');
                }

                $value = (int) $rawCount;
                $stamp = $fetchedAt->format(DATE_ATOM);
                $observationId = hash('sha256', implode('|', [
                    'youtube', $videoId, self::METRIC, self::SEMANTICS_VERSION, $rawCount, $stamp,
                ]));

                $observations[] = new ChartMetricObservation(
                    observationId: $observationId,
                    canonicalRecordingId: (string) $destination->entity_id,
                    provider: 'youtube',
                    providerItemId: $videoId,
                    metric: self::METRIC,
                    value: $value,
                    observedAt: $fetchedAt,
                    metricUnit: self::METRIC_UNIT,
                    metricSemanticsVersion: self::SEMANTICS_VERSION,
                    fetchedAt: $fetchedAt,
                    sourceReference: 'youtube:videos.list:'.$videoId.':statistics',
                );
            }
        }

        return $observations;
    }

    private function assertConfigured(): void
    {
        if (! (bool) config('songchart.providers.youtube.enabled')) {
            throw new RuntimeException('YouTube provider is disabled.');
        }
        if ($this->credentials->configuredCount('youtube', 'api_key') === 0 && trim((string) config('songchart.providers.youtube.api_key')) === '') {
            throw new RuntimeException('YouTube API credential pool is not configured.');
        }
    }

    private function apiKey(): string
    {
        return $this->credentials->resolve('youtube', 'api_key', trim((string) config('songchart.providers.youtube.api_key')) ?: null);
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl((string) config('songchart.providers.youtube.base_url'))->acceptJson()
            ->connectTimeout((int) config('songchart.providers.youtube.connect_timeout_seconds', 5))
            ->timeout((int) config('songchart.providers.youtube.timeout_seconds', 15));
    }
}
