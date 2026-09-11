<?php

declare(strict_types=1);

namespace App\Application\Chart\Queries;

use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Chart\DTO\ChartSnapshot;
use App\Models\Catalog\Recording;
use App\Support\Catalog\PublicEntityUrl;
use App\Support\Chart\ChartDefinitionRegistry;
use DateTimeImmutable;
use LogicException;
use RuntimeException;

final readonly class PublicChartProjection
{
    public function __construct(private ChartDefinitionRegistry $definitions) {}

    /** @return array<string, mixed> */
    public function fromSnapshot(ChartSnapshot $snapshot): array
    {
        $definition = $this->definitionFor($snapshot->chartId);
        $freshness = $definition['freshness'] ?? [];
        $nowTimestamp = now()->getTimestamp();
        $snapshotAgeSeconds = max(0, $nowTimestamp - $snapshot->snapshotAt->getTimestamp());
        $maxSnapshotAgeSeconds = is_int($freshness['max_snapshot_age_seconds'] ?? null)
            ? $freshness['max_snapshot_age_seconds']
            : null;
        $maxObservationAgeSeconds = is_int($freshness['max_observation_age_seconds'] ?? null)
            ? $freshness['max_observation_age_seconds']
            : null;

        $ids = array_map(static fn (array $row): string => $row['canonical_recording_id'], $snapshot->rows);
        $recordings = Recording::query()->whereIn('id', $ids)->get()->keyBy(fn (Recording $recording): string => (string) $recording->getKey());

        $rows = [];
        foreach ($snapshot->rows as $row) {
            $recording = $recordings->get($row['canonical_recording_id']);
            if (! $recording instanceof Recording) {
                throw new LogicException('Chart snapshot references a missing canonical recording.');
            }

            $observations = array_values(array_filter(
                $row['observations'] ?? [],
                static fn (mixed $observation): bool => is_array($observation),
            ));
            $latestObservedAt = $this->latestObservedAt($observations);
            $observationAgeSeconds = $latestObservedAt instanceof DateTimeImmutable
                ? max(0, $nowTimestamp - $latestObservedAt->getTimestamp())
                : null;
            $score = (float) $row['score'];
            $slug = (string) $recording->getAttribute('slug');

            $rows[] = [
                'rank' => $row['rank'],
                'canonical_recording_id' => (string) $recording->getKey(),
                'title' => (string) $recording->getAttribute('title'),
                'slug' => $slug,
                'url' => PublicEntityUrl::to(EntityType::Recording, $slug),
                'score' => $score,
                'value_state' => $score === 0.0 ? 'observed_zero' : 'observed',
                'observation_ids' => $row['observation_ids'],
                'evidence_count' => count($observations),
                'latest_observed_at' => $latestObservedAt?->format(DATE_ATOM),
                'observation_freshness' => $this->freshnessState($observationAgeSeconds, $maxObservationAgeSeconds),
                'observations' => array_map(static fn (array $observation): array => [
                    'provider' => (string) ($observation['provider'] ?? ''),
                    'metric' => (string) ($observation['metric'] ?? ''),
                    'value' => (float) ($observation['value'] ?? 0),
                    'observed_at' => isset($observation['observed_at']) ? (string) $observation['observed_at'] : null,
                    'metric_unit' => (string) ($observation['metric_unit'] ?? ''),
                    'metric_semantics_version' => (string) ($observation['metric_semantics_version'] ?? ''),
                    'fetched_at' => isset($observation['fetched_at']) ? (string) $observation['fetched_at'] : null,
                    'source_reference' => isset($observation['source_reference']) ? (string) $observation['source_reference'] : null,
                ], $observations),
            ];
        }

        $firstObservation = $rows[0]['observations'][0] ?? [];

        return [
            'state' => 'observed',
            'chart_id' => $snapshot->chartId,
            'metric' => $snapshot->metric,
            'metric_unit' => (string) ($definition['metric_unit'] ?? $firstObservation['metric_unit'] ?? 'count'),
            'metric_semantics_version' => (string) ($definition['metric_semantics_version'] ?? $firstObservation['metric_semantics_version'] ?? 'unknown'),
            'provider' => isset($definition['provider']) ? (string) $definition['provider'] : null,
            'calculation_version' => $snapshot->calculationVersion,
            'snapshot_at' => $snapshot->snapshotAt->format(DATE_ATOM),
            'snapshot_age_seconds' => $snapshotAgeSeconds,
            'max_snapshot_age_seconds' => $maxSnapshotAgeSeconds,
            'freshness' => $this->freshnessState($snapshotAgeSeconds, $maxSnapshotAgeSeconds),
            'rows' => $rows,
        ];
    }

    /** @param array<string, mixed> $definition
     *  @return array<string, mixed>
     */
    public function unavailable(array $definition): array
    {
        return [
            'state' => 'unavailable',
            'chart_id' => (string) $definition['id'],
            'metric' => (string) $definition['metric'],
            'metric_unit' => (string) $definition['metric_unit'],
            'metric_semantics_version' => (string) $definition['metric_semantics_version'],
            'provider' => isset($definition['provider']) ? (string) $definition['provider'] : null,
            'calculation_version' => (string) $definition['calculation_version'],
            'snapshot_at' => null,
            'snapshot_age_seconds' => null,
            'max_snapshot_age_seconds' => $definition['freshness']['max_snapshot_age_seconds'] ?? null,
            'freshness' => 'unavailable',
            'rows' => [],
        ];
    }

    /** @return array<string, mixed> */
    private function definitionFor(string $chartId): array
    {
        try {
            return $this->definitions->get($chartId);
        } catch (RuntimeException) {
            return [];
        }
    }

    /** @param list<array<string, mixed>> $observations */
    private function latestObservedAt(array $observations): ?DateTimeImmutable
    {
        $latest = null;
        foreach ($observations as $observation) {
            $value = $observation['observed_at'] ?? null;
            if (! is_string($value) || $value === '') {
                continue;
            }

            $candidate = new DateTimeImmutable($value);
            if (! $latest instanceof DateTimeImmutable || $candidate > $latest) {
                $latest = $candidate;
            }
        }

        return $latest;
    }

    private function freshnessState(?int $ageSeconds, ?int $maxAgeSeconds): string
    {
        if ($ageSeconds === null || $maxAgeSeconds === null) {
            return 'unknown';
        }

        return $ageSeconds <= $maxAgeSeconds ? 'fresh' : 'stale';
    }
}
