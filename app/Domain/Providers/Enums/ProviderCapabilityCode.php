<?php

declare(strict_types=1);

namespace App\Domain\Providers\Enums;

enum ProviderCapabilityCode: string
{
    case CatalogSearch = 'catalog.search';
    case CatalogLookup = 'catalog.lookup';
    case CatalogImport = 'catalog.import';
    case CatalogRelationships = 'catalog.relationships';
    case CatalogArtwork = 'catalog.artwork';
    case CatalogEnrichment = 'catalog.enrichment';
    case MediaSearch = 'media.search';
    case MediaInspect = 'media.inspect';
    case MediaEmbed = 'media.embed';
    case MediaOutbound = 'media.outbound';
    case AnalyticsEvents = 'analytics.events';
    case AnalyticsPageviews = 'analytics.pageviews';
    case ObservabilityExceptions = 'observability.exceptions';
    case ObservabilityPerformance = 'observability.performance';
    case SecurityBotProtection = 'security.bot_protection';
    case NotificationEmail = 'notification.email';
    case AiChat = 'ai.chat';
    case AiEmbedding = 'ai.embedding';
    case AiClassification = 'ai.classification';
    case AiEnrichment = 'ai.enrichment';
}
