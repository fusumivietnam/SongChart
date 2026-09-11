<?php

declare(strict_types=1);

namespace App\Support\Chart;

use App\Domain\Chart\DTO\ChartSnapshot;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

final readonly class DatabaseChartSnapshotStore
{
    public function append(ChartSnapshot $snapshot): string
    {
        $existing = DB::table('chart_snapshots')
            ->where('chart_key', $snapshot->chartId)
            ->where('calculation_version', $snapshot->calculationVersion)
            ->where('input_fingerprint', $snapshot->inputFingerprint)
            ->value('id');

        if ($existing !== null) {
            return (string) $existing;
        }

        $id = (string) Str::ulid();
        $payload = [
            'chart_id' => $snapshot->chartId,
            'metric' => $snapshot->metric,
            'calculation_version' => $snapshot->calculationVersion,
            'input_fingerprint' => $snapshot->inputFingerprint,
            'snapshot_at' => $snapshot->snapshotAt->format(DATE_ATOM),
            'rows' => $snapshot->rows,
        ];

        DB::table('chart_snapshots')->insertOrIgnore([
            'id' => $id,
            'chart_key' => $snapshot->chartId,
            'metric' => $snapshot->metric,
            'calculation_version' => $snapshot->calculationVersion,
            'input_fingerprint' => $snapshot->inputFingerprint,
            'snapshot_at' => $snapshot->snapshotAt,
            'payload' => json_encode($payload, JSON_THROW_ON_ERROR),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $stored = DB::table('chart_snapshots')
            ->where('chart_key', $snapshot->chartId)
            ->where('calculation_version', $snapshot->calculationVersion)
            ->where('input_fingerprint', $snapshot->inputFingerprint)
            ->value('id');

        if ($stored === null) {
            throw new RuntimeException('Chart snapshot could not be persisted.');
        }

        return (string) $stored;
    }

    public function latest(string $chartId): ?ChartSnapshot
    {
        $row = DB::table('chart_snapshots')
            ->where('chart_key', $chartId)
            ->orderByDesc('snapshot_at')
            ->orderByDesc('id')
            ->first();

        if ($row === null) {
            return null;
        }

        $payload = json_decode((string) $row->payload, true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($payload) || ! isset($payload['rows']) || ! is_array($payload['rows'])) {
            throw new RuntimeException('Stored chart snapshot payload is invalid.');
        }

        return new ChartSnapshot(
            (string) $row->chart_key,
            (string) $row->metric,
            (string) $row->calculation_version,
            (string) $row->input_fingerprint,
            new DateTimeImmutable((string) $row->snapshot_at),
            $payload['rows'],
        );
    }
}
