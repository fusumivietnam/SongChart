<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Application\Chart\Queries\RecordingDataTrace;
use Illuminate\Console\Command;
use RuntimeException;

final class TraceRecordingDataCommand extends Command
{
    protected $signature = 'songchart:data:trace {recording : Canonical Recording ULID} {--json : Emit machine-readable JSON}';

    protected $description = 'Trace a canonical Recording through provider destinations, metric history, chart snapshots and freshness.';

    public function handle(RecordingDataTrace $trace): int
    {
        try {
            $result = $trace->handle((string) $this->argument('recording'));
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        if ((bool) $this->option('json')) {
            $this->line((string) json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $entity = $result['entity'];
        $this->info($entity['title'].' ['.$entity['id'].']');
        $this->line('Public: '.$entity['public_url']);
        $this->line('Provider destinations: '.count($result['provider_destinations']));
        foreach ($result['charts'] as $chart) {
            $this->line(sprintf(
                '%s | observation=%s | snapshot=%s',
                $chart['chart_id'],
                $chart['observation_freshness']['state'],
                $chart['snapshot_freshness']['state'],
            ));
        }

        return self::SUCCESS;
    }
}
