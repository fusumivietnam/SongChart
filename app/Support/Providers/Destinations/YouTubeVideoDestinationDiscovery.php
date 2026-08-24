<?php

declare(strict_types=1);

namespace App\Support\Providers\Destinations;

use App\Contracts\Providers\Destinations\VideoDestinationDiscovery;
use App\Domain\Providers\Destinations\DTO\VideoDestinationCandidate;
use App\Models\Catalog\Artist;
use App\Models\Catalog\Recording;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final class YouTubeVideoDestinationDiscovery implements VideoDestinationDiscovery
{
    public function __construct(private readonly YouTubeQuotaGuard $quota) {}

    public function candidates(Recording $recording, int $limit = 5): array
    {
        $this->assertConfigured();
        $limit = max(1, min(10, $limit));
        $query = trim($this->artistCredit($recording).' '.$recording->title);

        $this->quota->consume('search.list');
        $search = $this->client()->get('/youtube/v3/search', [
            'part' => 'snippet',
            'type' => 'video',
            'videoEmbeddable' => 'true',
            'safeSearch' => 'moderate',
            'maxResults' => $limit,
            'q' => $query,
            'key' => config('songchart.providers.youtube.api_key'),
        ])->throw()->json();

        $ids = [];
        foreach ((array) ($search['items'] ?? []) as $item) {
            if (is_array($item) && is_array($item['id'] ?? null) && is_string($item['id']['videoId'] ?? null)) {
                $ids[] = $item['id']['videoId'];
            }
        }
        if ($ids === []) {
            return [];
        }

        return $this->verifiedCandidates($ids, $recording);
    }

    public function verify(string $resourceId, Recording $recording): VideoDestinationCandidate
    {
        $this->assertConfigured();
        $items = $this->verifiedCandidates([$resourceId], $recording);
        if ($items === []) {
            throw new RuntimeException('YouTube video was not found or is not an actionable public video.');
        }

        return $items[0];
    }

    /**
     * @param  list<string>  $ids
     * @return list<VideoDestinationCandidate>
     */
    private function verifiedCandidates(array $ids, Recording $recording): array
    {
        $this->quota->consume('videos.list');
        $response = $this->client()->get('/youtube/v3/videos', [
            'part' => 'snippet,contentDetails,status',
            'id' => implode(',', array_slice($ids, 0, 50)),
            'key' => config('songchart.providers.youtube.api_key'),
        ])->throw()->json();

        $candidates = [];
        foreach ((array) ($response['items'] ?? []) as $item) {
            if (! is_array($item) || ! is_string($item['id'] ?? null)) {
                continue;
            }
            $snippet = is_array($item['snippet'] ?? null) ? $item['snippet'] : [];
            $content = is_array($item['contentDetails'] ?? null) ? $item['contentDetails'] : [];
            $status = is_array($item['status'] ?? null) ? $item['status'] : [];
            $privacy = (string) ($status['privacyStatus'] ?? '');
            $embeddable = (bool) ($status['embeddable'] ?? false);
            if ($privacy !== 'public') {
                continue;
            }

            $durationMs = $this->durationMilliseconds((string) ($content['duration'] ?? ''));
            [$score, $evidence] = $this->score(
                recording: $recording,
                videoTitle: (string) ($snippet['title'] ?? ''),
                channelTitle: (string) ($snippet['channelTitle'] ?? ''),
                durationMs: $durationMs,
            );
            $decision = $score >= 85 ? 'recommended' : ($score >= 65 ? 'review' : 'weak');

            $candidates[] = new VideoDestinationCandidate(
                resourceId: $item['id'],
                url: 'https://www.youtube.com/watch?v='.rawurlencode($item['id']),
                title: (string) ($snippet['title'] ?? ''),
                channelId: (string) ($snippet['channelId'] ?? ''),
                channelTitle: (string) ($snippet['channelTitle'] ?? ''),
                durationMs: $durationMs,
                embeddable: $embeddable,
                privacyStatus: $privacy,
                score: $score,
                decision: $decision,
                evidence: $evidence,
            );
        }

        usort($candidates, static fn (VideoDestinationCandidate $a, VideoDestinationCandidate $b): int => $b->score <=> $a->score);

        return $candidates;
    }

    /** @return array{int,array<string,mixed>} */
    private function score(Recording $recording, string $videoTitle, string $channelTitle, ?int $durationMs): array
    {
        $needle = $this->normalize($recording->title);
        $haystack = $this->normalize($videoTitle);
        $titleScore = $needle !== '' && str_contains($haystack, $needle) ? 50 : 0;

        $artist = $this->normalize($this->artistCredit($recording));
        $artistScore = $artist !== '' && (str_contains($haystack, $artist) || str_contains($this->normalize($channelTitle), $artist)) ? 25 : 0;

        $durationScore = 0;
        $canonicalDuration = $recording->duration_ms;
        $durationDelta = null;
        if (is_int($canonicalDuration) && $canonicalDuration > 0 && $durationMs !== null) {
            $durationDelta = abs($canonicalDuration - $durationMs);
            $durationScore = $durationDelta <= 3000 ? 20 : ($durationDelta <= 10000 ? 10 : 0);
        }

        $officialMetadataSignal = preg_match('/\b(official|topic|vevo)\b/i', $videoTitle.' '.$channelTitle) === 1 ? 5 : 0;
        $score = min(100, $titleScore + $artistScore + $durationScore + $officialMetadataSignal);

        return [$score, [
            'title_match' => $titleScore,
            'artist_match' => $artistScore,
            'duration_match' => $durationScore,
            'duration_delta_ms' => $durationDelta,
            'official_metadata_signal' => $officialMetadataSignal,
            'note' => 'Official metadata signal is heuristic only; human approval remains authoritative in Stage 17.7.',
        ]];
    }

    private function artistCredit(Recording $recording): string
    {
        $recording->loadMissing('artists');

        return trim($recording->artists->map(function (Artist $artist): string {
            $pivot = $artist->getRelation('pivot');
            $creditedName = $artist->name;
            $joinPhrase = '';

            if ($pivot instanceof Pivot) {
                $pivotCreditedName = $pivot->getAttribute('credited_name');
                $pivotJoinPhrase = $pivot->getAttribute('join_phrase');
                if (is_string($pivotCreditedName) && $pivotCreditedName !== '') {
                    $creditedName = $pivotCreditedName;
                }
                if (is_string($pivotJoinPhrase)) {
                    $joinPhrase = $pivotJoinPhrase;
                }
            }

            return $creditedName.$joinPhrase;
        })->implode(''));
    }

    private function normalize(string $value): string
    {
        return trim((string) preg_replace('/[^\pL\pN]+/u', ' ', mb_strtolower($value)));
    }

    private function durationMilliseconds(string $duration): ?int
    {
        if ($duration === '' || preg_match('/^PT(?:(\d+)H)?(?:(\d+)M)?(?:(\d+)S)?$/', $duration, $m) !== 1) {
            return null;
        }

        return (((int) ($m[1] ?? 0) * 3600) + ((int) ($m[2] ?? 0) * 60) + (int) ($m[3] ?? 0)) * 1000;
    }

    private function assertConfigured(): void
    {
        if (! (bool) config('songchart.providers.youtube.enabled')) {
            throw new RuntimeException('YouTube provider is disabled. Set YOUTUBE_ENABLED=true only after policy and quota configuration are accepted.');
        }
        if (trim((string) config('songchart.providers.youtube.api_key')) === '') {
            throw new RuntimeException('YouTube API key is not configured.');
        }
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl((string) config('songchart.providers.youtube.base_url'))
            ->acceptJson()
            ->connectTimeout((int) config('songchart.providers.youtube.connect_timeout_seconds', 5))
            ->timeout((int) config('songchart.providers.youtube.timeout_seconds', 15));
    }
}
