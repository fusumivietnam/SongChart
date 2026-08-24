<?php

declare(strict_types=1);

namespace App\Support\DomainContracts;

use App\Support\DomainContracts\DTO\EntityDataSurface;
use App\Support\DomainContracts\DTO\SupportDataSurface;
use App\Support\DomainContracts\DTO\UseCaseContract;
use RuntimeException;

final class UseCaseContractRegistry
{
    /** @var array<string, mixed>|null */
    private ?array $contracts = null;

    public function get(string $key): UseCaseContract
    {
        $raw = $this->contracts()['use_cases'][$key] ?? null;
        if (! is_array($raw)) {
            throw new RuntimeException("Use-case contract [{$key}] is missing.");
        }

        $surfaces = [];
        foreach (($raw['data_surfaces'] ?? []) as $entityType => $surface) {
            if (! is_array($surface)) {
                throw new RuntimeException("Use-case data surface [{$key}.{$entityType}] is invalid.");
            }

            $surfaces[] = new EntityDataSurface(
                entityType: (string) $entityType,
                readFields: $this->stringList($surface['read_fields'] ?? []),
                writeFields: $this->stringList($surface['write_fields'] ?? []),
            );
        }

        $supportSurfaces = [];
        $supportReads = is_array($raw['support_surfaces'] ?? null) ? $raw['support_surfaces'] : [];
        $supportWrites = is_array($raw['support_surface_writes'] ?? null) ? $raw['support_surface_writes'] : [];
        foreach (array_unique(array_merge(array_keys($supportReads), array_keys($supportWrites))) as $surfaceKey) {
            $supportSurfaces[] = new SupportDataSurface(
                key: (string) $surfaceKey,
                readFields: $this->stringList($supportReads[$surfaceKey] ?? []),
                writeFields: $this->stringList($supportWrites[$surfaceKey] ?? []),
            );
        }

        $routeParameters = [];
        foreach (($raw['route_parameters'] ?? []) as $parameter => $type) {
            if (! is_string($parameter) || ! is_string($type) || $parameter === '' || $type === '') {
                throw new RuntimeException("Use-case route parameter [{$key}] is invalid.");
            }
            $routeParameters[$parameter] = $type;
        }

        return new UseCaseContract(
            key: $key,
            method: $this->requiredString($raw, 'method', $key),
            routeName: $this->requiredString($raw, 'route_name', $key),
            uri: $this->requiredString($raw, 'uri', $key),
            middleware: $this->stringList($raw['middleware'] ?? []),
            implementation: $this->requiredString($raw, 'implementation', $key),
            actors: $this->stringList($raw['actors'] ?? []),
            dataSurfaces: $surfaces,
            supportSurfaces: $supportSurfaces,
            routeParameters: $routeParameters,
            outputType: $this->requiredString($raw, 'output_type', $key),
            readOnly: (bool) ($raw['read_only'] ?? false),
        );
    }

    /** @return list<string> */
    public function keys(): array
    {
        $useCases = $this->contracts()['use_cases'] ?? [];
        if (! is_array($useCases)) {
            return [];
        }

        return array_map('strval', array_keys($useCases));
    }

    /** @param array<string, mixed> $raw */
    private function requiredString(array $raw, string $field, string $key): string
    {
        $value = $raw[$field] ?? null;
        if (! is_string($value) || $value === '') {
            throw new RuntimeException("Use-case contract [{$key}.{$field}] is missing.");
        }

        return $value;
    }

    /** @return list<string> */
    private function stringList(mixed $value): array
    {
        if (! is_array($value)) {
            throw new RuntimeException('Use-case contract list value is invalid.');
        }

        $strings = [];
        foreach ($value as $item) {
            if (! is_string($item) || $item === '') {
                throw new RuntimeException('Use-case contract list contains a non-string value.');
            }
            $strings[] = $item;
        }

        return $strings;
    }

    /** @return array<string, mixed> */
    private function contracts(): array
    {
        if ($this->contracts !== null) {
            return $this->contracts;
        }

        $path = base_path('docs/project/domain/use-case-contracts.json');
        $contents = file_get_contents($path);
        if ($contents === false) {
            throw new RuntimeException("Unable to read use-case contract registry [{$path}].");
        }

        $decoded = json_decode($contents, true, flags: JSON_THROW_ON_ERROR);
        if (! is_array($decoded)) {
            throw new RuntimeException('Use-case contract registry must decode to an object.');
        }

        return $this->contracts = $decoded;
    }
}
