<?php

declare(strict_types=1);

namespace App\Extensions;

final class ExtensionHealthChecker
{
    /**
     * @param  array<string, mixed>  $entry
     * @return array{healthy: bool, summary: string}
     */
    public function checkPlugin(array $entry): array
    {
        $source = base_path((string) ($entry['source'] ?? ''));
        if (! is_dir($source)) {
            return ['healthy' => false, 'summary' => 'Plugin source directory is missing.'];
        }
        $provider = $entry['provider'] ?? null;
        if ($provider && ! class_exists((string) $provider)) {
            return ['healthy' => false, 'summary' => 'Plugin service provider cannot be loaded.'];
        }

        return ['healthy' => true, 'summary' => 'Plugin files and service provider are available.'];
    }
}
