<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Extensions\ExtensionInstaller;
use Illuminate\Console\Command;

final class InstallPluginCommand extends Command
{
    protected $signature = 'plugin:install {zip} {--enable}';

    protected $description = 'Preflight and install a versioned plugin release.';

    public function handle(ExtensionInstaller $installer): int
    {
        $m = $installer->install($this->path((string) $this->argument('zip')), 'plugin', (bool) $this->option('enable'));
        $this->components->info("Installed {$m->slug()} {$m->version()} in a versioned release.");

        return self::SUCCESS;
    }

    private function path(string $p): string
    {
        return str_starts_with($p, '/') || preg_match('~^[A-Za-z]:[\\/]~', $p) ? $p : base_path($p);
    }
}
