<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Extensions\ExtensionUpgradeManager;
use Illuminate\Console\Command;

final class RollbackExtensionCommand extends Command
{
    protected $signature = 'extension:rollback {slug} {--release=}';

    protected $description = 'Switch an extension back to a retained release.';

    public function handle(ExtensionUpgradeManager $manager): int
    {
        $manager->rollback((string) $this->argument('slug'), $this->option('release') ?: null);
        $this->components->info('Extension rollback completed.');

        return self::SUCCESS;
    }
}
