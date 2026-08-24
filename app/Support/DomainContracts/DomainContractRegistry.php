<?php

declare(strict_types=1);

namespace App\Support\DomainContracts;

use App\Domain\Catalog\Enums\EntityType;
use RuntimeException;

final class DomainContractRegistry
{
    /** @var array<string, mixed>|null */
    private ?array $contracts = null;

    public function displayField(EntityType $type): string
    {
        return $this->stringEntityProperty($type, 'display_field');
    }

    public function slugField(EntityType $type): string
    {
        return $this->stringEntityProperty($type, 'slug_field');
    }

    public function dateField(EntityType $type): ?string
    {
        return $this->nullableStringEntityProperty($type, 'date_field');
    }

    public function descriptionField(EntityType $type): ?string
    {
        return $this->nullableStringEntityProperty($type, 'description_field');
    }

    /** @return list<string> */
    public function fieldNames(EntityType $type): array
    {
        $fields = $this->entity($type)['fields'] ?? null;

        if (! is_array($fields)) {
            throw new RuntimeException("Domain contract fields for [{$type->value}] are missing.");
        }

        return array_map('strval', array_keys($fields));
    }

    public function hasField(EntityType $type, string $field): bool
    {
        return in_array($field, $this->fieldNames($type), true);
    }

    public function adminUlidPattern(): string
    {
        $identity = $this->contracts()['identity']['internal_id'] ?? null;
        $pattern = is_array($identity) ? ($identity['route_pattern'] ?? null) : null;

        if (! is_string($pattern) || $pattern === '') {
            throw new RuntimeException('Domain contract internal ULID route pattern is missing.');
        }

        return $pattern;
    }

    /** @return array<string, mixed> */
    public function entity(EntityType $type): array
    {
        $entity = $this->contracts()['entities'][$type->value] ?? null;

        if (! is_array($entity)) {
            throw new RuntimeException("Domain contract for [{$type->value}] is missing.");
        }

        return $entity;
    }

    private function stringEntityProperty(EntityType $type, string $property): string
    {
        $value = $this->entity($type)[$property] ?? null;

        if (! is_string($value) || $value === '') {
            throw new RuntimeException("Domain contract property [{$type->value}.{$property}] is missing.");
        }

        return $value;
    }

    private function nullableStringEntityProperty(EntityType $type, string $property): ?string
    {
        $value = $this->entity($type)[$property] ?? null;

        if ($value === null) {
            return null;
        }

        if (! is_string($value) || $value === '') {
            throw new RuntimeException("Domain contract property [{$type->value}.{$property}] is invalid.");
        }

        return $value;
    }

    /** @return array<string, mixed> */
    private function contracts(): array
    {
        if ($this->contracts !== null) {
            return $this->contracts;
        }

        $path = base_path('docs/project/domain/domain-contracts.json');
        $contents = file_get_contents($path);

        if ($contents === false) {
            throw new RuntimeException("Unable to read domain contract registry [{$path}].");
        }

        $decoded = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);

        if (! is_array($decoded)) {
            throw new RuntimeException('Domain contract registry must decode to an object.');
        }

        return $this->contracts = $decoded;
    }
}
