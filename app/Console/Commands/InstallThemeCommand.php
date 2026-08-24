<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Extensions\ExtensionInstaller;
use Illuminate\Console\Command;

final class InstallThemeCommand extends Command
{
    protected $signature = 'theme:install {zip} {--activate}';

    protected $description = 'Preflight and install a versioned theme release.';

    public function handle(ExtensionInstaller $installer): int
    {
        $m = $installer->install($this->path((string) $this->argument('zip')), 'theme', (bool) $this->option('activate'));
        $this->components->info("Installed {$m->slug()} {$m->version()} in a versioned release.");

        return self::SUCCESS;
    }

    private function path(string $p): string
    {
        return str_starts_with($p, '/') || preg_match('~^[A-Za-z]:[\\/]~', $p) ? $p : base_path($p);
    }
}
