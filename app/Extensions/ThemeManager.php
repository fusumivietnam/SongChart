<?php

declare(strict_types=1);

namespace App\Extensions;

use Illuminate\View\Factory;

final class ThemeManager
{
    public function __construct(private ExtensionRuntimeRegistry $runtime, private Factory $views) {}

    public function boot(): void
    {
        $r = $this->runtime->themes();
        $entry = $r['installed'][$r['active']] ?? null;
        if (! is_array($entry)) {
            return;
        } $path = base_path((string) $entry['path'].'/resources/views');
        if (is_dir($path)) {
            $this->views->prependLocation($path);
            $this->views->addNamespace('theme', $path);
        }
    }

    public function active(): string
    {
        return $this->runtime->themes()['active'];
    }
}
