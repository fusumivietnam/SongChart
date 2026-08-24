<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Extensions\ExtensionHealthChecker;
use App\Extensions\ExtensionManager;
use App\Extensions\PluginRegistry;
use Illuminate\Console\Command;

final class EnablePluginCommand extends Command
{
    protected $signature = 'plugin:enable {slug}';

    protected $description = 'Run health checks and enable an installed plugin.';

    public function handle(PluginRegistry $r, ExtensionHealthChecker $h, ExtensionManager $m): int
    {
        $slug = (string) $this->argument('slug');
        $entry = $r->all()[$slug] ?? null;
        if (! is_array($entry)) {
            $this->components->error('Plugin not installed.');

            return self::FAILURE;
        }

        $result = $h->checkPlugin($entry);
        if (! $result['healthy']) {
            $this->components->error($result['summary']);

            return self::FAILURE;
        }

        $m->setPluginEnabled($slug, true);
        $this->components->info('Plugin enabled.');

        return self::SUCCESS;
    }
}
