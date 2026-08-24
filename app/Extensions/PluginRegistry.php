<?php

declare(strict_types=1);

namespace App\Extensions;

use RuntimeException;

final class PluginRegistry
{
    /** @return array<string, array<string, mixed>> */
    public function all(): array
    {
        return require base_path('plugins/registry.php');
    }

    /** @param array<string, mixed> $entry */
    public function put(string $slug, array $entry): void
    {
        $registry = $this->all();
        $registry[$slug] = $entry;
        ksort($registry);
        $this->write($registry);
    }

    /** @param array<string, array<string, mixed>> $registry */
    private function write(array $registry): void
    {
        $content = "<?php\n\nreturn ".var_export($registry, true).";\n";
        $temp = base_path('plugins/registry.php.tmp');
        file_put_contents($temp, $content, LOCK_EX);
        if (! rename($temp, base_path('plugins/registry.php'))) {
            throw new RuntimeException('Unable to update plugin registry.');
        }
    }
}
