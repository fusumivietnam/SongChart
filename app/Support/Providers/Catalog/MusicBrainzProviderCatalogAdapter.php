<?php

declare(strict_types=1);

namespace App\Support\Providers\Catalog;

use App\Contracts\Providers\Catalog\ProviderCatalogAdapter;
use App\Contracts\Providers\Rate\ProviderRatePolicyRegistry;
use App\Contracts\Providers\Rate\ProviderRequestGate;
use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Catalog\Enums\RelationshipType;
use App\Domain\Providers\Catalog\DTO\NormalizedProviderEntity;
use App\Domain\Providers\Catalog\DTO\ProviderImportContext;
use App\Domain\Providers\Catalog\DTO\ProviderPage;
use App\Domain\Providers\Catalog\DTO\ProviderPayload;
use App\Domain\Providers\Catalog\Enums\ProviderCatalogCapability;
use App\Domain\Providers\Catalog\Exceptions\ProviderRequestException;
use App\Domain\Providers\Normalization\DTO\NormalizedEntityDataFactory;
use App\Domain\Providers\Normalization\DTO\NormalizedIdentifier;
use App\Domain\Providers\Normalization\DTO\NormalizedRelationship;
use App\Domain\Providers\Normalization\ValueObjects\ProviderField;
use DateTimeImmutable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

final class MusicBrainzProviderCatalogAdapter implements ProviderCatalogAdapter
{
    private const PROVIDER_SLUG = 'musicbrainz';

    public function __construct(
        private readonly ProviderRatePolicyRegistry $ratePolicies,
        private readonly ProviderRequestGate $requestGate,
    ) {}

    public function providerSlug(): string
    {
        return self::PROVIDER_SLUG;
    }

    public function capabilities(): array
    {
        return [
            ProviderCatalogCapability::ArtistLookup,
            ProviderCatalogCapability::ReleaseGroupLookup,
            ProviderCatalogCapability::ReleaseLookup,
            ProviderCatalogCapability::RecordingLookup,
            ProviderCatalogCapability::WorkLookup,
            ProviderCatalogCapability::Search,
        ];
    }

    public function fetchPage(ProviderImportContext $context, ?string $cursor = null): ProviderPage
    {
        $this->assertConfigured();

        return match ($context->entityType) {
            EntityType::Artist => $this->fetchArtistPage($context, $cursor),
            EntityType::ReleaseGroup => $this->fetchReleaseGroupPage($context, $cursor),
            EntityType::Release => $this->fetchReleasePage($context, $cursor),
            EntityType::Recording => $this->fetchRecordingPage($context, $cursor),
            EntityType::Work => $this->fetchWorkPage($context, $cursor),
            default => throw new RuntimeException('Stage 17.6 MusicBrainz adapter supports Artist, Release Group, Release, Recording and Work.'),
        };
    }

    public function normalize(ProviderPayload $payload): NormalizedProviderEntity
    {
        if ($payload->providerSlug !== self::PROVIDER_SLUG) {
            throw new RuntimeException('MusicBrainz adapter received a payload owned by another provider.');
        }

        return match ($payload->entityType) {
            EntityType::Artist => $this->normalizeArtist($payload),
            EntityType::ReleaseGroup => $this->normalizeReleaseGroup($payload),
            EntityType::Release => $this->normalizeRelease($payload),
            EntityType::Recording => $this->normalizeRecording($payload),
            EntityType::Work => $this->normalizeWork($payload),
            default => throw new RuntimeException('Stage 17.6 MusicBrainz normalization supports Artist, Release Group, Release, Recording and Work.'),
        };
    }

    private function fetchArtistPage(ProviderImportContext $context, ?string $cursor): ProviderPage
    {
        if ($context->externalId !== null) {
            $response = $this->request('/artist/'.rawurlencode($context->externalId), [
                'inc' => 'artist-rels+url-rels+aliases',
                'fmt' => 'json',
            ], 'artist.lookup');

            return new ProviderPage(items: [$this->artistPayload($this->decode($response))], complete: true);
        }

        return $this->searchPage($context, $cursor, '/artist', 'artists', 'artist.search', fn (array $item): ProviderPayload => $this->artistPayload($item));
    }

    private function fetchReleaseGroupPage(ProviderImportContext $context, ?string $cursor): ProviderPage
    {
        if ($context->externalId !== null) {
            $response = $this->request('/release-group/'.rawurlencode($context->externalId), [
                'inc' => 'artist-credits',
                'fmt' => 'json',
            ], 'release-group.lookup');

            return new ProviderPage(items: [$this->releaseGroupPayload($this->decode($response))], complete: true);
        }

        return $this->searchPage($context, $cursor, '/release-group', 'release-groups', 'release-group.search', fn (array $item): ProviderPayload => $this->releaseGroupPayload($item));
    }

    private function fetchReleasePage(ProviderImportContext $context, ?string $cursor): ProviderPage
    {
        if ($context->externalId !== null) {
            $response = $this->request('/release/'.rawurlencode($context->externalId), [
                'inc' => 'release-groups+artist-credits+labels+media',
                'fmt' => 'json',
            ], 'release.lookup');

            return new ProviderPage(items: [$this->releasePayload($this->decode($response))], complete: true);
        }

        return $this->searchPage($context, $cursor, '/release', 'releases', 'release.search', fn (array $item): ProviderPayload => $this->releasePayload($item));
    }

    private function fetchRecordingPage(ProviderImportContext $context, ?string $cursor): ProviderPage
    {
        if ($context->externalId !== null) {
            $response = $this->request('/recording/'.rawurlencode($context->externalId), [
                'inc' => 'artist-credits+isrcs+releases+work-rels',
                'fmt' => 'json',
            ], 'recording.lookup');

            return new ProviderPage(items: [$this->recordingPayload($this->decode($response))], complete: true);
        }

        return $this->searchPage($context, $cursor, '/recording', 'recordings', 'recording.search', fn (array $item): ProviderPayload => $this->recordingPayload($item));
    }

    private function fetchWorkPage(ProviderImportContext $context, ?string $cursor): ProviderPage
    {
        if ($context->externalId !== null) {
            $response = $this->request('/work/'.rawurlencode($context->externalId), [
                'inc' => 'artist-rels+recording-rels+aliases',
                'fmt' => 'json',
            ], 'work.lookup');

            return new ProviderPage(items: [$this->workPayload($this->decode($response))], complete: true);
        }

        return $this->searchPage($context, $cursor, '/work', 'works', 'work.search', fn (array $item): ProviderPayload => $this->workPayload($item));
    }

    /** @param callable(array<string,mixed>):ProviderPayload $payloadFactory */
    private function searchPage(ProviderImportContext $context, ?string $cursor, string $path, string $collectionKey, string $operation, callable $payloadFactory): ProviderPage
    {
        $query = trim((string) $context->query);
        if ($query === '') {
            throw new RuntimeException('MusicBrainz search requires a query or external MBID.');
        }

        $offset = max(0, (int) ($cursor ?? 0));
        $limit = max(1, min(100, $context->pageSize));
        $response = $this->request($path, [
            'query' => $query,
            'limit' => $limit,
            'offset' => $offset,
            'fmt' => 'json',
        ], $operation);
        $data = $this->decode($response);
        $rawItems = is_array($data[$collectionKey] ?? null) ? $data[$collectionKey] : [];
        $items = [];

        foreach ($rawItems as $item) {
            if (is_array($item) && is_string($item['id'] ?? null) && $item['id'] !== '') {
                $items[] = $payloadFactory($item);
            }
        }

        $count = max(0, (int) ($data['count'] ?? count($items)));
        $nextOffset = $offset + count($items);
        $complete = $items === [] || $nextOffset >= $count;

        return new ProviderPage(items: $items, nextCursor: $complete ? null : (string) $nextOffset, complete: $complete);
    }

    private function normalizeArtist(ProviderPayload $payload): NormalizedProviderEntity
    {
        $lifeSpan = is_array($payload->data['life-span'] ?? null) ? $payload->data['life-span'] : [];
        $aliases = is_array($payload->data['aliases'] ?? null) ? array_values(array_filter(array_map(static fn ($alias): ?string => is_array($alias) && is_string($alias['name'] ?? null) ? $alias['name'] : null, $payload->data['aliases']))) : [];
        $externalUrls = [];
        $relationships = [];

        foreach ((is_array($payload->data['relations'] ?? null) ? $payload->data['relations'] : []) as $relation) {
            if (! is_array($relation)) {
                continue;
            }

            $targetType = $relation['target-type'] ?? null;
            if ($targetType === 'artist' && ($relation['type'] ?? null) === 'member of band') {
                $artist = is_array($relation['artist'] ?? null) ? $relation['artist'] : [];
                $mbid = $artist['id'] ?? null;
                if (! is_string($mbid) || $mbid === '') {
                    continue;
                }

                $direction = (string) ($relation['direction'] ?? 'forward');
                $type = $direction === 'backward' ? RelationshipType::HasMember : RelationshipType::MemberOf;
                $relationships[] = new NormalizedRelationship($type->value, EntityType::Artist, $mbid, [
                    'target_name' => ProviderField::provided((string) ($artist['name'] ?? '')),
                    'target_sort_name' => ProviderField::provided((string) ($artist['sort-name'] ?? '')),
                    'target_type' => ProviderField::provided((string) ($artist['type'] ?? '')),
                    'target_country_code' => ProviderField::provided((string) ($artist['country'] ?? '')),
                    'begin' => ProviderField::provided((string) ($relation['begin'] ?? '')),
                    'end' => ProviderField::provided((string) ($relation['end'] ?? '')),
                    'ended' => ProviderField::provided((bool) ($relation['ended'] ?? false)),
                ]);
            } elseif ($targetType === 'url') {
                $url = is_array($relation['url'] ?? null) ? ($relation['url']['resource'] ?? null) : null;
                if (is_string($url) && $url !== '') {
                    $externalUrls[] = ['type' => (string) ($relation['type'] ?? 'external'), 'url' => $url];
                }
            }
        }

        $attributes = [
            'name' => $payload->data['name'] ?? null,
            'sort_name' => $payload->data['sort-name'] ?? null,
            'disambiguation' => $payload->data['disambiguation'] ?? null,
            'country_code' => $payload->data['country'] ?? null,
            'begin_date' => $lifeSpan['begin'] ?? null,
            'end_date' => $lifeSpan['end'] ?? null,
            'ended' => $lifeSpan['ended'] ?? null,
            'type' => $payload->data['type'] ?? null,
            'aliases' => $aliases,
            'external_urls' => $externalUrls,
        ];

        $identifiers = [new NormalizedIdentifier('musicbrainz_artist', $payload->externalId)];
        foreach ($externalUrls as $externalUrl) {
            $identifiers[] = new NormalizedIdentifier('url:'.str_replace(' ', '_', strtolower((string) $externalUrl['type'])), (string) $externalUrl['url']);
        }

        return new NormalizedProviderEntity(
            providerSlug: self::PROVIDER_SLUG,
            entityType: EntityType::Artist,
            externalId: $payload->externalId,
            data: NormalizedEntityDataFactory::fromProviderAttributes(EntityType::Artist, $attributes),
            identifiers: $identifiers,
            relationships: $relationships,
            normalizerVersion: 'musicbrainz-artist-v2',
        );
    }

    private function normalizeReleaseGroup(ProviderPayload $payload): NormalizedProviderEntity
    {
        $attributes = [
            'title' => $payload->data['title'] ?? null,
            'primary_type' => $payload->data['primary-type'] ?? null,
            'secondary_types' => is_array($payload->data['secondary-types'] ?? null) ? array_values($payload->data['secondary-types']) : null,
            'first_release_date' => $payload->data['first-release-date'] ?? null,
            'disambiguation' => $payload->data['disambiguation'] ?? null,
        ];

        return new NormalizedProviderEntity(
            providerSlug: self::PROVIDER_SLUG,
            entityType: EntityType::ReleaseGroup,
            externalId: $payload->externalId,
            data: NormalizedEntityDataFactory::fromProviderAttributes(EntityType::ReleaseGroup, $attributes),
            identifiers: [new NormalizedIdentifier('musicbrainz_release_group', $payload->externalId)],
            relationships: $this->artistCreditRelationships($payload->data),
            normalizerVersion: 'musicbrainz-release-group-v1',
        );
    }

    private function normalizeRelease(ProviderPayload $payload): NormalizedProviderEntity
    {
        $releaseGroup = is_array($payload->data['release-group'] ?? null) ? $payload->data['release-group'] : [];
        $labelInfo = is_array($payload->data['label-info'] ?? null) ? $payload->data['label-info'] : [];
        $firstLabel = isset($labelInfo[0]) && is_array($labelInfo[0]) ? $labelInfo[0] : [];
        $media = is_array($payload->data['media'] ?? null) ? $payload->data['media'] : [];
        $trackCount = 0;
        foreach ($media as $medium) {
            if (is_array($medium)) {
                $trackCount += max(0, (int) ($medium['track-count'] ?? 0));
            }
        }

        $releaseGroupMbid = is_string($releaseGroup['id'] ?? null) ? $releaseGroup['id'] : null;
        $coverArtArchive = is_array($payload->data['cover-art-archive'] ?? null) ? $payload->data['cover-art-archive'] : [];
        $coverArtFrontUrl = ($coverArtArchive['front'] ?? false) === true
            ? 'https://coverartarchive.org/release/'.rawurlencode($payload->externalId).'/front-500'
            : null;
        $relationships = $this->artistCreditRelationships($payload->data);
        if ($releaseGroupMbid !== null && $releaseGroupMbid !== '') {
            $relationships[] = new NormalizedRelationship(RelationshipType::PartOf->value, EntityType::ReleaseGroup, $releaseGroupMbid);
        }

        $attributes = [
            'title' => $payload->data['title'] ?? null,
            'barcode' => $payload->data['barcode'] ?? null,
            'catalog_number' => $firstLabel['catalog-number'] ?? null,
            'country_code' => $payload->data['country'] ?? null,
            'release_date' => $payload->data['date'] ?? null,
            'status' => $payload->data['status'] ?? null,
            'primary_type' => $releaseGroup['primary-type'] ?? null,
            'release_group_mbid' => $releaseGroupMbid,
            'packaging' => $payload->data['packaging'] ?? null,
            'track_count' => $trackCount > 0 ? $trackCount : null,
            'cover_art_archive_front_url' => $coverArtFrontUrl,
        ];

        return new NormalizedProviderEntity(
            providerSlug: self::PROVIDER_SLUG,
            entityType: EntityType::Release,
            externalId: $payload->externalId,
            data: NormalizedEntityDataFactory::fromProviderAttributes(EntityType::Release, $attributes),
            identifiers: [new NormalizedIdentifier('musicbrainz_release', $payload->externalId)],
            relationships: $relationships,
            normalizerVersion: 'musicbrainz-release-v1',
        );
    }

    private function normalizeRecording(ProviderPayload $payload): NormalizedProviderEntity
    {
        $isrcs = is_array($payload->data['isrcs'] ?? null) ? array_values(array_filter($payload->data['isrcs'], 'is_string')) : [];
        $releases = is_array($payload->data['releases'] ?? null) ? $payload->data['releases'] : [];
        $relationships = $this->artistCreditRelationships($payload->data);

        foreach ($releases as $release) {
            if (! is_array($release)) {
                continue;
            }
            $releaseMbid = $release['id'] ?? null;
            if (is_string($releaseMbid) && $releaseMbid !== '') {
                $relationships[] = new NormalizedRelationship(RelationshipType::RelatedTo->value, EntityType::Release, $releaseMbid);
            }
        }

        foreach ((is_array($payload->data['relations'] ?? null) ? $payload->data['relations'] : []) as $relation) {
            if (! is_array($relation) || ($relation['target-type'] ?? null) !== 'work') {
                continue;
            }
            $work = is_array($relation['work'] ?? null) ? $relation['work'] : [];
            $workMbid = $work['id'] ?? null;
            if (! is_string($workMbid) || $workMbid === '') {
                continue;
            }
            $relationships[] = new NormalizedRelationship(RelationshipType::RecordingOf->value, EntityType::Work, $workMbid, [
                'target_title' => ProviderField::provided((string) ($work['title'] ?? '')),
                'target_type' => ProviderField::provided((string) ($work['type'] ?? '')),
                'target_language_code' => ProviderField::provided((string) (($work['languages'][0] ?? null) ?: ($work['language'] ?? ''))),
                'target_iswcs' => ProviderField::provided(is_array($work['iswcs'] ?? null) ? array_values($work['iswcs']) : []),
            ]);
        }

        $identifiers = [new NormalizedIdentifier('musicbrainz_recording', $payload->externalId)];
        foreach ($isrcs as $isrc) {
            $normalizedIsrc = strtoupper(trim($isrc));
            if ($normalizedIsrc !== '') {
                $identifiers[] = new NormalizedIdentifier('isrc', $normalizedIsrc);
            }
        }

        $firstReleaseDate = null;
        foreach ($releases as $release) {
            if (is_array($release) && is_string($release['date'] ?? null) && $release['date'] !== '') {
                $candidate = $release['date'];
                if ($firstReleaseDate === null || strcmp($candidate, $firstReleaseDate) < 0) {
                    $firstReleaseDate = $candidate;
                }
            }
        }

        $attributes = [
            'title' => $payload->data['title'] ?? null,
            'duration_ms' => $payload->data['length'] ?? null,
            'disambiguation' => $payload->data['disambiguation'] ?? null,
            'isrc' => $isrcs[0] ?? null,
            'first_release_date' => $firstReleaseDate,
            'video' => $payload->data['video'] ?? null,
        ];

        return new NormalizedProviderEntity(
            providerSlug: self::PROVIDER_SLUG,
            entityType: EntityType::Recording,
            externalId: $payload->externalId,
            data: NormalizedEntityDataFactory::fromProviderAttributes(EntityType::Recording, $attributes),
            identifiers: $identifiers,
            relationships: $relationships,
            normalizerVersion: 'musicbrainz-recording-v1',
        );
    }

    private function normalizeWork(ProviderPayload $payload): NormalizedProviderEntity
    {
        $iswcs = is_array($payload->data['iswcs'] ?? null) ? array_values(array_filter($payload->data['iswcs'], 'is_string')) : [];
        $languages = is_array($payload->data['languages'] ?? null) ? array_values(array_filter($payload->data['languages'], 'is_string')) : [];
        $attributes = [
            'title' => $payload->data['title'] ?? null,
            'subtitle' => $payload->data['disambiguation'] ?? null,
            'language_code' => $languages[0] ?? null,
            'iswc' => $iswcs[0] ?? null,
            'type' => $payload->data['type'] ?? null,
            'first_release_date' => null,
        ];
        $identifiers = [new NormalizedIdentifier('musicbrainz_work', $payload->externalId)];
        foreach ($iswcs as $iswc) {
            $identifiers[] = new NormalizedIdentifier('iswc', strtoupper(trim($iswc)));
        }

        return new NormalizedProviderEntity(
            providerSlug: self::PROVIDER_SLUG,
            entityType: EntityType::Work,
            externalId: $payload->externalId,
            data: NormalizedEntityDataFactory::fromProviderAttributes(EntityType::Work, $attributes),
            identifiers: $identifiers,
            normalizerVersion: 'musicbrainz-work-v1',
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return list<NormalizedRelationship>
     */
    private function artistCreditRelationships(array $data): array
    {
        $credits = is_array($data['artist-credit'] ?? null) ? $data['artist-credit'] : [];
        $relationships = [];

        foreach ($credits as $index => $credit) {
            if (! is_array($credit)) {
                continue;
            }
            $artist = is_array($credit['artist'] ?? null) ? $credit['artist'] : [];
            $mbid = $artist['id'] ?? null;
            if (is_string($mbid) && $mbid !== '') {
                $relationships[] = new NormalizedRelationship(RelationshipType::PerformedBy->value, EntityType::Artist, $mbid, [
                    'position' => ProviderField::provided($index + 1),
                    'credited_name' => ProviderField::provided((string) ($credit['name'] ?? $artist['name'] ?? '')),
                    'join_phrase' => ProviderField::provided((string) ($credit['joinphrase'] ?? '')),
                ]);
            }
        }

        return $relationships;
    }

    /** @param array<string, scalar> $query */
    private function request(string $path, array $query, string $operation): Response
    {
        $policy = $this->ratePolicies->for(self::PROVIDER_SLUG, $operation);
        $this->requestGate->await($policy);

        try {
            $response = $this->client()->get($path, $query);
        } catch (ConnectionException $exception) {
            throw new ProviderRequestException(
                message: 'MusicBrainz transport failure: '.$exception->getMessage(),
                kind: 'transport',
                retryable: true,
                previous: $exception,
            );
        }

        if ($response->status() === 404) {
            throw new ProviderRequestException(message: 'MusicBrainz entity was not found.', kind: 'not-found', retryable: false, httpStatus: 404);
        }

        if (in_array($response->status(), [429, 503], true)) {
            $retryAfterSeconds = $this->retryAfterSeconds($response);
            $this->requestGate->recordCooldown(
                $policy,
                $retryAfterSeconds,
                $response->status() === 429 ? 'rate-limited' : 'temporarily-unavailable',
            );

            throw new ProviderRequestException(
                message: 'MusicBrainz rate limit or temporary service limit was reached.',
                kind: $response->status() === 429 ? 'rate-limited' : 'temporarily-unavailable',
                retryable: true,
                httpStatus: $response->status(),
                retryAfterSeconds: $retryAfterSeconds ?? $policy->defaultCooldownSeconds,
            );
        }

        if ($response->serverError()) {
            throw new ProviderRequestException(message: 'MusicBrainz request failed with HTTP '.$response->status().'.', kind: 'server-error', retryable: true, httpStatus: $response->status());
        }

        if (! $response->successful()) {
            throw new ProviderRequestException(message: 'MusicBrainz request failed with HTTP '.$response->status().'.', kind: 'request-rejected', retryable: false, httpStatus: $response->status());
        }

        return $response;
    }

    private function retryAfterSeconds(Response $response): ?int
    {
        $value = trim((string) $response->header('Retry-After'));
        if ($value === '' || ! ctype_digit($value)) {
            return null;
        }

        return max(1, min(900, (int) $value));
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl(rtrim((string) config('songchart.providers.musicbrainz.base_url'), '/'))
            ->acceptJson()
            ->withUserAgent((string) config('songchart.providers.musicbrainz.user_agent'))
            ->connectTimeout((int) config('songchart.providers.musicbrainz.connect_timeout_seconds', 5))
            ->timeout((int) config('songchart.providers.musicbrainz.timeout_seconds', 15));
    }

    /** @return array<string, mixed> */
    private function decode(Response $response): array
    {
        $data = $response->json();
        if (! is_array($data)) {
            throw new RuntimeException('MusicBrainz returned an invalid JSON response.');
        }

        return $data;
    }

    /** @param array<string, mixed> $artist */
    private function artistPayload(array $artist): ProviderPayload
    {
        return $this->payload(EntityType::Artist, $artist, 'ws2-json-artist-v1');
    }

    /** @param array<string, mixed> $releaseGroup */
    private function releaseGroupPayload(array $releaseGroup): ProviderPayload
    {
        return $this->payload(EntityType::ReleaseGroup, $releaseGroup, 'ws2-json-release-group-v1');
    }

    /** @param array<string, mixed> $release */
    private function releasePayload(array $release): ProviderPayload
    {
        return $this->payload(EntityType::Release, $release, 'ws2-json-release-v1');
    }

    /** @param array<string, mixed> $recording */
    private function recordingPayload(array $recording): ProviderPayload
    {
        return $this->payload(EntityType::Recording, $recording, 'ws2-json-recording-v1');
    }

    /** @param array<string, mixed> $work */
    private function workPayload(array $work): ProviderPayload
    {
        return $this->payload(EntityType::Work, $work, 'ws2-json-work-v1');
    }

    /** @param array<string,mixed> $data */
    private function payload(EntityType $entityType, array $data, string $schemaVersion): ProviderPayload
    {
        $externalId = $data['id'] ?? null;
        if (! is_string($externalId) || $externalId === '') {
            throw new RuntimeException('MusicBrainz '.$entityType->value.' payload is missing its MBID.');
        }

        return new ProviderPayload(
            providerSlug: self::PROVIDER_SLUG,
            entityType: $entityType,
            externalId: $externalId,
            data: $data,
            receivedAt: new DateTimeImmutable,
            schemaVersion: $schemaVersion,
        );
    }

    private function assertConfigured(): void
    {
        if (! (bool) config('songchart.providers.musicbrainz.enabled', false)) {
            throw new RuntimeException('MusicBrainz live adapter is disabled. Set MUSICBRAINZ_ENABLED=true after policy review.');
        }

        $userAgent = trim((string) config('songchart.providers.musicbrainz.user_agent'));
        if ($userAgent === '' || str_contains($userAgent, 'contact@example.com')) {
            throw new RuntimeException('MusicBrainz requires a meaningful User-Agent with real application/contact information.');
        }
    }
}
