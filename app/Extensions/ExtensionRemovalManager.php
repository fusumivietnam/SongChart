<?php

declare(strict_types=1);

namespace App\Extensions;

use App\Domain\Extensions\Enums\ExtensionState;
use App\Models\Extension;
use App\Models\ExtensionOperation;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

final class ExtensionRemovalManager
{
    public function __construct(private Filesystem $files, private PluginRegistry $plugins, private ThemeRegistry $themes) {}

    public function removeCode(string $slug): void
    {
        $extension = Extension::query()->where('slug', $slug)->firstOrFail();
        if ($extension->enabled) {
            throw new RuntimeException('Disable the extension before removing code.');
        }
        $base = base_path(($extension->type === 'plugin' ? 'plugins/' : 'themes/').$slug);
        if (is_dir($base)) {
            $this->files->deleteDirectory($base);
        }
        $extension->update(['state' => ExtensionState::Disabled, 'active_path' => null, 'active_version' => null]);
        $this->removeRegistry($extension);
        $this->audit($extension, 'remove_code', ['data_preserved' => true]);
    }

    public function uninstall(string $slug, bool $purge = false): void
    {
        $extension = Extension::query()->where('slug', $slug)->firstOrFail();
        if ($extension->enabled) {
            throw new RuntimeException('Disable the extension before uninstalling it.');
        }
        $manifestValue = $extension->getAttribute('manifest');
        $manifest = is_array($manifestValue) ? $manifestValue : [];
        $uninstall = is_array($manifest['uninstall'] ?? null) ? $manifest['uninstall'] : [];
        if ($purge) {
            foreach ($this->stringList($uninstall['owned_tables'] ?? []) as $table) {
                if (str_starts_with($table, 'plugin_') && Schema::hasTable($table)) {
                    Schema::drop($table);
                }
            }
            foreach ($this->stringList($uninstall['owned_files'] ?? []) as $path) {

                $absolute = base_path($path);
                if (str_starts_with(realpath(dirname($absolute)) ?: '', base_path()) && is_dir($absolute)) {
                    $this->files->deleteDirectory($absolute);
                }

            }
        }
        $this->removeCode($slug);
        $this->audit($extension, $purge ? 'purge' : 'uninstall', ['purged' => $purge]);
        if ($purge) {
            $extension->delete();
        }
    }

    private function removeRegistry(Extension $extension): void
    {
        if ($extension->type === 'plugin') {
            $registry = $this->plugins->all();
            unset($registry[$extension->slug]);
            $this->write(base_path('plugins/registry.php'), $registry);

            return;
        }
        $registry = $this->themes->all();
        unset($registry['installed'][$extension->slug]);
        if ($registry['active'] === $extension->slug) {
            $registry['active'] = 'songchart/default';
        }
        $this->write(base_path('themes/registry.php'), $registry);
    }

    /** @return list<string> */
    private function stringList(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_values(array_filter($value, is_string(...)));
    }

    /** @param array<mixed> $data */
    private function write(string $path, array $data): void
    {
        $temp = $path.'.tmp';
        file_put_contents($temp, "<?php\n\nreturn ".var_export($data, true).";\n", LOCK_EX);
        rename($temp, $path);
    }

    /** @param array<string,mixed> $details */
    private function audit(Extension $extension, string $type, array $details): void
    {
        ExtensionOperation::query()->create(['extension_id' => $extension->id, 'type' => $type, 'status' => 'succeeded', 'actor_id' => auth()->id(), 'details' => $details, 'started_at' => now(), 'finished_at' => now()]);
    }
}
