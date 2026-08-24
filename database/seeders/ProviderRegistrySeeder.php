<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Provider;
use Illuminate\Database\Seeder;

final class ProviderRegistrySeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['musicbrainz', 'MusicBrainz', 'music', 'approved'],
            ['cover-art-archive', 'Cover Art Archive', 'music', 'approved'],
            ['wikidata', 'Wikidata', 'music', 'approved'],
            ['youtube', 'YouTube', 'music', 'approved'],
            ['posthog', 'PostHog', 'analytics', 'research'],
            ['sentry', 'Sentry', 'observability', 'research'],
            ['cloudflare-turnstile', 'Cloudflare Turnstile', 'security', 'research'],
            ['resend', 'Resend', 'email', 'research'],
        ] as [$slug, $name, $category, $status]) {
            Provider::query()->firstOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'category' => $category, 'status' => $status, 'is_enabled' => false, 'official_docs_url' => $slug === 'youtube' ? 'https://developers.google.com/youtube/v3' : null, 'policy_reviewed_at' => $slug === 'youtube' ? '2026-08-18' : '2026-08-02'],
            );
        }
    }
}
