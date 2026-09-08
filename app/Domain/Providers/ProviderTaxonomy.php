<?php

declare(strict_types=1);

namespace App\Domain\Providers;

use App\Domain\Providers\Enums\ProviderCapabilityCode;
use App\Domain\Providers\Enums\ProviderCategory;
use App\Domain\Providers\Enums\ProviderRole;

final class ProviderTaxonomy
{
    /**
     * @return array<string, array{
     *     name: string,
     *     category: ProviderCategory,
     *     role: ProviderRole,
     *     capabilities: list<ProviderCapabilityCode>
     * }>
     */
    public static function definitions(): array
    {
        return [
            'musicbrainz' => [
                'name' => 'MusicBrainz',
                'category' => ProviderCategory::Music,
                'role' => ProviderRole::Data,
                'capabilities' => [
                    ProviderCapabilityCode::CatalogSearch,
                    ProviderCapabilityCode::CatalogLookup,
                    ProviderCapabilityCode::CatalogImport,
                    ProviderCapabilityCode::CatalogRelationships,
                ],
            ],
            'cover-art-archive' => [
                'name' => 'Cover Art Archive',
                'category' => ProviderCategory::Music,
                'role' => ProviderRole::Data,
                'capabilities' => [ProviderCapabilityCode::CatalogArtwork],
            ],
            'wikidata' => [
                'name' => 'Wikidata',
                'category' => ProviderCategory::Music,
                'role' => ProviderRole::Data,
                'capabilities' => [
                    ProviderCapabilityCode::CatalogLookup,
                    ProviderCapabilityCode::CatalogEnrichment,
                ],
            ],
            'youtube' => [
                'name' => 'YouTube',
                'category' => ProviderCategory::Music,
                'role' => ProviderRole::Destination,
                'capabilities' => [
                    ProviderCapabilityCode::MediaSearch,
                    ProviderCapabilityCode::MediaInspect,
                    ProviderCapabilityCode::MediaStatistics,
                    ProviderCapabilityCode::MediaEmbed,
                    ProviderCapabilityCode::MediaOutbound,
                ],
            ],
            'posthog' => [
                'name' => 'PostHog',
                'category' => ProviderCategory::Analytics,
                'role' => ProviderRole::Service,
                'capabilities' => [
                    ProviderCapabilityCode::AnalyticsEvents,
                    ProviderCapabilityCode::AnalyticsPageviews,
                ],
            ],
            'sentry' => [
                'name' => 'Sentry',
                'category' => ProviderCategory::Observability,
                'role' => ProviderRole::Service,
                'capabilities' => [
                    ProviderCapabilityCode::ObservabilityExceptions,
                    ProviderCapabilityCode::ObservabilityPerformance,
                ],
            ],
            'cloudflare-turnstile' => [
                'name' => 'Cloudflare Turnstile',
                'category' => ProviderCategory::Security,
                'role' => ProviderRole::Service,
                'capabilities' => [ProviderCapabilityCode::SecurityBotProtection],
            ],
            'resend' => [
                'name' => 'Resend',
                'category' => ProviderCategory::Email,
                'role' => ProviderRole::Service,
                'capabilities' => [ProviderCapabilityCode::NotificationEmail],
            ],
        ];
    }

    /**
     * @return array{
     *     name: string,
     *     category: ProviderCategory,
     *     role: ProviderRole,
     *     capabilities: list<ProviderCapabilityCode>
     * }|null
     */
    public static function definition(string $slug): ?array
    {
        return self::definitions()[$slug] ?? null;
    }
}
