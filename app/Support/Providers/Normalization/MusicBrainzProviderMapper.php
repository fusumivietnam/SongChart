<?php

declare(strict_types=1);

namespace App\Support\Providers\Normalization;

use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Providers\Catalog\DTO\NormalizedProviderEntity;
use App\Domain\Providers\Catalog\DTO\ProviderPayload;
use App\Domain\Providers\Normalization\Contracts\ProviderSpecificMapper;
use App\Domain\Providers\Normalization\DTO\NormalizedArtist;
use App\Domain\Providers\Normalization\DTO\NormalizedIdentifier;
use App\Domain\Providers\Normalization\DTO\NormalizedRecording;
use App\Domain\Providers\Normalization\DTO\NormalizedRelationship;
use App\Domain\Providers\Normalization\ValueObjects\ProviderField;
use InvalidArgumentException;

final class MusicBrainzProviderMapper implements ProviderSpecificMapper
{
    public function supports(ProviderPayload $payload): bool
    {
        return $payload->providerSlug === 'musicbrainz'
            && in_array($payload->entityType, [EntityType::Artist, EntityType::Recording], true);
    }

    public function map(ProviderPayload $payload): NormalizedProviderEntity
    {
        if (! $this->supports($payload)) {
            throw new InvalidArgumentException('MusicBrainz mapper does not support this payload.');
        }

        return match ($payload->entityType) {
            EntityType::Artist => $this->mapArtist($payload),
            EntityType::Recording => $this->mapRecording($payload),
            default => throw new InvalidArgumentException('Unsupported MusicBrainz entity type.'),
        };
    }

    private function mapArtist(ProviderPayload $payload): NormalizedProviderEntity
    {
        $data = $payload->data;

        return new NormalizedProviderEntity(
            providerSlug: 'musicbrainz',
            entityType: EntityType::Artist,
            externalId: $payload->externalId,
            data: new NormalizedArtist(
                name: $this->field($data, 'name'),
                sortName: $this->field($data, 'sort-name'),
                disambiguation: $this->field($data, 'disambiguation'),
                countryCode: $this->field($data, 'country'),
                beginDate: $this->nestedField($data, 'life-span', 'begin'),
                endDate: $this->nestedField($data, 'life-span', 'end'),
                ended: $this->nestedField($data, 'life-span', 'ended'),
                type: $this->field($data, 'type'),
                aliases: $this->field($data, 'aliases'),
                externalUrls: $this->field($data, 'relations'),
            ),
            identifiers: [new NormalizedIdentifier('musicbrainz_artist', $payload->externalId)],
            normalizerVersion: 'musicbrainz-rich-v1',
        );
    }

    private function mapRecording(ProviderPayload $payload): NormalizedProviderEntity
    {
        $data = $payload->data;
        $isrcs = $data['isrcs'] ?? null;
        $primaryIsrc = is_array($isrcs) && isset($isrcs[0]) && is_string($isrcs[0]) ? $isrcs[0] : null;
        $identifiers = [new NormalizedIdentifier('musicbrainz_recording', $payload->externalId)];

        if ($primaryIsrc !== null && trim($primaryIsrc) !== '') {
            $identifiers[] = new NormalizedIdentifier('isrc', $primaryIsrc);
        }

        $relationships = [];
        $credits = $data['artist-credit'] ?? null;
        if (is_array($credits)) {
            foreach ($credits as $credit) {
                if (! is_array($credit) || ! isset($credit['artist']) || ! is_array($credit['artist'])) {
                    continue;
                }

                $artistId = $credit['artist']['id'] ?? null;
                if (is_string($artistId) && trim($artistId) !== '') {
                    $relationships[] = new NormalizedRelationship('performed-by', EntityType::Artist, $artistId);
                }
            }
        }

        return new NormalizedProviderEntity(
            providerSlug: 'musicbrainz',
            entityType: EntityType::Recording,
            externalId: $payload->externalId,
            data: new NormalizedRecording(
                title: $this->field($data, 'title'),
                subtitle: ProviderField::missing(),
                durationMs: $this->field($data, 'length'),
                disambiguation: $this->field($data, 'disambiguation'),
                isrc: $primaryIsrc === null ? ProviderField::missing() : ProviderField::provided($primaryIsrc),
                firstReleaseDate: $this->field($data, 'first-release-date'),
                video: $this->field($data, 'video'),
            ),
            identifiers: $identifiers,
            relationships: $relationships,
            normalizerVersion: 'musicbrainz-rich-v1',
        );
    }

    /** @param array<string, mixed> $data */
    private function field(array $data, string $key): ProviderField
    {
        if (! array_key_exists($key, $data)) {
            return ProviderField::missing();
        }

        return $data[$key] === null ? ProviderField::explicitNull() : ProviderField::provided($data[$key]);
    }

    /** @param array<string, mixed> $data */
    private function nestedField(array $data, string $parent, string $key): ProviderField
    {
        if (! isset($data[$parent]) || ! is_array($data[$parent])) {
            return ProviderField::missing();
        }

        /** @var array<string, mixed> $nested */
        $nested = $data[$parent];

        return $this->field($nested, $key);
    }
}
