<?php

declare(strict_types=1);

namespace App\Support\Chart;

use App\Domain\Chart\DTO\ChartMetricObservation;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

final class DatabaseChartMetricObservationStore
{
    /**
     * @param list<ChartMetricObservation> $observations
     * @return list<ChartMetricObservation>
     */
    public function appendMany(array $observations): array
    {
        if ($observations === []) {
            return [];
        }

        $now = now();
        foreach ($observations as $observation) {
            $valueKind = is_int($observation->value) ? 'int' : 'float';
            $inserted = DB::table('chart_metric_observations')->insertOrIgnore([
                'id' => (string) Str::ulid(),
                'observation_id' => $observation->observationId,
                'canonical_recording_id' => $observation->canonicalRecordingId,
                'provider' => $observation->provider,
                'provider_item_id' => $observation->providerItemId,
                'metric' => $observation->metric,
                'metric_unit' => $observation->metricUnit,
                'metric_semantics_version' => $observation->metricSemanticsVersion,
                'value' => $this->serializeValue($observation->value),
                'value_kind' => $valueKind,
                'observed_at' => $observation->observedAt,
                'fetched_at' => $observation->fetchedAt,
                'source_reference' => $observation->sourceReference,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            if ($inserted === 0) {
                $this->assertSameObservation($observation);
            }
        }

        return $this->findByObservationIds(array_map(
            static fn (ChartMetricObservation $observation): string => $observation->observationId,
            $observations,
        ));
    }

    /** @param list<string> $observationIds
     *  @return list<ChartMetricObservation>
     */
    public function findByObservationIds(array $observationIds): array
    {
        if ($observationIds === []) {
            return [];
        }

        $rows = DB::table('chart_metric_observations')
            ->whereIn('observation_id', $observationIds)
            ->orderBy('observed_at')
            ->orderBy('observation_id')
            ->get();

        return $rows->map(fn (object $row): ChartMetricObservation => $this->rehydrate($row))->all();
    }

    private function assertSameObservation(ChartMetricObservation $observation): void
    {
        $stored = DB::table('chart_metric_observations')
            ->where('observation_id', $observation->observationId)
            ->first();

        if ($stored === null) {
            throw new RuntimeException('Metric observation uniqueness conflict could not be reloaded.');
        }

        $rehydrated = $this->rehydrate($stored);
        $same = $rehydrated->canonicalRecordingId === $observation->canonicalRecordingId
            && $rehydrated->provider === $observation->provider
            && $rehydrated->providerItemId === $observation->providerItemId
            && $rehydrated->metric === $observation->metric
            && $rehydrated->metricUnit === $observation->metricUnit
            && $rehydrated->metricSemanticsVersion === $observation->metricSemanticsVersion
            && (float) $rehydrated->value === (float) $observation->value
            && $rehydrated->observedAt->format(DATE_ATOM) === $observation->observedAt->format(DATE_ATOM)
            && $rehydrated->fetchedAt?->format(DATE_ATOM) === $observation->fetchedAt?->format(DATE_ATOM)
            && $rehydrated->sourceReference === $observation->sourceReference;

        if (! $same) {
            throw new RuntimeException('Metric observation id was reused for different evidence.');
        }
    }

    private function rehydrate(object $row): ChartMetricObservation
    {
        $value = match ((string) $row->value_kind) {
            'int' => (int) $row->value,
            'float' => (float) $row->value,
            default => throw new RuntimeException('Unknown persisted metric value kind.'),
        };

        return new ChartMetricObservation(
            observationId: (string) $row->observation_id,
            canonicalRecordingId: (string) $row->canonical_recording_id,
            provider: (string) $row->provider,
            providerItemId: (string) $row->provider_item_id,
            metric: (string) $row->metric,
            value: $value,
            observedAt: new DateTimeImmutable((string) $row->observed_at),
            metricUnit: (string) $row->metric_unit,
            metricSemanticsVersion: (string) $row->metric_semantics_version,
            fetchedAt: $row->fetched_at === null ? null : new DateTimeImmutable((string) $row->fetched_at),
            sourceReference: $row->source_reference === null ? null : (string) $row->source_reference,
        );
    }

    private function serializeValue(int|float $value): string
    {
        if (is_int($value)) {
            return (string) $value;
        }

        return number_format($value, 8, '.', '');
    }
}
