<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Extensions\ExtensionPreflight;
use Illuminate\Console\Command;

final class InspectThemeCommand extends Command
{
    protected $signature = 'theme:inspect {zip}';

    protected $description = 'Inspect a theme ZIP without executing package code.';

    public function handle(ExtensionPreflight $preflight): int
    {
        $result = $preflight->inspect($this->path((string) $this->argument('zip')), 'theme');

        $this->table(
            ['Field', 'Value'],
            [
                ['Slug', $result->manifest->slug()],
                ['Version', $result->manifest->version()],
                ['SHA-256', $result->checksum],
                ['Status', $result->status()],
            ],
        );

        foreach ($result->warnings as $warning) {
            $this->components->warn($warning);
        }

        foreach ($result->errors as $error) {
            $this->components->error($error);
        }

        return $result->passed() ? self::SUCCESS : self::FAILURE;
    }

    private function path(string $path): string
    {
        return str_starts_with($path, '/') || preg_match('~^[A-Za-z]:[\\/]~', $path)
            ? $path
            : base_path($path);
    }
}
