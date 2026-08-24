<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Extensions\ExtensionUpgradeManager;
use Illuminate\Console\Command;

final class CleanupExtensionCommand extends Command
{
    protected $signature = 'extension:cleanup {slug} {--keep=2}';

    protected $description = 'Remove old inactive extension releases.';

    public function handle(ExtensionUpgradeManager $manager): int
    {
        $count = $manager->cleanup((string) $this->argument('slug'), max(1, (int) $this->option('keep')));
        $this->components->info("Removed {$count} old release(s).");

        return self::SUCCESS;
    }
}
