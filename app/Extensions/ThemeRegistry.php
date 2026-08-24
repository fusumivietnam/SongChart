<?php

declare(strict_types=1);

namespace App\Extensions;

use RuntimeException;

final class ThemeRegistry
{
    /** @return array{active:string,installed:array<string,array<string,mixed>>} */
    public function all(): array
    {
        return require base_path('themes/registry.php');
    }

    /** @param array<string, mixed> $entry */
    public function put(string $slug, array $entry): void
    {
        $registry = $this->all();
        $registry['installed'][$slug] = $entry;
        ksort($registry['installed']);
        $this->write($registry);
    }

    public function activate(string $slug): void
    {
        $registry = $this->all();
        if (! isset($registry['installed'][$slug])) {
            throw new RuntimeException("Theme is not installed: {$slug}");
        }
        $registry['active'] = $slug;
        $this->write($registry);
    }

    /** @param array{active:string,installed:array<string,array<string,mixed>>} $registry */
    private function write(array $registry): void
    {
        $content = "<?php\n\nreturn ".var_export($registry, true).";\n";
        $temp = base_path('themes/registry.php.tmp');
        file_put_contents($temp, $content, LOCK_EX);
        if (! rename($temp, base_path('themes/registry.php'))) {
            throw new RuntimeException('Unable to update theme registry.');
        }
    }
}
