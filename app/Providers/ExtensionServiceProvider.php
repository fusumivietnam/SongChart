<?php

declare(strict_types=1);

namespace App\Providers;

use App\Extensions\ExtensionRuntimeRegistry;
use App\Extensions\ThemeManager;
use Illuminate\Support\ServiceProvider;

final class ExtensionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        foreach ((new ExtensionRuntimeRegistry)->enabledPlugins() as $entry) {
            if (($entry['enabled'] ?? false) !== true) {
                continue;
            }

            $this->registerPluginAutoload($entry);
            $provider = $entry['provider'] ?? null;

            if (is_string($provider) && class_exists($provider)) {
                $this->app->register($provider);
            }
        }
    }

    public function boot(ThemeManager $themes): void
    {
        $themes->boot();
    }

    /** @param array<string, mixed> $entry */
    private function registerPluginAutoload(array $entry): void
    {
        $namespace = rtrim((string) ($entry['namespace'] ?? ''), '\\').'\\';
        $source = base_path((string) ($entry['source'] ?? ''));

        if ($namespace === '\\' || ! is_dir($source)) {
            return;
        }

        spl_autoload_register(static function (string $class) use ($namespace, $source): void {
            if (! str_starts_with($class, $namespace)) {
                return;
            }

            $relative = substr($class, strlen($namespace));
            $file = $source.'/'.str_replace('\\', '/', $relative).'.php';

            if (is_file($file)) {
                require_once $file;
            }
        });
    }
}
