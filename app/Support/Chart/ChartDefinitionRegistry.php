<?php

declare(strict_types=1);

namespace App\Support\Chart;

use App\Domain\Catalog\Enums\EntityType;
use JsonException;
use RuntimeException;

final class ChartDefinitionRegistry
{
    /** @return array<string, mixed> */
    public function get(string $chartId): array
    {
        foreach ($this->all() as $definition) {
            if (($definition['id'] ?? null) === $chartId) {
                return $definition;
            }
        }

        throw new RuntimeException("Unknown chart definition [{$chartId}].");
    }

    /** @return list<array<string, mixed>> */
    public function activeForEntityType(EntityType $entityType): array
    {
        return array_values(array_filter(
            $this->all(),
            static fn (array $definition): bool => ($definition['active'] ?? false) === true
                && in_array($entityType->value, $definition['dependencies']['canonical_entity_types'] ?? [], true),
        ));
    }

    /** @return list<array<string, mixed>> */
    public function all(): array
    {
        $path = base_path('docs/project/domain/chart-definitions.json');
        $raw = file_get_contents($path);
        if ($raw === false) {
            throw new RuntimeException('Chart definition authority could not be read.');
        }

        try {
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException('Chart definition authority is invalid JSON.', 0, $exception);
        }

        $charts = $decoded['charts'] ?? null;
        if (! is_array($charts)) {
            throw new RuntimeException('Chart definition authority is missing charts.');
        }

        foreach ($charts as $chart) {
            if (! is_array($chart)
                || ! is_string($chart['id'] ?? null)
                || ! is_string($chart['entity_type'] ?? null)
                || ! is_string($chart['metric'] ?? null)
                || ! is_string($chart['metric_unit'] ?? null)
                || ! is_string($chart['metric_semantics_version'] ?? null)
                || ! is_string($chart['calculation_version'] ?? null)
                || ! is_bool($chart['active'] ?? null)
            ) {
                throw new RuntimeException('Chart definition authority contains an invalid chart definition.');
            }
        }

        /** @var list<array<string, mixed>> $charts */
        return $charts;
    }
}
