<?php

declare(strict_types=1);

namespace App\Support\Catalog\Enrichment;

use App\Contracts\Catalog\EnrichmentExecutor;
use App\Contracts\Providers\Catalog\ProviderCatalogAdapterRegistry;
use App\Domain\Catalog\Enrichment\EnrichmentExecutionResult;
use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Providers\Catalog\DTO\ProviderImportContext;
use App\Domain\Providers\Catalog\Exceptions\ProviderRequestException;
use App\Domain\Providers\Normalization\Validation\Contracts\NormalizedProviderEntityValidator;
use App\Models\Catalog\ExternalIdentifier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Throwable;

final class GovernedEnrichmentExecutor implements EnrichmentExecutor
{
    public function __construct(
        private readonly ProviderCatalogAdapterRegistry $adapters,
        private readonly NormalizedProviderEntityValidator $validator,
    ) {}

    public function execute(array $attempt): EnrichmentExecutionResult
    {
        if ($attempt['provider'] !== 'musicbrainz') {
            return $this->unsupported($attempt);
        }

        if (! (bool) config('songchart.providers.musicbrainz.enabled', false)) {
            return EnrichmentExecutionResult::reviewRequired('MusicBrainz enrichment is disabled by runtime configuration.');
        }

        $entityType = EntityType::tryFrom($attempt['entity_type']);
        if ($entityType === null || ! in_array($entityType, [EntityType::Artist, EntityType::ReleaseGroup, EntityType::Release, EntityType::Recording, EntityType::Work], true)) {
            return EnrichmentExecutionResult::reviewRequired('MusicBrainz enrichment does not support entity type ['.$attempt['entity_type'].'].');
        }

        $entity = $entityType->modelClass()::query()->find($attempt['entity_id']);
        if (! $entity instanceof Model) {
            return EnrichmentExecutionResult::failed('Canonical entity no longer exists for enrichment attempt.');
        }

        if ($this->isFreshIdentityNeed($attempt)) {
            $fresh = $this->musicBrainzIdentifier($entityType, $attempt['entity_id'], true);
            if ($fresh instanceof ExternalIdentifier) {
                return EnrichmentExecutionResult::succeeded([
                    'provider' => 'musicbrainz',
                    'admission' => 'fresh-existing-identity',
                    'identifier' => ['namespace' => $fresh->namespace, 'value' => $fresh->value],
                ]);
            }
        }

        $budget = $this->consumeDailyBudget();
        if ($budget !== null) {
            return $budget;
        }

        $adapter = $this->adapters->for('musicbrainz');
        if ($adapter === null) {
            return EnrichmentExecutionResult::reviewRequired('MusicBrainz catalog adapter is not registered.');
        }

        $knownIdentifier = $this->musicBrainzIdentifier($entityType, $attempt['entity_id']);
        $context = new ProviderImportContext(
            runId: 'enrichment-'.$attempt['id'],
            entityType: $entityType,
            externalId: $knownIdentifier?->value,
            query: $knownIdentifier === null ? $this->displayValue($entityType, $entity) : null,
            pageSize: max(1, min(10, (int) config('songchart.providers.musicbrainz.enrichment.candidate_limit', 5))),
            options: ['purpose' => 'enrichment', 'need_kind' => $attempt['need_kind'], 'need_key' => $attempt['need_key']],
        );

        try {
            $page = $adapter->fetchPage($context);
            $candidates = array_map(function ($payload) use ($adapter): array {
                $normalized = $adapter->normalize($payload);
                $candidate = $normalized->toArray();
                $validation = $this->validator->validate($normalized);
                $candidate['validation'] = [
                    'valid' => $validation->isValid(),
                    'issues' => $validation->toArray(),
                ];

                return $candidate;
            }, $page->items);
        } catch (ProviderRequestException $exception) {
            return $exception->retryable
                ? EnrichmentExecutionResult::retryable($exception->getMessage(), $exception->retryAfterSeconds ?? 5)
                : EnrichmentExecutionResult::failed($exception->getMessage());
        } catch (Throwable $exception) {
            return EnrichmentExecutionResult::failed($exception->getMessage());
        }

        $evidence = [
            'provider' => 'musicbrainz',
            'need' => ['kind' => $attempt['need_kind'], 'key' => $attempt['need_key']],
            'lookup' => $knownIdentifier === null ? 'search' : 'external-id',
            'candidates' => $candidates,
        ];

        return EnrichmentExecutionResult::succeeded($evidence);
    }

    /** @param array{id:string,entity_type:string,entity_id:string,provider:string,need_kind:string,need_key:string,reason:string} $attempt */
    private function unsupported(array $attempt): EnrichmentExecutionResult
    {
        return EnrichmentExecutionResult::reviewRequired(sprintf(
            'No governed enrichment executor is registered for provider [%s] need [%s:%s].',
            $attempt['provider'],
            $attempt['need_kind'],
            $attempt['need_key'],
        ));
    }

    /** @param array{id:string,entity_type:string,entity_id:string,provider:string,need_kind:string,need_key:string,reason:string} $attempt */
    private function isFreshIdentityNeed(array $attempt): bool
    {
        return $attempt['need_kind'] === 'identity' || $attempt['need_kind'] === 'identifier';
    }

    private function musicBrainzIdentifier(EntityType $entityType, string $entityId, bool $freshOnly = false): ?ExternalIdentifier
    {
        $namespace = match ($entityType) {
            EntityType::Artist => 'musicbrainz_artist',
            EntityType::ReleaseGroup => 'musicbrainz_release_group',
            EntityType::Release => 'musicbrainz_release',
            EntityType::Recording => 'musicbrainz_recording',
            EntityType::Work => 'musicbrainz_work',
            default => null,
        };

        if ($namespace === null) {
            return null;
        }

        $query = ExternalIdentifier::query()
            ->where('entity_type', $entityType->value)
            ->where('entity_id', $entityId)
            ->where('namespace', $namespace)
            ->orderByDesc('is_primary')
            ->latest('updated_at');

        if ($freshOnly) {
            $days = max(1, (int) config('songchart.providers.musicbrainz.enrichment.fresh_for_days', 30));
            $query->where('updated_at', '>=', now()->subDays($days));
        }

        return $query->first();
    }

    private function displayValue(EntityType $type, Model $entity): string
    {
        return trim((string) $entity->getAttribute($type === EntityType::Artist ? 'name' : 'title'));
    }

    private function consumeDailyBudget(): ?EnrichmentExecutionResult
    {
        $limit = max(0, (int) config('songchart.providers.musicbrainz.enrichment.daily_execution_budget', 500));
        if ($limit === 0) {
            return null;
        }

        $key = 'songchart:enrichment:musicbrainz:budget:'.now('UTC')->format('Y-m-d');
        if (! Cache::add($key, 0, now('UTC')->addDays(2))) {
            // Existing counter is expected.
        }

        $used = (int) Cache::increment($key);
        if ($used <= $limit) {
            return null;
        }

        $retryAfter = max(60, (int) now('UTC')->diffInSeconds(now('UTC')->addDay()->startOfDay()));

        return EnrichmentExecutionResult::retryable('MusicBrainz enrichment daily execution budget is exhausted.', $retryAfter);
    }
}
