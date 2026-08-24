<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Extensions\ExtensionRemovalManager;
use Illuminate\Console\Command;

final class UninstallExtensionCommand extends Command
{
    protected $signature = 'extension:uninstall {slug} {--purge}';

    protected $description = 'Uninstall an extension; purge only declared owned data.';

    public function handle(ExtensionRemovalManager $m): int
    {
        $purge = (bool) $this->option('purge');
        if (! $this->confirm($purge ? 'Permanently purge declared extension-owned data?' : 'Uninstall code and preserve data?')) {
            return self::FAILURE;
        } $m->uninstall((string) $this->argument('slug'), $purge);
        $this->components->info($purge ? 'Extension purged.' : 'Extension uninstalled; data preserved.');

        return self::SUCCESS;
    }
}
