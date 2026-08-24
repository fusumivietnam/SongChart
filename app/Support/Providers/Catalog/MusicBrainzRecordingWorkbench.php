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

final class MusicBrainzRecordingWorkbench
{
    public function __construct(
        private readonly ProviderCatalogAdapterRegistry $adapters,
        private readonly ProviderImportOrchestrator $imports,
    ) {}

    /** @return list<array{id:string,title:string,length_ms:int|null,disambiguation:string,artist_credit:string,isrcs:list<string>} > */
    public function search(string $query, int $limit = 10): array
    {
        $page = $this->adapter()->fetchPage(new ProviderImportContext(
            runId: 'admin-musicbrainz-recording-search',
            entityType: EntityType::Recording,
            query: trim($query),
            pageSize: max(1, min(25, $limit)),
        ));

        return array_map(static function ($payload): array {
            $credits = is_array($payload->data['artist-credit'] ?? null) ? $payload->data['artist-credit'] : [];
            $credit = '';
            foreach ($credits as $item) {
                if (! is_array($item)) {
                    continue;
                }
                $artist = is_array($item['artist'] ?? null) ? $item['artist'] : [];
                $credit .= (string) ($item['name'] ?? $artist['name'] ?? '');
                $credit .= (string) ($item['joinphrase'] ?? '');
            }

            $isrcs = is_array($payload->data['isrcs'] ?? null)
                ? array_values(array_filter($payload->data['isrcs'], 'is_string'))
                : [];

            return [
                'id' => $payload->externalId,
                'title' => (string) ($payload->data['title'] ?? ''),
                'length_ms' => isset($payload->data['length']) ? (int) $payload->data['length'] : null,
                'disambiguation' => (string) ($payload->data['disambiguation'] ?? ''),
                'artist_credit' => trim($credit),
                'isrcs' => $isrcs,
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
            EntityType::Recording,
            'recording-lookup',
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
