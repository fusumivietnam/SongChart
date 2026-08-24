<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Extensions\ExtensionRemovalManager;
use Illuminate\Console\Command;

final class RemoveExtensionCodeCommand extends Command
{
    protected $signature = 'extension:remove-code {slug}';

    protected $description = 'Remove extension release files while preserving database data.';

    public function handle(ExtensionRemovalManager $m): int
    {
        if (! $this->confirm('Remove extension code while preserving data?')) {
            return self::FAILURE;
        } $m->removeCode((string) $this->argument('slug'));
        $this->components->info('Extension code removed; data preserved.');

        return self::SUCCESS;
    }
}
