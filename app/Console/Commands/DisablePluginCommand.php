<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Extensions\ExtensionManager;
use Illuminate\Console\Command;

final class DisablePluginCommand extends Command
{
    protected $signature = 'plugin:disable {slug}';

    protected $description = 'Disable a plugin while preserving code and data.';

    public function handle(ExtensionManager $m): int
    {
        $m->setPluginEnabled((string) $this->argument('slug'), false);
        $this->components->info('Plugin disabled.');

        return self::SUCCESS;
    }
}
