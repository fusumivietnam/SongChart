<?php

declare(strict_types=1);

namespace App\Support\Providers\Catalog;

use App\Contracts\Providers\Catalog\ProviderCatalogAdapter;
use App\Contracts\Providers\Catalog\ProviderCatalogAdapterRegistry;
use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Providers\Catalog\DTO\ProviderImportContext;
use App\Models\Provider;
use App\Models\Providers\Ingestion\ProviderImportRun;
use App\Support\Providers\Ingestion\ProviderImportOrchestrator;
use RuntimeException;

final class MusicBrainzWorkWorkbench
{
    public function __construct(
        private readonly ProviderCatalogAdapterRegistry $adapters,
        private readonly ProviderImportOrchestrator $imports,
    ) {}

    /** @return list<array{id:string,title:string,type:string,language:string,iswcs:list<string>,disambiguation:string}> */
    public function search(string $query, int $limit = 10): array
    {
        $page = $this->adapter()->fetchPage(new ProviderImportContext(
            runId: 'admin-musicbrainz-work-search',
            entityType: EntityType::Work,
            query: trim($query),
            pageSize: max(1, min(25, $limit)),
        ));

        return array_map(static function ($payload): array {
            $languages = is_array($payload->data['languages'] ?? null) ? array_values(array_filter($payload->data['languages'], 'is_string')) : [];
            $iswcs = is_array($payload->data['iswcs'] ?? null) ? array_values(array_filter($payload->data['iswcs'], 'is_string')) : [];

            return [
                'id' => $payload->externalId,
                'title' => (string) ($payload->data['title'] ?? ''),
                'type' => (string) ($payload->data['type'] ?? ''),
                'language' => (string) ($languages[0] ?? ''),
                'iswcs' => $iswcs,
                'disambiguation' => (string) ($payload->data['disambiguation'] ?? ''),
            ];
        }, $page->items);
    }

    public function importForProvider(string $providerId, string $mbid): ProviderImportRun
    {
        $provider = Provider::query()->find($providerId);
        if ($provider === null || $provider->slug !== 'musicbrainz') {
            throw new RuntimeException('MusicBrainz provider registry row was not found for this Admin route.');
        }
        if (! $provider->is_enabled) {
            throw new RuntimeException('MusicBrainz is disabled in the provider registry. Enable it before importing.');
        }

        return $this->imports->start(
            $provider,
            EntityType::Work,
            'work-lookup',
            externalId: strtolower($mbid),
            pageSize: 1,
            options: ['source' => 'admin-provider-workbench'],
        );
    }

    private function adapter(): ProviderCatalogAdapter
    {
        $adapter = $this->adapters->for('musicbrainz');
        if ($adapter === null) {
            throw new RuntimeException('MusicBrainz catalog adapter is not registered.');
        }

        return $adapter;
    }
}
