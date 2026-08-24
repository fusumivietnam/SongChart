<?php

declare(strict_types=1);

namespace App\Support\Discovery;

use App\Domain\Discovery\Contracts\DiscoveryFieldRegistry;
use App\Domain\Discovery\DTO\DiscoveryFieldDefinition;
use App\Domain\Discovery\Enums\DiscoverableEntityType;
use App\Domain\Discovery\Enums\DiscoveryRuleOperator;
use InvalidArgumentException;

final class CanonicalDiscoveryFieldRegistry implements DiscoveryFieldRegistry
{
    /** @var array<string, list<DiscoveryFieldDefinition>>|null */
    private ?array $definitions = null;

    public function get(DiscoverableEntityType $entityType, string $field): DiscoveryFieldDefinition
    {
        foreach ($this->allFor($entityType) as $definition) {
            if ($definition->key === $field) {
                return $definition;
            }
        }

        throw new InvalidArgumentException("Unknown discovery field [{$entityType->value}.{$field}].");
    }

    public function has(DiscoverableEntityType $entityType, string $field): bool
    {
        try {
            $this->get($entityType, $field);

            return true;
        } catch (InvalidArgumentException) {
            return false;
        }
    }

    public function allFor(DiscoverableEntityType $entityType): array
    {
        return $this->definitions()[$entityType->value] ?? [];
    }

    /** @return array<string, list<DiscoveryFieldDefinition>> */
    private function definitions(): array
    {
        if ($this->definitions !== null) {
            return $this->definitions;
        }

        $eq = [DiscoveryRuleOperator::Equal, DiscoveryRuleOperator::NotEqual, DiscoveryRuleOperator::In, DiscoveryRuleOperator::NotIn, DiscoveryRuleOperator::Exists, DiscoveryRuleOperator::NotExists];
        $ordered = [...$eq, DiscoveryRuleOperator::GreaterThan, DiscoveryRuleOperator::GreaterThanOrEqual, DiscoveryRuleOperator::LessThan, DiscoveryRuleOperator::LessThanOrEqual];

        $shared = static fn (DiscoverableEntityType $type): array => [
            new DiscoveryFieldDefinition('id', 'string', $eq, [$type], true, true),
            new DiscoveryFieldDefinition('slug', 'string', $eq, [$type], true, true),
            new DiscoveryFieldDefinition('verification_state', 'string', $eq, [$type], true, true),
            new DiscoveryFieldDefinition('created_at', 'datetime', $ordered, [$type], true, true),
            new DiscoveryFieldDefinition('updated_at', 'datetime', $ordered, [$type], true, true),
        ];

        return $this->definitions = [
            DiscoverableEntityType::Artist->value => [
                ...$shared(DiscoverableEntityType::Artist),
                new DiscoveryFieldDefinition('name', 'string', $eq, [DiscoverableEntityType::Artist], true, true),
                new DiscoveryFieldDefinition('sort_name', 'string', $eq, [DiscoverableEntityType::Artist], true, true),
                new DiscoveryFieldDefinition('artist_type', 'string', $eq, [DiscoverableEntityType::Artist], true, true),
                new DiscoveryFieldDefinition('country_code', 'string', $eq, [DiscoverableEntityType::Artist], true, true),
            ],
            DiscoverableEntityType::Recording->value => [
                ...$shared(DiscoverableEntityType::Recording),
                new DiscoveryFieldDefinition('title', 'string', $eq, [DiscoverableEntityType::Recording], true, true),
                new DiscoveryFieldDefinition('duration_ms', 'integer', $ordered, [DiscoverableEntityType::Recording], true, true),
                new DiscoveryFieldDefinition('is_explicit', 'boolean', $eq, [DiscoverableEntityType::Recording], true, true),
            ],
            DiscoverableEntityType::Release->value => [
                ...$shared(DiscoverableEntityType::Release),
                new DiscoveryFieldDefinition('title', 'string', $eq, [DiscoverableEntityType::Release], true, true),
                new DiscoveryFieldDefinition('release_type', 'string', $eq, [DiscoverableEntityType::Release], true, true),
                new DiscoveryFieldDefinition('released_on', 'date', $ordered, [DiscoverableEntityType::Release], true, true),
                new DiscoveryFieldDefinition('country_code', 'string', $eq, [DiscoverableEntityType::Release], true, true),
            ],
            DiscoverableEntityType::Collection->value => [
                ...$shared(DiscoverableEntityType::Collection),
                new DiscoveryFieldDefinition('title', 'string', $eq, [DiscoverableEntityType::Collection], true, true),
                new DiscoveryFieldDefinition('visibility', 'string', $eq, [DiscoverableEntityType::Collection], true, true),
            ],
        ];
    }
}
