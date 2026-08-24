<?php

declare(strict_types=1);

namespace App\Extensions;

use App\Models\Extension;
use Composer\Semver\Semver;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

final class ExtensionPreflight
{
    public function __construct(
        private SafeZipExtractor $extractor,
        private PackageSignatureVerifier $signatures,
    ) {}

    public function inspect(string $archive, string $type): PreflightResult
    {
        if (! is_file($archive)) {
            throw new RuntimeException("Package not found: {$archive}");
        }

        $checksum = hash_file('sha256', $archive);
        if ($checksum === false) {
            throw new RuntimeException('Unable to calculate package checksum.');
        }

        $temporary = storage_path('app/extensions/quarantine/'.bin2hex(random_bytes(10)));
        $this->extractor->extract($archive, $temporary);

        try {
            $root = $this->locatePackageRoot($temporary, $type.'.json');
            $manifest = ExtensionManifest::fromFile($root.'/'.$type.'.json', $type);
            $errors = [];
            $warnings = [];
            $signature = $this->signatures->verify($archive, $checksum, $manifest);

            if ($signature['warning']) {
                $warnings[] = $signature['warning'];
            }

            $coreVersion = (string) config('songchart.core_version', '1.0.0');

            if (! $manifest->supportsCore($coreVersion)) {
                $errors[] = "Core {$coreVersion} does not satisfy {$manifest->coreConstraint()}.";
            }

            if (! $manifest->supportsPhp(PHP_VERSION)) {
                $errors[] = 'PHP '.PHP_VERSION.' does not satisfy '.$manifest->phpConstraint().'.';
            }

            foreach ($manifest->phpExtensions() as $extension) {
                if (! extension_loaded($extension)) {
                    $errors[] = "Required PHP extension is missing: {$extension}.";
                }
            }

            if ($manifest->type === 'plugin') {
                $this->checkPluginDependencies($manifest, $errors, $warnings);
            }

            if ($manifest->serviceProvider() && ! $manifest->namespace()) {
                $errors[] = 'Plugin service_provider requires autoload.namespace.';
            }

            return new PreflightResult(
                $manifest,
                $checksum,
                $errors,
                $warnings,
                [
                    'archive_bytes' => filesize($archive),
                    'core_version' => $coreVersion,
                    'php_version' => PHP_VERSION,
                    'capabilities' => (array) ($manifest->data['capabilities'] ?? []),
                    'permissions' => (array) ($manifest->data['permissions'] ?? []),
                    'external_services' => (array) ($manifest->data['external_services'] ?? []),
                    'signature' => $signature,
                ],
            );
        } finally {
            (new Filesystem)->deleteDirectory($temporary);
        }
    }

    /**
     * @param  list<string>  $errors
     * @param  list<string>  $warnings
     */
    private function checkPluginDependencies(ExtensionManifest $manifest, array &$errors, array &$warnings): void
    {
        if (! Schema::hasTable('extensions')) {
            if ($manifest->pluginDependencies() !== []) {
                $warnings[] = 'Dependency database is unavailable until migrations run.';
            }

            return;
        }

        foreach ($manifest->pluginDependencies() as $slug => $constraint) {
            $dependency = Extension::query()->where('slug', $slug)->first();

            if (! $dependency) {
                $errors[] = "Missing required plugin: {$slug} {$constraint}.";

                continue;
            }

            if (! $dependency->active_version || ! Semver::satisfies($dependency->active_version, (string) $constraint)) {
                $errors[] = "Plugin {$slug} version does not satisfy {$constraint}.";
            }

            if (! $dependency->enabled) {
                $errors[] = "Required plugin is disabled: {$slug}.";
            }
        }

        foreach ($manifest->conflicts() as $slug => $constraint) {
            $conflict = Extension::query()->where('slug', $slug)->first();

            if ($conflict && $conflict->active_version && Semver::satisfies($conflict->active_version, (string) $constraint)) {
                $errors[] = "Conflicting plugin is installed: {$slug} {$conflict->active_version}.";
            }
        }
    }

    private function locatePackageRoot(string $temporary, string $manifest): string
    {
        if (is_file($temporary.'/'.$manifest)) {
            return $temporary;
        }

        $directories = array_values(array_filter(glob($temporary.'/*') ?: [], 'is_dir'));

        if (count($directories) === 1 && is_file($directories[0].'/'.$manifest)) {
            return $directories[0];
        }

        throw new RuntimeException("ZIP must contain {$manifest} at root or one top-level directory.");
    }
}
