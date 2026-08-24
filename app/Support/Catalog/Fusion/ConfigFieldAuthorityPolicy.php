<?php

declare(strict_types=1);

namespace App\Support\Catalog\Fusion;

use App\Contracts\Catalog\FieldAuthorityPolicy;
use App\Domain\Catalog\Enums\EntityType;

final readonly class ConfigFieldAuthorityPolicy implements FieldAuthorityPolicy
{
    /** @param array<string, mixed> $policy */
    public function __construct(private array $policy) {}

    public function authority(string $sourceKey, EntityType $entityType, string $fieldName): float
    {
        $defaultValue = $this->policy['default'] ?? 0.5;
        $default = $this->bounded(is_numeric($defaultValue) ? (float) $defaultValue : 0.5);

        $sources = $this->policy['sources'] ?? [];
        if (! is_array($sources)) {
            return $default;
        }
        $source = $sources[$sourceKey] ?? null;
        if (! is_array($source)) {
            return $default;
        }

        $fields = $source['fields'] ?? [];
        $fields = is_array($fields) ? $fields : [];
        $entityField = $entityType->value.'.'.$fieldName;
        $weight = $fields[$entityField]
            ?? $fields['*.'.$fieldName]
            ?? $fields[$entityType->value.'.*']
            ?? $source['default']
            ?? $default;

        return $this->bounded(is_numeric($weight) ? (float) $weight : $default);
    }

    private function bounded(float $value): float
    {
        return max(0.0, min(1.0, $value));
    }
}
