<?php

declare(strict_types=1);

namespace App\Support\Discovery;

use App\Domain\Discovery\Contracts\DiscoveryProjectionReader;
use App\Domain\Discovery\Contracts\DiscoveryProjectionWriter;
use App\Domain\Discovery\DTO\DiscoveryProjection;
use App\Domain\Discovery\DTO\DiscoveryProjectionItem;
use App\Domain\Discovery\Enums\DiscoverableEntityType;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class DatabaseDiscoveryProjectionStore implements DiscoveryProjectionReader, DiscoveryProjectionWriter
{
    public function latestForChannel(string $channelId): ?DiscoveryProjection
    {
        $row = DB::table('discovery_projections')
            ->where('channel_id', $channelId)
            ->orderByDesc('projection_revision')
            ->first();

        return $row === null ? null : $this->hydrate($row);
    }

    public function append(DiscoveryProjection $projection): DiscoveryProjection
    {
        return DB::transaction(function () use ($projection): DiscoveryProjection {
            DB::table('discovery_channels')->where('id', $projection->channelId)->lockForUpdate()->firstOrFail();
            $revision = ((int) DB::table('discovery_projections')->where('channel_id', $projection->channelId)->max('projection_revision')) + 1;
            $stored = new DiscoveryProjection(
                $projection->channelId,
                $projection->channelRevision,
                $revision,
                $projection->ruleSchemaVersion,
                $projection->sourceVersion,
                $projection->generatedAt,
                $projection->expiresAt,
                $projection->items,
            );

            DB::table('discovery_projections')->insert([
                'id' => (string) Str::ulid(),
                'channel_id' => $stored->channelId,
                'channel_revision' => $stored->channelRevision,
                'projection_revision' => $stored->projectionRevision,
                'rule_schema_version' => $stored->ruleSchemaVersion,
                'source_version' => $stored->sourceVersion,
                'item_count' => count($stored->items),
                'payload' => json_encode(['items' => array_map($this->serializeItem(...), $stored->items)], JSON_THROW_ON_ERROR),
                'generated_at' => $stored->generatedAt,
                'expires_at' => $stored->expiresAt,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return $stored;
        }, 3);
    }

    private function hydrate(object $row): DiscoveryProjection
    {
        $payload = json_decode((string) $row->payload, true, 512, JSON_THROW_ON_ERROR);
        $items = [];
        foreach (($payload['items'] ?? []) as $item) {
            $items[] = new DiscoveryProjectionItem(
                DiscoverableEntityType::from((string) $item['entity_type']),
                (string) $item['entity_id'],
                (int) $item['rank'],
                (string) $item['title'],
                (string) $item['slug'],
                isset($item['subtitle']) ? (string) $item['subtitle'] : null,
                isset($item['image']) ? (string) $item['image'] : null,
                $this->metrics($item['metrics'] ?? null),
            );
        }

        return new DiscoveryProjection(
            (string) $row->channel_id,
            (int) $row->channel_revision,
            (int) $row->projection_revision,
            (int) $row->rule_schema_version,
            (string) $row->source_version,
            new DateTimeImmutable((string) $row->generated_at),
            $row->expires_at === null ? null : new DateTimeImmutable((string) $row->expires_at),
            $items,
        );
    }

    /** @return array<string, int|float|string|bool|null> */
    private function metrics(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $metrics = [];
        foreach ($value as $key => $metric) {
            if (is_string($key) && ($metric === null || is_scalar($metric))) {
                $metrics[$key] = $metric;
            }
        }

        return $metrics;
    }

    /** @return array<string, mixed> */
    private function serializeItem(DiscoveryProjectionItem $item): array
    {
        return [
            'entity_type' => $item->entityType->value,
            'entity_id' => $item->entityId,
            'rank' => $item->rank,
            'title' => $item->title,
            'slug' => $item->slug,
            'subtitle' => $item->subtitle,
            'image' => $item->image,
            'metrics' => $item->metrics,
        ];
    }
}
