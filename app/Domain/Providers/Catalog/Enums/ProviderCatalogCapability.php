<?php

declare(strict_types=1);

namespace App\Domain\Providers\Catalog\Enums;

enum ProviderCatalogCapability: string
{
    case ArtistLookup = 'artist-lookup';
    case WorkLookup = 'work-lookup';
    case RecordingLookup = 'recording-lookup';
    case ReleaseGroupLookup = 'release-group-lookup';
    case ReleaseLookup = 'release-lookup';
    case CollectionLookup = 'collection-lookup';
    case Search = 'search';
    case IncrementalSync = 'incremental-sync';
}
