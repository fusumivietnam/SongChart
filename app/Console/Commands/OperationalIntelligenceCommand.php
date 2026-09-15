<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Application\Operations\Queries\OperationalIntelligenceReadModel;
use App\Support\Operations\OperationalIntelligenceSnapshot;
use Illuminate\Console\Command;

final class OperationalIntelligenceCommand extends Command
{
    protected $signature = 'operations:intelligence {--json : Emit machine-readable operational intelligence}';

    protected $description = 'Inspect the bounded SongChart operational intelligence scorecard';

    public function handle(
        OperationalIntelligenceReadModel $readModel,
        OperationalIntelligenceSnapshot $snapshot,
    ): int {
        $result = $snapshot->build($readModel->evidence());

        if ((bool) $this->option('json')) {
            $this->line((string) json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return self::SUCCESS;
        }

        $this->info('SongChart Operational Intelligence');
        $this->line('Status: '.(string) $result['status']);
        $this->line('Scale scorecard: '.(string) $result['scale_scorecard']['status']);

        /** @var array<string, array<string, mixed>> $metrics */
        $metrics = $result['metrics'];
        $this->table(
            ['Metric', 'Value', 'Status', 'Owner'],
            array_map(
                static fn (string $key, array $metric): array => [
                    $key,
                    $metric['value'] === null ? 'unavailable' : (string) $metric['value'],
                    (string) $metric['status'],
                    (string) $metric['owner'],
                ],
                array_keys($metrics),
                array_values($metrics),
            ),
        );

        return self::SUCCESS;
    }
}
