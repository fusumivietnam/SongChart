<?php

declare(strict_types=1);

namespace App\Support\Providers\Catalog;

use App\Contracts\Providers\Catalog\ProviderCatalogAdapterRegistry;
use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Providers\Catalog\DTO\ProviderImportContext;
use App\Models\Provider;
use App\Models\Providers\Ingestion\ProviderImportRun;
use App\Support\Providers\Configuration\ProviderRuntimeConfiguration;
use App\Support\Providers\Ingestion\ProviderImportOrchestrator;
use RuntimeException;

final class MusicBrainzArtistWorkbench
{
    public function __construct(
        private readonly ProviderCatalogAdapterRegistry $adapters,
        private readonly ProviderImportOrchestrator $imports,
        private readonly ProviderRuntimeConfiguration $runtimeConfiguration,
    ) {}

    /** @return list<array{id:string,name:string,sort_name:string,country:string,type:string,disambiguation:string}> */
    public function search(string $query, int $limit = 10): array
    {
        $this->runtimeConfiguration->apply('musicbrainz');
        $adapter = $this->adapters->for('musicbrainz');
        if ($adapter === null) {
            throw new RuntimeException('MusicBrainz catalog adapter is not registered.');
        }

        $page = $adapter->fetchPage(new ProviderImportContext(
            runId: 'admin-musicbrainz-artist-search',
            entityType: EntityType::Artist,
            query: trim($query),
            pageSize: max(1, min(25, $limit)),
        ));

        return array_map(static fn ($payload): array => [
            'id' => $payload->externalId,
            'name' => (string) ($payload->data['name'] ?? ''),
            'sort_name' => (string) ($payload->data['sort-name'] ?? ''),
            'country' => (string) ($payload->data['country'] ?? ''),
            'type' => (string) ($payload->data['type'] ?? ''),
            'disambiguation' => (string) ($payload->data['disambiguation'] ?? ''),
        ], $page->items);
    }

    public function importForProvider(string $providerId, string $mbid, string $source = 'admin-provider-workbench'): ProviderImportRun
    {
        $provider = Provider::query()->find($providerId);
        if ($provider === null || $provider->slug !== 'musicbrainz') {
            throw new RuntimeException('MusicBrainz provider registry row was not found for this Admin route.');
        }

        return $this->queueImport($provider, $mbid, $source);
    }

    public function import(string $mbid, string $source = 'development-status'): ProviderImportRun
    {
        $provider = Provider::query()->where('slug', 'musicbrainz')->first();
        if ($provider === null) {
            throw new RuntimeException('MusicBrainz provider registry row is missing. Run the database seeder first.');
        }

        return $this->queueImport($provider, $mbid, $source);
    }

    private function queueImport(Provider $provider, string $mbid, string $source): ProviderImportRun
    {
        if (! $provider->is_enabled) {
            throw new RuntimeException('MusicBrainz is disabled in the provider registry. Enable it from Admin → Providers before importing.');
        }

        return $this->imports->start(
            $provider,
            EntityType::Artist,
            'artist-lookup',
            externalId: strtolower($mbid),
            pageSize: 1,
            options: ['source' => $source],
        );
    }
}
