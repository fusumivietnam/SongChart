<?php

declare(strict_types=1);

namespace App\Application\Discovery\Rules;

use App\Domain\Discovery\Contracts\DiscoveryFieldRegistry;
use App\Domain\Discovery\Contracts\DiscoveryRuleEngine;
use App\Domain\Discovery\DTO\CompiledDiscoveryRule;
use App\Domain\Discovery\DTO\DiscoveryEntitySnapshot;
use App\Domain\Discovery\DTO\DiscoveryFieldDefinition;
use App\Domain\Discovery\DTO\DiscoveryRuleCondition;
use App\Domain\Discovery\DTO\DiscoveryRuleSet;
use App\Domain\Discovery\DTO\DiscoverySort;
use App\Domain\Discovery\Enums\DiscoverableEntityType;
use App\Domain\Discovery\Enums\DiscoveryRuleOperator;
use App\Domain\Discovery\Enums\DiscoverySortDirection;
use DateTimeImmutable;
use InvalidArgumentException;

final class DefaultDiscoveryRuleEngine implements DiscoveryRuleEngine
{
    public const MAX_SET_VALUES = 100;

    public function __construct(private readonly DiscoveryFieldRegistry $fields) {}

    public function compile(DiscoverableEntityType $entityType, DiscoveryRuleSet $rules): CompiledDiscoveryRule
    {
        $predicates = [];
        foreach ($rules->conditions as $condition) {
            $definition = $this->validateCondition($entityType, $condition);
            $predicates[] = $this->compileCondition($definition, $condition);
        }

        $booleanOperator = $rules->booleanOperator;

        return new CompiledDiscoveryRule(
            $entityType,
            static function (DiscoveryEntitySnapshot $entity) use ($predicates, $booleanOperator): bool {
                if ($booleanOperator === 'and') {
                    foreach ($predicates as $predicate) {
                        if (! $predicate($entity)) {
                            return false;
                        }
                    }

                    return true;
                }

                foreach ($predicates as $predicate) {
                    if ($predicate($entity)) {
                        return true;
                    }
                }

                return false;
            },
        );
    }

    public function validateSorts(DiscoverableEntityType $entityType, array $sorts): void
    {
        if (count($sorts) > 3) {
            throw new InvalidArgumentException('Discovery channels allow at most 3 configured sorts.');
        }

        $seen = [];
        foreach ($sorts as $sort) {
            $definition = $this->fields->get($entityType, $sort->field);
            if (! $definition->sortable) {
                throw new InvalidArgumentException("Discovery field [{$sort->field}] is not sortable.");
            }
            if (isset($seen[$sort->field])) {
                throw new InvalidArgumentException("Duplicate discovery sort field [{$sort->field}].");
            }
            $seen[$sort->field] = true;
        }
    }

    public function matches(DiscoveryEntitySnapshot $entity, DiscoveryRuleSet $rules): bool
    {
        return $this->compile($entity->entityType, $rules)->matches($entity);
    }

    public function sort(array $entities, array $sorts): array
    {
        if ($entities === []) {
            return [];
        }

        $entityType = $entities[0]->entityType;
        foreach ($entities as $entity) {
            if ($entity->entityType !== $entityType) {
                throw new InvalidArgumentException('Discovery sort input must contain one entity type only.');
            }
        }
        $this->validateSorts($entityType, $sorts);

        $effectiveSorts = $sorts;
        $hasIdTieBreaker = false;
        foreach ($effectiveSorts as $sort) {
            if ($sort->field === 'id') {
                $hasIdTieBreaker = true;
                break;
            }
        }

        if (! $hasIdTieBreaker) {
            $effectiveSorts[] = new DiscoverySort('id', DiscoverySortDirection::Ascending);
        }

        usort($entities, function (DiscoveryEntitySnapshot $left, DiscoveryEntitySnapshot $right) use ($effectiveSorts): int {
            foreach ($effectiveSorts as $sort) {
                $comparison = $this->compare($left->value($sort->field), $right->value($sort->field));
                if ($comparison !== 0) {
                    return $sort->direction === DiscoverySortDirection::Ascending ? $comparison : -$comparison;
                }
            }

            return 0;
        });

        return $entities;
    }

    private function validateCondition(DiscoverableEntityType $entityType, DiscoveryRuleCondition $condition): DiscoveryFieldDefinition
    {
        $definition = $this->fields->get($entityType, $condition->field);
        if (! $definition->filterable) {
            throw new InvalidArgumentException("Discovery field [{$condition->field}] is not filterable.");
        }
        if (! in_array($condition->operator, $definition->allowedOperators, true)) {
            throw new InvalidArgumentException("Operator [{$condition->operator->value}] is not allowed for discovery field [{$condition->field}].");
        }

        if (in_array($condition->operator, [DiscoveryRuleOperator::Exists, DiscoveryRuleOperator::NotExists], true)) {
            if ($condition->value !== null) {
                throw new InvalidArgumentException('exists/not_exists discovery operators require a null value.');
            }

            return $definition;
        }

        if (in_array($condition->operator, [DiscoveryRuleOperator::In, DiscoveryRuleOperator::NotIn], true)) {
            if (! is_array($condition->value) || $condition->value === [] || count($condition->value) > self::MAX_SET_VALUES) {
                throw new InvalidArgumentException('in/not_in discovery operators require 1..100 values.');
            }
            foreach ($condition->value as $value) {
                $this->assertValueType($definition, $value);
            }

            return $definition;
        }

        $this->assertValueType($definition, $condition->value);

        return $definition;
    }

    /** @return \Closure(DiscoveryEntitySnapshot): bool */
    private function compileCondition(DiscoveryFieldDefinition $definition, DiscoveryRuleCondition $condition): \Closure
    {
        return function (DiscoveryEntitySnapshot $entity) use ($definition, $condition): bool {
            $exists = $entity->has($condition->field) && $entity->value($condition->field) !== null;
            if ($condition->operator === DiscoveryRuleOperator::Exists) {
                return $exists;
            }
            if ($condition->operator === DiscoveryRuleOperator::NotExists) {
                return ! $exists;
            }

            $actual = $entity->value($condition->field);
            if ($actual === null) {
                return false;
            }

            if ($condition->operator === DiscoveryRuleOperator::Equal) {
                return $actual === $condition->value;
            }
            if ($condition->operator === DiscoveryRuleOperator::NotEqual) {
                return $actual !== $condition->value;
            }
            if ($condition->operator === DiscoveryRuleOperator::In) {
                return is_array($condition->value) && in_array($actual, $condition->value, true);
            }
            if ($condition->operator === DiscoveryRuleOperator::NotIn) {
                return is_array($condition->value) && ! in_array($actual, $condition->value, true);
            }

            $comparison = $this->compareTyped($definition, $actual, $condition->value);
            $orderedComparators = [
                DiscoveryRuleOperator::GreaterThan->value => static fn (int $result): bool => $result > 0,
                DiscoveryRuleOperator::GreaterThanOrEqual->value => static fn (int $result): bool => $result >= 0,
                DiscoveryRuleOperator::LessThan->value => static fn (int $result): bool => $result < 0,
                DiscoveryRuleOperator::LessThanOrEqual->value => static fn (int $result): bool => $result <= 0,
            ];

            return $orderedComparators[$condition->operator->value]($comparison);
        };
    }

    private function assertValueType(DiscoveryFieldDefinition $definition, mixed $value): void
    {
        $valid = match ($definition->dataType) {
            'string' => is_string($value),
            'integer' => is_int($value),
            'boolean' => is_bool($value),
            'date' => is_string($value) && $this->isDate($value, 'Y-m-d'),
            'datetime' => is_string($value) && $this->isDate($value, DATE_ATOM),
            default => false,
        };

        if (! $valid) {
            throw new InvalidArgumentException("Invalid value type for discovery field [{$definition->key}]; expected {$definition->dataType}.");
        }
    }

    private function compareTyped(DiscoveryFieldDefinition $definition, mixed $left, mixed $right): int
    {
        return match ($definition->dataType) {
            'integer' => $left <=> $right,
            'date', 'datetime', 'string' => strcmp((string) $left, (string) $right),
            default => throw new InvalidArgumentException("Discovery field [{$definition->key}] does not support ordered comparison."),
        };
    }

    private function compare(string|int|float|bool|null $left, string|int|float|bool|null $right): int
    {
        if ($left === $right) {
            return 0;
        }

        if ($left === null) {
            return 1;
        }

        if ($right === null) {
            return -1;
        }

        if (is_int($left) || is_float($left)) {
            return $left <=> $right;
        }

        if (is_bool($left)) {
            return (int) $left <=> (int) $right;
        }

        return strcmp((string) $left, (string) $right);
    }

    private function isDate(string $value, string $format): bool
    {
        $date = DateTimeImmutable::createFromFormat($format, $value);

        return $date !== false && $date->format($format) === $value;
    }
}
