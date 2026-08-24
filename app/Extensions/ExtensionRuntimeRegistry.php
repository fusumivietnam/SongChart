<?php

declare(strict_types=1);

namespace App\Extensions;

use App\Models\Extension;
use Illuminate\Support\Facades\Schema;
use Throwable;

final class ExtensionRuntimeRegistry
{
    /** @return array<string, array<string, mixed>> */
    public function enabledPlugins(): array
    {
        try {
            if (! Schema::hasTable('extensions')) {
                return (new PluginRegistry)->all();
            }

            return Extension::query()
                ->where('type', 'plugin')
                ->where('enabled', true)
                ->get()
                ->mapWithKeys(function (Extension $extension): array {
                    $manifestValue = $extension->getAttribute('manifest');
                    $manifest = is_array($manifestValue) ? $manifestValue : [];
                    $autoload = is_array($manifest['autoload'] ?? null) ? $manifest['autoload'] : [];
                    $path = (string) $extension->active_path;

                    return [$extension->slug => [
                        'path' => $path,
                        'release' => $extension->active_version,
                        'provider' => $manifest['service_provider'] ?? null,
                        'namespace' => $autoload['namespace'] ?? null,
                        'source' => $path.'/'.($autoload['path'] ?? 'src'),
                        'enabled' => true,
                    ]];
                })
                ->all();
        } catch (Throwable) {
            return (new PluginRegistry)->all();
        }
    }

    /** @return array{active:string, installed:array<string, array<string, mixed>>} */
    public function themes(): array
    {
        try {
            if (! Schema::hasTable('extensions')) {
                return (new ThemeRegistry)->all();
            }

            $installed = [];
            $active = 'songchart/default';

            foreach (Extension::query()->where('type', 'theme')->get() as $extension) {
                $installed[$extension->slug] = [
                    'path' => $extension->active_path,
                    'release' => $extension->active_version,
                    'enabled' => true,
                ];

                if ($extension->enabled) {
                    $active = $extension->slug;
                }
            }

            return ['active' => $active, 'installed' => $installed];
        } catch (Throwable) {
            return (new ThemeRegistry)->all();
        }
    }
}
