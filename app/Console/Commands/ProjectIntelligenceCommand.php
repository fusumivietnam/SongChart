<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

final class ProjectIntelligenceCommand extends Command
{
    protected $signature = 'project:intelligence
        {--json : Emit the structural/connectivity snapshot as machine-readable JSON}
        {--write : Persist the snapshot under storage/project-intelligence/{sha}}';

    protected $description = 'Inspect or persist the SongChart structural and source-connectivity project intelligence snapshot';

    public function handle(): int
    {
        $arguments = [PHP_BINARY, base_path('scripts/project-intelligence.php')];

        if ((bool) $this->option('json')) {
            $arguments[] = '--json';
        }

        if ((bool) $this->option('write')) {
            $arguments[] = '--write';
        }

        $process = new Process($arguments, base_path());
        $process->setTimeout(120);
        $process->run(function (string $type, string $buffer): void {
            if ($type === Process::ERR) {
                fwrite(STDERR, $buffer);

                return;
            }

            $this->getOutput()->write($buffer);
        });

        return $process->getExitCode() ?? self::FAILURE;
    }
}
