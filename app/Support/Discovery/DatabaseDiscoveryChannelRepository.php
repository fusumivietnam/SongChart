<?php

declare(strict_types=1);

namespace App\Support\Discovery;

use App\Domain\Discovery\Contracts\DiscoveryChannelRepository;
use App\Domain\Discovery\DiscoveryChannel;
use App\Domain\Discovery\DTO\DiscoveryRuleCondition;
use App\Domain\Discovery\DTO\DiscoveryRuleSet;
use App\Domain\Discovery\DTO\DiscoverySort;
use App\Domain\Discovery\Enums\DiscoverableEntityType;
use App\Domain\Discovery\Enums\DiscoveryChannelMode;
use App\Domain\Discovery\Enums\DiscoveryChannelStatus;
use App\Domain\Discovery\Enums\DiscoveryLayout;
use App\Domain\Discovery\Enums\DiscoveryRuleOperator;
use App\Domain\Discovery\Enums\DiscoverySortDirection;
use App\Domain\Discovery\ValueObjects\DiscoveryPublicationWindow;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final class DatabaseDiscoveryChannelRepository implements DiscoveryChannelRepository
{
    public function get(string $id): DiscoveryChannel
    {
        $row = DB::table('discovery_channels')->where('id', $id)->first();
        if ($row === null) {
            throw new RuntimeException("Discovery channel [{$id}] was not found.");
        }

        $rulesPayload = $this->json($row->rules ?? null);
        $sortPayload = $this->json($row->sorts ?? null) ?? [];
        $presentation = $this->json($row->presentation ?? null) ?? [];

        $rules = null;
        if ($rulesPayload !== null) {
            $rawConditions = $rulesPayload['conditions'] ?? $rulesPayload['rules'] ?? [];
            if (! is_array($rawConditions)) {
                throw new RuntimeException('Discovery rule payload conditions must be an array.');
            }

            $conditions = [];
            foreach ($rawConditions as $condition) {
                if (! is_array($condition) || ! isset($condition['field'], $condition['operator'])) {
                    throw new RuntimeException('Discovery rule payload contains an invalid condition.');
                }
                $conditions[] = new DiscoveryRuleCondition(
                    (string) $condition['field'],
                    DiscoveryRuleOperator::from((string) $condition['operator']),
                    $condition['value'] ?? null,
                );
            }
            $rules = new DiscoveryRuleSet(
                (string) ($rulesPayload['boolean_operator'] ?? $rulesPayload['operator'] ?? 'and'),
                $conditions,
                (int) ($rulesPayload['schema_version'] ?? 1),
            );
        }

        $sorts = [];
        foreach ($sortPayload as $sort) {
            if (! is_array($sort) || ! isset($sort['field'], $sort['direction'])) {
                throw new RuntimeException('Discovery sort payload contains an invalid sort.');
            }
            $sorts[] = new DiscoverySort(
                (string) $sort['field'],
                DiscoverySortDirection::from((string) $sort['direction']),
            );
        }

        return new DiscoveryChannel(
            (string) $row->id,
            (string) $row->key,
            (string) $row->slug,
            (string) $row->name,
            $row->description === null ? null : (string) $row->description,
            DiscoverableEntityType::from((string) $row->entity_type),
            DiscoveryChannelMode::from((string) $row->mode),
            DiscoveryChannelStatus::from((string) $row->status),
            $rules,
            $sorts,
            DiscoveryLayout::from((string) ($presentation['layout'] ?? DiscoveryLayout::Grid->value)),
            (int) $row->default_limit,
            new DiscoveryPublicationWindow(
                $row->publish_from === null ? null : new DateTimeImmutable((string) $row->publish_from),
                $row->publish_until === null ? null : new DateTimeImmutable((string) $row->publish_until),
                (string) $row->publication_timezone,
            ),
            (int) $row->revision,
        );
    }

    public function save(DiscoveryChannel $channel): void
    {
        DB::table('discovery_channels')->where('id', $channel->id)->update([
            'slug' => $channel->slug,
            'name' => $channel->name,
            'description' => $channel->description,
            'status' => $channel->status->value,
            'revision' => $channel->revision,
            'updated_at' => now(),
        ]);
    }

    public function existsByKey(string $key): bool
    {
        return DB::table('discovery_channels')->where('key', $key)->exists();
    }

    /** @return array<string, mixed>|null */
    private function json(mixed $value): ?array
    {
        if ($value === null) {
            return null;
        }
        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode((string) $value, true, 512, JSON_THROW_ON_ERROR);

        return is_array($decoded) ? $decoded : null;
    }
}
