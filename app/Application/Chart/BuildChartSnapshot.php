<?php

declare(strict_types=1);

namespace App\Application\Chart;

use App\Domain\Chart\DTO\ChartMetricObservation;
use App\Domain\Chart\DTO\ChartSnapshot;
use DateTimeImmutable;
use InvalidArgumentException;

final readonly class BuildChartSnapshot
{
    public const CALCULATION_VERSION = 'songchart-chart-v1';

    /** @param list<ChartMetricObservation> $observations */
    public function handle(string $chartId, string $metric, DateTimeImmutable $snapshotAt, array $observations): ChartSnapshot
    {
        if ($chartId === '' || $metric === '') {
            throw new InvalidArgumentException('Chart id and metric are required.');
        }

        $grouped = [];
        $fingerprintRows = [];
        $unit = null;
        $semanticsVersion = null;

        foreach ($observations as $observation) {
            if (($observation instanceof ChartMetricObservation) === false) {
                throw new InvalidArgumentException('Chart observations must use the provenance DTO contract.');
            }
            if ($observation->metric !== $metric) {
                throw new InvalidArgumentException('All chart observations must use the requested metric.');
            }
            if ($observation->canonicalRecordingId === '' || $observation->observationId === '' || $observation->provider === '' || $observation->providerItemId === '') {
                throw new InvalidArgumentException('Chart observations require canonical identity and provider provenance.');
            }
            if ($observation->observedAt > $snapshotAt) {
                throw new InvalidArgumentException('Chart observations cannot occur after the snapshot time.');
            }

            $unit ??= $observation->metricUnit;
            $semanticsVersion ??= $observation->metricSemanticsVersion;
            if ($observation->metricUnit !== $unit || $observation->metricSemanticsVersion !== $semanticsVersion) {
                throw new InvalidArgumentException('Chart observations must share one metric unit and semantics version.');
            }

            $input = [
                'observation_id' => $observation->observationId,
                'canonical_recording_id' => $observation->canonicalRecordingId,
                'provider' => $observation->provider,
                'provider_item_id' => $observation->providerItemId,
                'metric' => $observation->metric,
                'metric_unit' => $observation->metricUnit,
                'metric_semantics_version' => $observation->metricSemanticsVersion,
                'value' => (float) $observation->value,
                'observed_at' => $observation->observedAt->format(DATE_ATOM),
                'fetched_at' => $observation->fetchedAt?->format(DATE_ATOM),
                'source_reference' => $observation->sourceReference,
            ];
            $fingerprintRows[] = $input;

            $id = $observation->canonicalRecordingId;
            $grouped[$id] ??= ['score' => 0.0, 'observation_ids' => [], 'observations' => []];
            $grouped[$id]['score'] += (float) $observation->value;
            $grouped[$id]['observation_ids'][] = $observation->observationId;
            $grouped[$id]['observations'][] = $input;
        }

        usort($fingerprintRows, static fn (array $left, array $right): int => strcmp($left['observation_id'], $right['observation_id']));
        $inputFingerprint = hash('sha256', json_encode($fingerprintRows, JSON_THROW_ON_ERROR));

        $rows = [];
        foreach ($grouped as $recordingId => $data) {
            sort($data['observation_ids']);
            usort($data['observations'], static fn (array $left, array $right): int => strcmp($left['observation_id'], $right['observation_id']));
            $rows[] = [
                'rank' => 0,
                'canonical_recording_id' => (string) $recordingId,
                'score' => (float) $data['score'],
                'observation_ids' => $data['observation_ids'],
                'observations' => $data['observations'],
            ];
        }

        usort($rows, static function (array $left, array $right): int {
            $score = $right['score'] <=> $left['score'];
            return $score !== 0 ? $score : strcmp($left['canonical_recording_id'], $right['canonical_recording_id']);
        });

        foreach ($rows as $index => &$row) {
            $row['rank'] = $index + 1;
        }
        unset($row);

        return new ChartSnapshot($chartId, $metric, self::CALCULATION_VERSION, $inputFingerprint, $snapshotAt, $rows);
    }
}
