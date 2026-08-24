<?php

declare(strict_types=1);

namespace App\Extensions;

use App\Domain\Extensions\Enums\ExtensionState;
use App\Models\Extension;
use App\Models\ExtensionOperation;
use App\Models\ExtensionRelease;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

final class ExtensionInstaller
{
    public function __construct(private ExtensionPreflight $preflight, private SafeZipExtractor $extractor, private Filesystem $files, private PluginRegistry $plugins, private ThemeRegistry $themes, private ExtensionMigrationRunner $migrations, private ExtensionHealthChecker $health) {}

    public function install(string $archive, string $type, bool $enable = false): ExtensionManifest
    {
        $result = $this->preflight->inspect($archive, $type);
        if (! $result->passed()) {
            throw new RuntimeException('Preflight failed: '.implode(' ', $result->errors));
        }
        $manifest = $result->manifest;
        if (Schema::hasTable('extensions') && Extension::query()->where('slug', $manifest->slug())->exists()) {
            throw new RuntimeException('Extension is already installed. Use the upgrade command.');
        }
        [$vendor,$package] = explode('/', $manifest->slug(), 2);
        $relative = ($type === 'plugin' ? 'plugins' : 'themes')."/{$vendor}/{$package}/releases/{$manifest->version()}";
        $releasePath = base_path($relative);
        if (is_dir($releasePath)) {
            throw new RuntimeException('Release directory already exists.');
        }
        $temporary = storage_path('app/extensions/staging/'.bin2hex(random_bytes(10)));
        try {
            $this->extractor->extract($archive, $temporary);
            $root = $this->locatePackageRoot($temporary, $type.'.json');
            $this->files->ensureDirectoryExists(dirname($releasePath));
            if (! $this->files->moveDirectory($root, $releasePath)) {
                throw new RuntimeException('Unable to stage extension release.');
            }
            $this->files->deleteDirectory($temporary);
            $migration = $this->migrations->migrate($relative);
            $entry = ['path' => $relative, 'release' => $manifest->version(), 'provider' => $manifest->serviceProvider(), 'namespace' => $manifest->namespace(), 'source' => $relative.'/'.$manifest->sourceDirectory(), 'enabled' => $enable];
            $health = $type === 'plugin' ? $this->health->checkPlugin($entry) : ['healthy' => is_dir(base_path($relative.'/resources/views')), 'summary' => 'Theme release is readable.'];
            if ($enable && ! $health['healthy']) {
                throw new RuntimeException('Health check failed: '.$health['summary']);
            }
            if (Schema::hasTable('extensions')) {
                DB::transaction(function () use ($manifest, $type, $enable, $relative, $result, $archive, $migration, $health): void {
                    $extension = Extension::query()->create(['type' => $type, 'slug' => $manifest->slug(), 'name' => $manifest->name(), 'state' => $enable ? ExtensionState::Enabled : ExtensionState::Disabled, 'active_version' => $manifest->version(), 'active_path' => $relative, 'enabled' => $enable, 'manifest' => $manifest->data, 'health_status' => $health['healthy'] ? 'healthy' : 'warning', 'last_health_checked_at' => now(), 'installed_at' => now()]);
                    ExtensionRelease::query()->create(['extension_id' => $extension->id, 'version' => $manifest->version(), 'path' => $relative, 'checksum_sha256' => $result->checksum, 'signature_status' => $result->facts['signature']['status'] ?? 'unsigned', 'status' => 'active', 'manifest' => $manifest->data, 'installed_at' => now()]);
                    ExtensionOperation::query()->create(['extension_id' => $extension->id, 'type' => 'install', 'status' => 'succeeded', 'actor_id' => auth()->id(), 'source' => $archive, 'checksum_sha256' => $result->checksum, 'details' => ['preflight' => $result->status(), 'warnings' => $result->warnings, 'migration' => $migration, 'health' => $health, 'signature' => $result->facts['signature'] ?? null], 'started_at' => now(), 'finished_at' => now()]);
                });
            }
            $this->writeBootstrapRegistry($manifest, $type, $relative, $enable);

            return $manifest;
        } catch (Throwable $e) {
            $this->files->deleteDirectory($temporary);
            if (is_dir($releasePath)) {
                $this->files->deleteDirectory($releasePath);
            }
            throw $e;
        }
    }

    private function writeBootstrapRegistry(ExtensionManifest $manifest, string $type, string $path, bool $enable): void
    {
        if ($type === 'plugin') {
            $this->plugins->put($manifest->slug(), ['path' => $path, 'release' => $manifest->version(), 'provider' => $manifest->serviceProvider(), 'namespace' => $manifest->namespace(), 'source' => $path.'/'.$manifest->sourceDirectory(), 'enabled' => $enable]);
        } else {
            $this->themes->put($manifest->slug(), ['path' => $path, 'release' => $manifest->version(), 'enabled' => true]);
            if ($enable) {
                $this->themes->activate($manifest->slug());
            }
        }
    }

    private function locatePackageRoot(string $temporary, string $manifest): string
    {
        if (is_file($temporary.'/'.$manifest)) {
            return $temporary;
        }

        $dirs = array_values(array_filter(glob($temporary.'/*') ?: [], 'is_dir'));
        if (count($dirs) === 1 && is_file($dirs[0].'/'.$manifest)) {
            return $dirs[0];
        }

        throw new RuntimeException("ZIP must contain {$manifest} at root or one top-level directory.");
    }
}
