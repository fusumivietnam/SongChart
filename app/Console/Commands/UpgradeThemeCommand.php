<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Extensions\ExtensionUpgradeManager;
use Illuminate\Console\Command;

final class UpgradeThemeCommand extends Command
{
    protected $signature = 'theme:upgrade {zip} {--stage-only}';

    protected $description = 'Stage and safely upgrade a theme release.';

    public function handle(ExtensionUpgradeManager $manager): int
    {
        $release = $manager->upgrade($this->path((string) $this->argument('zip')), 'theme', ! (bool) $this->option('stage-only'));
        $this->components->info("Theme release {$release->version} prepared successfully.");

        return self::SUCCESS;
    }

    private function path(string $path): string
    {
        return str_starts_with($path, '/') || preg_match('/^[A-Za-z]:[\\\\\/]/', $path) ? $path : base_path($path);
    }
}
