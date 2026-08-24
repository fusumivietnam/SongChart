<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Extensions\ExtensionManager;
use Illuminate\Console\Command;

final class ActivateThemeCommand extends Command
{
    protected $signature = 'theme:activate {slug}';

    protected $description = 'Activate an installed versioned theme.';

    public function handle(ExtensionManager $m): int
    {
        $m->activateTheme((string) $this->argument('slug'));
        $this->components->info('Theme activated.');

        return self::SUCCESS;
    }
}
