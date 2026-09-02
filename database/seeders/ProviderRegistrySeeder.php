<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Providers\ProviderTaxonomy;
use App\Models\Provider;
use App\Models\ProviderCapability;
use Illuminate\Database\Seeder;

final class ProviderRegistrySeeder extends Seeder
{
    public function run(): void
    {
        foreach (ProviderTaxonomy::definitions() as $slug => $definition) {
            $status = in_array($slug, ['musicbrainz', 'cover-art-archive', 'wikidata', 'youtube'], true)
                ? 'approved'
                : 'research';

            $provider = Provider::query()->firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $definition['name'],
                    'category' => $definition['category']->value,
                    'status' => $status,
                    'is_enabled' => false,
                    'official_docs_url' => $slug === 'youtube' ? 'https://developers.google.com/youtube/v3' : null,
                    'policy_reviewed_at' => $slug === 'youtube' ? '2026-08-18' : '2026-08-02',
                ],
            );

            $provider->forceFill([
                'name' => $definition['name'],
                'category' => $definition['category']->value,
            ])->save();

            foreach ($definition['capabilities'] as $capability) {
                ProviderCapability::query()->firstOrCreate(
                    [
                        'provider_id' => $provider->getKey(),
                        'capability' => $capability->value,
                    ],
                    ['status' => 'supported'],
                );
            }
        }
    }
}
