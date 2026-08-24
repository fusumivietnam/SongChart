<?php

declare(strict_types=1);

namespace App\Extensions;

use App\Domain\Extensions\Enums\ExtensionState;
use App\Models\Extension;
use App\Models\ExtensionOperation;
use App\Models\ExtensionRelease;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

final class ExtensionUpgradeManager
{
    public function __construct(
        private ExtensionPreflight $preflight,
        private SafeZipExtractor $extractor,
        private Filesystem $files,
        private ExtensionSnapshotManager $snapshots,
        private ExtensionMigrationRunner $migrations,
        private ExtensionHealthChecker $health,
        private PluginRegistry $plugins,
        private ThemeRegistry $themes,
    ) {}

    public function upgrade(string $archive, string $type, bool $activate = true): ExtensionRelease
    {
        $result = $this->preflight->inspect($archive, $type);
        if (! $result->passed()) {
            throw new RuntimeException('Preflight failed: '.implode(' ', $result->errors));
        }

        $manifest = $result->manifest;
        $extension = Extension::query()->where('slug', $manifest->slug())->where('type', $type)->firstOrFail();
        if ($extension->active_version === $manifest->version()) {
            throw new RuntimeException('The requested version is already active.');
        }

        [$vendor, $package] = explode('/', $manifest->slug(), 2);
        $releasePath = ($type === 'plugin' ? 'plugins' : 'themes')."/{$vendor}/{$package}/releases/{$manifest->version()}";
        if (is_dir(base_path($releasePath))) {
            throw new RuntimeException('Target release already exists.');
        }

        $operation = ExtensionOperation::query()->create([
            'extension_id' => $extension->id,
            'type' => 'upgrade',
            'status' => 'running',
            'actor_id' => auth()->id(),
            'source' => $archive,
            'checksum_sha256' => $result->checksum,
            'details' => ['from' => $extension->active_version, 'to' => $manifest->version(), 'warnings' => $result->warnings],
            'started_at' => now(),
        ]);

        $snapshot = $this->snapshots->create($extension, 'upgrade');
        $temporary = storage_path('app/extensions/staging/'.bin2hex(random_bytes(10)));

        try {
            $this->extractor->extract($archive, $temporary);
            $root = $this->locatePackageRoot($temporary, $type.'.json');
            $this->files->ensureDirectoryExists(dirname(base_path($releasePath)));
            if (! $this->files->moveDirectory($root, base_path($releasePath))) {
                throw new RuntimeException('Unable to stage extension release.');
            }
            $this->files->deleteDirectory($temporary);

            $migration = $this->migrations->migrate($releasePath);
            $entry = $this->runtimeEntry($manifest, $releasePath, true);
            $health = $type === 'plugin' ? $this->health->checkPlugin($entry) : ['healthy' => is_dir(base_path($releasePath.'/resources/views')), 'summary' => 'Theme release is readable.'];
            if (! $health['healthy']) {
                throw new RuntimeException($health['summary']);
            }

            return DB::transaction(function () use ($extension, $manifest, $releasePath, $result, $activate, $operation, $snapshot, $migration, $health, $type): ExtensionRelease {
                ExtensionRelease::query()->where('extension_id', $extension->id)->where('status', 'active')->update(['status' => 'previous']);
                $release = ExtensionRelease::query()->create([
                    'extension_id' => $extension->id,
                    'version' => $manifest->version(),
                    'path' => $releasePath,
                    'checksum_sha256' => $result->checksum,
                    'signature_status' => 'unsigned',
                    'status' => $activate ? 'active' : 'staged',
                    'manifest' => $manifest->data,
                    'installed_at' => now(),
                ]);

                if ($activate) {
                    $extension->update([
                        'state' => $extension->enabled ? ExtensionState::Enabled : ExtensionState::Disabled,
                        'active_version' => $manifest->version(),
                        'active_path' => $releasePath,
                        'manifest' => $manifest->data,
                        'health_status' => 'healthy',
                        'last_health_checked_at' => now(),
                    ]);
                    $this->writeRegistry($manifest, $type, $releasePath, $extension->enabled);
                }

                $operation->update([
                    'status' => 'succeeded',
                    'details' => array_merge($operation->details ?? [], ['snapshot_id' => $snapshot->id, 'migration' => $migration, 'health' => $health, 'activated' => $activate]),
                    'finished_at' => now(),
                ]);

                return $release;
            });
        } catch (Throwable $e) {
            $this->files->deleteDirectory($temporary);
            if (is_dir(base_path($releasePath))) {
                $this->files->deleteDirectory(base_path($releasePath));
            }
            $operation->update(['status' => 'failed', 'error_summary' => $e->getMessage(), 'finished_at' => now()]);
            throw $e;
        }
    }

    public function rollback(string $slug, ?string $version = null): void
    {
        $extension = Extension::query()->where('slug', $slug)->firstOrFail();
        /** @var ExtensionRelease|null $target */
        $target = $version
            ? $extension->releases()->where('version', $version)->firstOrFail()
            : $extension->releases()->where('version', '!=', $extension->active_version)->whereIn('status', ['previous', 'active'])->latest('installed_at')->first();
        if (! $target) {
            throw new RuntimeException('No rollback release is available.');
        }
        if (! is_dir(base_path($target->path))) {
            throw new RuntimeException('Rollback release files are missing.');
        }

        $operation = ExtensionOperation::query()->create([
            'extension_id' => $extension->id,
            'type' => 'rollback',
            'status' => 'running',
            'actor_id' => auth()->id(),
            'details' => ['from' => $extension->active_version, 'to' => $target->version],
            'started_at' => now(),
        ]);
        $snapshot = $this->snapshots->create($extension, 'rollback');

        try {
            $targetManifestValue = $target->getAttribute('manifest');
            $targetManifest = is_array($targetManifestValue) ? $targetManifestValue : [];
            $entry = $this->runtimeEntryFromData($targetManifest, $target->path, true);
            $health = $extension->type === 'plugin' ? $this->health->checkPlugin($entry) : ['healthy' => true, 'summary' => 'Theme release is readable.'];
            if (! $health['healthy']) {
                throw new RuntimeException($health['summary']);
            }

            DB::transaction(function () use ($extension, $target, $operation, $snapshot): void {
                $extension->releases()->where('status', 'active')->update(['status' => 'previous']);
                $target->update(['status' => 'active']);
                $targetManifestValue = $target->getAttribute('manifest');
                $targetManifest = is_array($targetManifestValue) ? $targetManifestValue : [];
                $extension->update(['active_version' => $target->version, 'active_path' => $target->path, 'manifest' => $targetManifest, 'health_status' => 'healthy', 'last_health_checked_at' => now()]);
                $this->writeRegistryFromData($extension->slug, $extension->type, $targetManifest, $target->path, $extension->enabled);
                $operation->update(['status' => 'succeeded', 'details' => array_merge($operation->details ?? [], ['snapshot_id' => $snapshot->id]), 'finished_at' => now()]);
            });
        } catch (Throwable $e) {
            $operation->update(['status' => 'failed', 'error_summary' => $e->getMessage(), 'finished_at' => now()]);
            throw $e;
        }
    }

    public function cleanup(string $slug, int $keep = 2): int
    {
        $extension = Extension::query()->where('slug', $slug)->firstOrFail();
        $protected = $extension->releases()->orderByDesc('installed_at')->limit(max(1, $keep))->pluck('id')->all();
        $releases = $extension->releases()->whereNotIn('id', $protected)->where('status', '!=', 'active')->get();
        $deleted = 0;
        foreach ($releases as $release) {
            if (is_dir(base_path($release->path))) {
                $this->files->deleteDirectory(base_path($release->path));
            }
            $release->delete();
            $deleted++;
        }
        ExtensionOperation::query()->create(['extension_id' => $extension->id, 'type' => 'cleanup', 'status' => 'succeeded', 'actor_id' => auth()->id(), 'details' => ['deleted_releases' => $deleted, 'keep' => $keep], 'started_at' => now(), 'finished_at' => now()]);

        return $deleted;
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

    /** @return array<string,mixed> */
    private function runtimeEntry(ExtensionManifest $manifest, string $path, bool $enabled): array
    {
        return $this->runtimeEntryFromData($manifest->data, $path, $enabled);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function runtimeEntryFromData(array $data, string $path, bool $enabled): array
    {
        $autoload = is_array($data['autoload'] ?? null) ? $data['autoload'] : [];

        return ['path' => $path, 'release' => $data['version'] ?? null, 'provider' => $data['service_provider'] ?? null, 'namespace' => $autoload['namespace'] ?? null, 'source' => $path.'/'.($autoload['path'] ?? 'src'), 'enabled' => $enabled];
    }

    private function writeRegistry(ExtensionManifest $manifest, string $type, string $path, bool $enabled): void
    {
        $this->writeRegistryFromData($manifest->slug(), $type, $manifest->data, $path, $enabled);
    }

    /** @param array<string,mixed> $data */
    private function writeRegistryFromData(string $slug, string $type, array $data, string $path, bool $enabled): void
    {
        if ($type === 'plugin') {
            $this->plugins->put($slug, $this->runtimeEntryFromData($data, $path, $enabled));

            return;
        }
        $this->themes->put($slug, ['path' => $path, 'release' => $data['version'] ?? null, 'enabled' => true]);
        if ($enabled) {
            $this->themes->activate($slug);
        }
    }
}
