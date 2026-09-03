<?php

declare(strict_types=1);

namespace App\Support\Engineering;

use Illuminate\Support\Facades\Cache;

final class ProjectIntelligenceSnapshotReader
{
    /** @return array<string, mixed> */
    public function latest(): array
    {
        return Cache::remember('project-intelligence:latest', 60, fn (): array => $this->readLatest());
    }

    /** @return array<string, mixed> */
    private function readLatest(): array
    {
        $root = storage_path('project-intelligence');
        if (! is_dir($root)) {
            return $this->unavailable();
        }

        $candidates = [];
        foreach (glob($root.'/*/architecture-graph.json') ?: [] as $path) {
            if (! is_file($path)) {
                continue;
            }

            $candidates[$path] = filemtime($path) ?: 0;
        }

        if ($candidates === []) {
            return $this->unavailable();
        }

        arsort($candidates);
        $path = (string) array_key_first($candidates);
        $decoded = json_decode((string) file_get_contents($path), true);

        if (! is_array($decoded)) {
            return [
                ...$this->unavailable(),
                'status' => 'failed',
                'message' => 'Snapshot Development Intelligence gần nhất không đọc được.',
            ];
        }

        $snapshot = is_array($decoded['snapshot'] ?? null) ? $decoded['snapshot'] : [];
        $metrics = is_array($decoded['metrics'] ?? null) ? $decoded['metrics'] : [];

        return [
            'available' => true,
            'status' => (string) ($snapshot['status'] ?? 'fresh'),
            'sha' => $snapshot['head_sha'] ?? null,
            'short_sha' => is_string($snapshot['head_sha'] ?? null) ? substr($snapshot['head_sha'], 0, 8) : null,
            'branch' => $snapshot['branch'] ?? null,
            'metrics' => $metrics,
            'generated_at' => date(DATE_ATOM, (int) ($candidates[$path] ?? time())),
            'message' => null,
        ];
    }

    /** @return array<string, mixed> */
    private function unavailable(): array
    {
        return [
            'available' => false,
            'status' => 'stale',
            'sha' => null,
            'short_sha' => null,
            'branch' => null,
            'metrics' => [],
            'generated_at' => null,
            'message' => 'Chưa có snapshot Development Intelligence. Chỉ tạo khi SA/Dev/Tech yêu cầu hoặc qua refresh nền.',
        ];
    }
}
