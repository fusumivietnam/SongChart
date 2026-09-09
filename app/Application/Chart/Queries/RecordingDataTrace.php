<?php

declare(strict_types=1);

namespace App\Application\Chart\Queries;

use App\Domain\Catalog\Enums\EntityType;
use App\Models\Catalog\Recording;
use App\Support\Catalog\PublicEntityUrl;
use App\Support\Chart\ChartDefinitionRegistry;
use App\Support\Chart\DatabaseChartSnapshotStore;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Support\Facades\DB;
use RuntimeException;

final readonly class RecordingDataTrace
{
    public function __construct(
        private ChartDefinitionRegistry $definitions,
        private DatabaseChartSnapshotStore $snapshots,
    ) {}

    /** @return array<string, mixed> */
    public function handle(string $recordingId): array
    {
        $recording = Recording::query()->find($recordingId);
        if (! $recording instanceof Recording) {
            throw new RuntimeException("Recording [{$recordingId}] was not found.");
        }

        $destinations = DB::table('provider_destinations')
            ->where('entity_type', EntityType::Recording->value)
            ->where('entity_id', $recordingId)
            ->orderBy('provider_resource_id')
            ->get(['provider_id', 'provider_resource_id', 'review_state', 'privacy_status', 'last_checked_at'])
            ->map(static fn (object $row): array => [
                'provider_id' => (string) $row->provider_id,
                'provider_item_id' => (string) $row->provider_resource_id,
                'review_state' => (string) $row->review_state,
                'privacy_status' => $row->privacy_status === null ? null : (string) $row->privacy_status,
                'last_checked_at' => $row->last_checked_at === null ? null : (string) $row->last_checked_at,
            ])->all();

        $charts = [];
        foreach ($this->definitions->activeForEntityType(EntityType::Recording) as $definition) {
            $metric = (string) $definition['metric'];
            $latestObservation = DB::table('chart_metric_observations')
                ->where('canonical_recording_id', $recordingId)
                ->where('metric', $metric)
                ->orderByDesc('observed_at')
                ->orderByDesc('observation_id')
                ->first();
            $snapshot = $this->snapshots->latest((string) $definition['id']);
            $row = null;
            if ($snapshot !== null) {
                foreach ($snapshot->rows as $candidate) {
                    if (($candidate['canonical_recording_id'] ?? null) === $recordingId) {
                        $row = $candidate;
                        break;
                    }
                }
            }

            $charts[] = [
                'chart_id' => (string) $definition['id'],
                'metric' => $metric,
                'metric_unit' => (string) $definition['metric_unit'],
                'metric_semantics_version' => (string) $definition['metric_semantics_version'],
                'latest_observation' => $latestObservation === null ? null : [
                    'observation_id' => (string) $latestObservation->observation_id,
                    'provider' => (string) $latestObservation->provider,
                    'provider_item_id' => (string) $latestObservation->provider_item_id,
                    'value' => (string) $latestObservation->value,
                    'observed_at' => (string) $latestObservation->observed_at,
                    'fetched_at' => $latestObservation->fetched_at === null ? null : (string) $latestObservation->fetched_at,
                    'source_reference' => $latestObservation->source_reference === null ? null : (string) $latestObservation->source_reference,
                ],
                'observation_freshness' => $this->freshness(
                    $latestObservation?->observed_at,
                    (int) ($definition['freshness']['max_observation_age_seconds'] ?? 0),
                ),
                'latest_snapshot' => $snapshot === null ? null : [
                    'snapshot_at' => $snapshot->snapshotAt->format(DATE_ATOM),
                    'calculation_version' => $snapshot->calculationVersion,
                    'input_fingerprint' => $snapshot->inputFingerprint,
                    'row' => $row,
                ],
                'snapshot_freshness' => $this->freshness(
                    $snapshot?->snapshotAt->format(DATE_ATOM),
                    (int) ($definition['freshness']['max_snapshot_age_seconds'] ?? 0),
                ),
            ];
        }

        return [
            'entity' => [
                'type' => EntityType::Recording->value,
                'id' => (string) $recording->getKey(),
                'title' => (string) $recording->title,
                'slug' => (string) $recording->slug,
                'public_url' => PublicEntityUrl::to(EntityType::Recording, (string) $recording->slug),
            ],
            'provider_destinations' => $destinations,
            'charts' => $charts,
        ];
    }

    /** @return array{state:string,age_seconds:int|null,max_age_seconds:int} */
    private function freshness(mixed $timestamp, int $maxAgeSeconds): array
    {
        if (! is_string($timestamp) || trim($timestamp) === '' || $maxAgeSeconds <= 0) {
            return ['state' => 'unknown', 'age_seconds' => null, 'max_age_seconds' => $maxAgeSeconds];
        }

        $observed = new DateTimeImmutable($timestamp);
        $now = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $age = max(0, $now->getTimestamp() - $observed->getTimestamp());

        return [
            'state' => $age <= $maxAgeSeconds ? 'fresh' : 'stale',
            'age_seconds' => $age,
            'max_age_seconds' => $maxAgeSeconds,
        ];
    }
}
