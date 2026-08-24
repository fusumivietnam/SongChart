<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Extension;
use App\Models\ExtensionRelease;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(ProviderRegistrySeeder::class);
        $this->call(CatalogFixtureSeeder::class);

        $theme = Extension::query()->firstOrCreate(
            ['slug' => 'songchart/default'],
            ['type' => 'theme', 'name' => 'SongChart Default', 'state' => 'enabled', 'active_version' => '1.0.0', 'active_path' => 'themes/songchart/default/releases/1.0.0', 'enabled' => true, 'manifest' => json_decode(file_get_contents(base_path('themes/songchart/default/releases/1.0.0/theme.json')), true), 'installed_at' => now()],
        );
        ExtensionRelease::query()->firstOrCreate(
            ['extension_id' => $theme->id, 'version' => '1.0.0'],
            ['path' => 'themes/songchart/default/releases/1.0.0', 'checksum_sha256' => hash_file('sha256', base_path('themes/songchart/default/releases/1.0.0/theme.json')), 'signature_status' => 'unsigned', 'status' => 'active', 'manifest' => $theme->manifest, 'installed_at' => now()],
        );

    }
}
