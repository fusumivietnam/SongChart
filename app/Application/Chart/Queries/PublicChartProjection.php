<?php

declare(strict_types=1);

namespace App\Application\Chart\Queries;

use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Chart\DTO\ChartSnapshot;
use App\Models\Catalog\Recording;
use App\Support\Catalog\PublicEntityUrl;
use LogicException;

final readonly class PublicChartProjection
{
    /** @return array{chart_id:string,metric:string,calculation_version:string,snapshot_at:string,rows:list<array{rank:int,canonical_recording_id:string,title:string,slug:string,url:string,score:float,observation_ids:list<string>}>} */
    public function fromSnapshot(ChartSnapshot $snapshot): array
    {
        $ids = array_map(static fn (array $row): string => $row['canonical_recording_id'], $snapshot->rows);
        $recordings = Recording::query()->whereIn('id', $ids)->get()->keyBy(fn (Recording $recording): string => (string) $recording->getKey());

        $rows = [];
        foreach ($snapshot->rows as $row) {
            $recording = $recordings->get($row['canonical_recording_id']);
            if (! $recording instanceof Recording) {
                throw new LogicException('Chart snapshot references a missing canonical recording.');
            }

            $slug = (string) $recording->getAttribute('slug');
            $rows[] = [
                'rank' => $row['rank'],
                'canonical_recording_id' => (string) $recording->getKey(),
                'title' => (string) $recording->getAttribute('title'),
                'slug' => $slug,
                'url' => PublicEntityUrl::to(EntityType::Recording, $slug),
                'score' => $row['score'],
                'observation_ids' => $row['observation_ids'],
            ];
        }

        return [
            'chart_id' => $snapshot->chartId,
            'metric' => $snapshot->metric,
            'calculation_version' => $snapshot->calculationVersion,
            'snapshot_at' => $snapshot->snapshotAt->format(DATE_ATOM),
            'rows' => $rows,
        ];
    }
}
