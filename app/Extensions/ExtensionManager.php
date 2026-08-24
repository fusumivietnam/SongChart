<?php

declare(strict_types=1);

namespace App\Extensions;

use App\Domain\Extensions\Enums\ExtensionState;
use App\Models\Extension;
use App\Models\ExtensionOperation;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

final class ExtensionManager
{
    public function __construct(private PluginRegistry $plugins, private ThemeRegistry $themes) {}

    public function setPluginEnabled(string $slug, bool $enabled): void
    {
        $registry = $this->plugins->all();
        if (! isset($registry[$slug])) {
            throw new RuntimeException("Plugin is not installed: {$slug}");
        }
        $entry = $registry[$slug];
        $entry['enabled'] = $enabled;
        $this->plugins->put($slug, $entry);
        if (Schema::hasTable('extensions')) {
            $e = Extension::query()->where('slug', $slug)->where('type', 'plugin')->firstOrFail();
            $e->update(['enabled' => $enabled, 'state' => $enabled ? ExtensionState::Enabled : ExtensionState::Disabled]);
            $this->audit($e, $enabled ? 'enable' : 'disable');
        }
    }

    public function activateTheme(string $slug): void
    {
        $this->themes->activate($slug);
        if (Schema::hasTable('extensions')) {
            Extension::query()->where('type', 'theme')->update(['enabled' => false, 'state' => ExtensionState::Disabled]);
            $e = Extension::query()->where('slug', $slug)->where('type', 'theme')->firstOrFail();
            $e->update(['enabled' => true, 'state' => ExtensionState::Enabled]);
            $this->audit($e, 'activate');
        }
    }

    private function audit(Extension $e, string $type): void
    {
        ExtensionOperation::query()->create(['extension_id' => $e->id, 'type' => $type, 'status' => 'succeeded', 'actor_id' => auth()->id(), 'details' => [], 'started_at' => now(), 'finished_at' => now()]);
    }
}
