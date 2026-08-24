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

final class MusicBrainzReleaseWorkbench
{
    public function __construct(
        private readonly ProviderCatalogAdapterRegistry $adapters,
        private readonly ProviderImportOrchestrator $imports,
    ) {}

    /** @return list<array{id:string,title:string,primary_type:string,first_release_date:string,disambiguation:string}> */
    public function searchReleaseGroups(string $query, int $limit = 10): array
    {
        $page = $this->adapter()->fetchPage(new ProviderImportContext(
            runId: 'admin-musicbrainz-release-group-search',
            entityType: EntityType::ReleaseGroup,
            query: trim($query),
            pageSize: max(1, min(25, $limit)),
        ));

        return array_map(static fn ($payload): array => [
            'id' => $payload->externalId,
            'title' => (string) ($payload->data['title'] ?? ''),
            'primary_type' => (string) ($payload->data['primary-type'] ?? ''),
            'first_release_date' => (string) ($payload->data['first-release-date'] ?? ''),
            'disambiguation' => (string) ($payload->data['disambiguation'] ?? ''),
        ], $page->items);
    }

    /** @return list<array{id:string,title:string,status:string,date:string,country:string,barcode:string,release_group_id:string,release_group_title:string}> */
    public function searchReleases(string $query, int $limit = 10): array
    {
        $page = $this->adapter()->fetchPage(new ProviderImportContext(
            runId: 'admin-musicbrainz-release-search',
            entityType: EntityType::Release,
            query: trim($query),
            pageSize: max(1, min(25, $limit)),
        ));

        return array_map(static function ($payload): array {
            $group = is_array($payload->data['release-group'] ?? null) ? $payload->data['release-group'] : [];

            return [
                'id' => $payload->externalId,
                'title' => (string) ($payload->data['title'] ?? ''),
                'status' => (string) ($payload->data['status'] ?? ''),
                'date' => (string) ($payload->data['date'] ?? ''),
                'country' => (string) ($payload->data['country'] ?? ''),
                'barcode' => (string) ($payload->data['barcode'] ?? ''),
                'release_group_id' => (string) ($group['id'] ?? ''),
                'release_group_title' => (string) ($group['title'] ?? ''),
            ];
        }, $page->items);
    }

    public function importReleaseGroupForProvider(string $providerId, string $mbid): ProviderImportRun
    {
        return $this->queueImport($providerId, EntityType::ReleaseGroup, $mbid, 'release-group-lookup');
    }

    public function importReleaseForProvider(string $providerId, string $mbid): ProviderImportRun
    {
        return $this->queueImport($providerId, EntityType::Release, $mbid, 'release-lookup');
    }

    private function adapter(): ProviderCatalogAdapter
    {
        $adapter = $this->adapters->for('musicbrainz');
        if ($adapter === null) {
            throw new RuntimeException('MusicBrainz catalog adapter is not registered.');
        }

        return $adapter;
    }

    private function queueImport(string $providerId, EntityType $entityType, string $mbid, string $operation): ProviderImportRun
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
            $entityType,
            $operation,
            externalId: strtolower($mbid),
            pageSize: 1,
            options: ['source' => 'admin-provider-workbench'],
        );
    }
}
