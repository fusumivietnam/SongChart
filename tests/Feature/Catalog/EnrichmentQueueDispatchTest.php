<?php

declare(strict_types=1);

use App\Application\Catalog\Enrichment\BuildEnrichmentSchedule;
use App\Application\Catalog\Enrichment\DispatchEnrichmentAttempts;
use App\Application\Catalog\Enrichment\PersistEnrichmentSchedule;
use App\Contracts\Catalog\EnrichmentAttemptStore;
use App\Contracts\Catalog\EnrichmentEvidenceAdmissionPolicy;
use App\Contracts\Catalog\EnrichmentExecutor;
use App\Contracts\Providers\Catalog\ProviderCatalogAdapter;
use App\Contracts\Providers\Catalog\ProviderCatalogAdapterRegistry;
use App\Contracts\Providers\Rate\ProviderRatePolicyRegistry;
use App\Contracts\Providers\Rate\ProviderRequestGate;
use App\Domain\Catalog\Enrichment\EnrichmentExecutionResult;
use App\Domain\Catalog\Enrichment\EnrichmentNeed;
use App\Domain\Catalog\Enrichment\EnrichmentPlan;
use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Providers\Catalog\DTO\NormalizedProviderEntity;
use App\Domain\Providers\Catalog\DTO\ProviderImportContext;
use App\Domain\Providers\Catalog\DTO\ProviderPage;
use App\Domain\Providers\Catalog\DTO\ProviderPayload;
use App\Domain\Providers\Catalog\Enums\ProviderCatalogCapability;
use App\Domain\Providers\Normalization\DTO\NormalizedArtist;
use App\Domain\Providers\Normalization\DTO\NormalizedIdentifier;
use App\Domain\Providers\Normalization\ValueObjects\ProviderField;
use App\Jobs\Catalog\Enrichment\ExecuteEnrichmentAttempt;
use App\Jobs\Catalog\Enrichment\GateEnrichmentAttempt;
use App\Models\Catalog\Artist;
use App\Models\Catalog\EnrichmentAttempt;
use App\Models\Catalog\ExternalIdentifier;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    config()->set('cache.default', 'array');
    config()->set('songchart.providers.musicbrainz.rate', [
        'strategy' => 'minimum_interval',
        'minimum_interval_ms' => 0,
        'default_cooldown_seconds' => 2,
        'maximum_cooldown_seconds' => 30,
        'lock_wait_seconds' => 2,
    ]);
    Cache::clear();
});

it('queues reserved enrichment attempts exactly once per supplied attempt id', function (): void {
    Queue::fake();

    $plan = new EnrichmentPlan([
        new EnrichmentNeed('identity', 'external_identifier', 'musicbrainz', 'high', 'low', 'Missing external identity.', true),
    ], 50);

    $schedule = app(BuildEnrichmentSchedule::class)->handle(EntityType::Artist, 'artist-queue-1', $plan);
    $ids = app(PersistEnrichmentSchedule::class)->handle($schedule);

    app(DispatchEnrichmentAttempts::class)->handle([$ids[0], $ids[0]]);

    Queue::assertPushed(GateEnrichmentAttempt::class, 1);
    expect(is_subclass_of(GateEnrichmentAttempt::class, ShouldQueue::class))->toBeTrue()
        ->and(EnrichmentAttempt::query()->findOrFail($ids[0])->status)->toBe('queued');
});

it('passes a queued attempt through the existing provider request gate before provider execution', function (): void {
    Queue::fake();
    $plan = new EnrichmentPlan([
        new EnrichmentNeed('identity', 'external_identifier', 'musicbrainz', 'high', 'low', 'Missing external identity.', true),
    ], 50);

    $schedule = app(BuildEnrichmentSchedule::class)->handle(EntityType::Artist, 'artist-gate-1', $plan);
    $id = app(PersistEnrichmentSchedule::class)->handle($schedule)[0];
    app(EnrichmentAttemptStore::class)->markQueued($id);

    $job = new GateEnrichmentAttempt($id);
    $job->handle(
        app(EnrichmentAttemptStore::class),
        app(ProviderRatePolicyRegistry::class),
        app(ProviderRequestGate::class),
    );

    $attempt = EnrichmentAttempt::query()->findOrFail($id);
    Queue::assertPushed(ExecuteEnrichmentAttempt::class, 1);
    expect($attempt->status)->toBe('ready')
        ->and($attempt->attempt_count)->toBe(1)
        ->and($attempt->last_error)->toBeNull();
});

it('uses a safe review-required outcome when no governed provider executor exists yet', function (): void {
    $plan = new EnrichmentPlan([
        new EnrichmentNeed('identity', 'external_identifier', 'spotify', 'high', 'low', 'Missing external identity.', true),
    ], 50);

    $schedule = app(BuildEnrichmentSchedule::class)->handle(EntityType::Artist, 'artist-review-1', $plan);
    $id = app(PersistEnrichmentSchedule::class)->handle($schedule)[0];
    $store = app(EnrichmentAttemptStore::class);
    $store->markQueued($id);
    $store->beginGate($id);
    $store->markReady($id);

    (new ExecuteEnrichmentAttempt($id))->handle($store, app(EnrichmentExecutor::class), app(EnrichmentEvidenceAdmissionPolicy::class));

    $attempt = EnrichmentAttempt::query()->findOrFail($id);
    expect($attempt->status)->toBe('review_required')
        ->and($attempt->review_reason)->toContain('No governed enrichment executor is registered')
        ->and($attempt->completed_at)->not->toBeNull();
});

it('routes retryable provider execution back through the rate gate without canonical mutation', function (): void {
    Queue::fake();

    app()->instance(EnrichmentExecutor::class, new class implements EnrichmentExecutor
    {
        public function execute(array $attempt): EnrichmentExecutionResult
        {
            return EnrichmentExecutionResult::retryable('Provider temporarily unavailable.', 7);
        }
    });

    $plan = new EnrichmentPlan([
        new EnrichmentNeed('identity', 'external_identifier', 'musicbrainz', 'high', 'low', 'Missing external identity.', true),
    ], 50);

    $schedule = app(BuildEnrichmentSchedule::class)->handle(EntityType::Artist, 'artist-retry-1', $plan);
    $id = app(PersistEnrichmentSchedule::class)->handle($schedule)[0];
    $store = app(EnrichmentAttemptStore::class);
    $store->markQueued($id);
    $store->beginGate($id);
    $store->markReady($id);

    (new ExecuteEnrichmentAttempt($id))->handle($store, app(EnrichmentExecutor::class), app(EnrichmentEvidenceAdmissionPolicy::class));

    Queue::assertPushed(GateEnrichmentAttempt::class, 1);
    $attempt = EnrichmentAttempt::query()->findOrFail($id);
    expect($attempt->status)->toBe('deferred')
        ->and($attempt->last_error)->toBe('Provider temporarily unavailable.')
        ->and($attempt->completed_at)->toBeNull();
});

it('persists successful provider execution evidence without mutating canonical entities', function (): void {
    app()->instance(EnrichmentExecutor::class, new class implements EnrichmentExecutor
    {
        public function execute(array $attempt): EnrichmentExecutionResult
        {
            return EnrichmentExecutionResult::succeeded([
                'provider' => 'musicbrainz',
                'need' => ['kind' => 'identity', 'key' => 'external_identifier'],
                'candidates' => [[
                    'provider_slug' => 'musicbrainz',
                    'entity_type' => EntityType::Artist->value,
                    'external_id' => 'mbid-123',
                    'fields' => [],
                    'identifiers' => [],
                    'relationships' => [],
                    'normalizer_version' => '1',
                    'validation' => ['valid' => true, 'issues' => []],
                ]],
            ]);
        }
    });

    $plan = new EnrichmentPlan([
        new EnrichmentNeed('identity', 'external_identifier', 'musicbrainz', 'high', 'low', 'Missing external identity.', true),
    ], 50);

    $schedule = app(BuildEnrichmentSchedule::class)->handle(EntityType::Artist, 'artist-success-1', $plan);
    $id = app(PersistEnrichmentSchedule::class)->handle($schedule)[0];
    $store = app(EnrichmentAttemptStore::class);
    $store->markQueued($id);
    $store->beginGate($id);
    $store->markReady($id);

    (new ExecuteEnrichmentAttempt($id))->handle($store, app(EnrichmentExecutor::class), app(EnrichmentEvidenceAdmissionPolicy::class));

    $attempt = EnrichmentAttempt::query()->findOrFail($id);
    expect($attempt->status)->toBe('succeeded')
        ->and($attempt->result_payload['evidence_admission']['decision'])->toBe('admissible')
        ->and($attempt->result_payload['evidence_admission']['canonical_mutation'])->toBeFalse()
        ->and($attempt->result_payload['candidates'][0]['external_id'])->toBe('mbid-123')
        ->and($attempt->completed_at)->not->toBeNull();
});

it('executes MusicBrainz enrichment through the existing catalog adapter and stores normalized evidence', function (): void {
    config()->set('songchart.providers.musicbrainz.enabled', true);
    config()->set('songchart.providers.musicbrainz.enrichment.daily_execution_budget', 10);

    $artist = Artist::factory()->create(['name' => 'Radiohead']);
    $payload = new ProviderPayload('musicbrainz', EntityType::Artist, 'mbid-radiohead', ['name' => 'Radiohead'], new DateTimeImmutable);
    $normalized = new NormalizedProviderEntity(
        'musicbrainz',
        EntityType::Artist,
        'mbid-radiohead',
        new NormalizedArtist(
            ProviderField::provided('Radiohead'),
            ProviderField::provided('Radiohead'),
            ProviderField::missing(),
            ProviderField::provided('GB'),
            ProviderField::missing(),
            ProviderField::missing(),
            ProviderField::provided(false),
        ),
        [new NormalizedIdentifier('musicbrainz_artist', 'mbid-radiohead')],
    );

    $adapter = new class($payload, $normalized) implements ProviderCatalogAdapter
    {
        public function __construct(private ProviderPayload $payload, private NormalizedProviderEntity $normalized) {}

        public function providerSlug(): string
        {
            return 'musicbrainz';
        }

        public function capabilities(): array
        {
            return [ProviderCatalogCapability::ArtistLookup, ProviderCatalogCapability::Search];
        }

        public function fetchPage(ProviderImportContext $context, ?string $cursor = null): ProviderPage
        {
            expect($context->entityType)->toBe(EntityType::Artist)->and($context->query)->toBe('Radiohead');

            return new ProviderPage([$this->payload], complete: true);
        }

        public function normalize(ProviderPayload $payload): NormalizedProviderEntity
        {
            return $this->normalized;
        }
    };
    app()->instance(ProviderCatalogAdapterRegistry::class, new class($adapter) implements ProviderCatalogAdapterRegistry
    {
        public function __construct(private ProviderCatalogAdapter $adapter) {}

        public function for(string $providerSlug): ?ProviderCatalogAdapter
        {
            return $providerSlug === 'musicbrainz' ? $this->adapter : null;
        }

        public function slugs(): array
        {
            return ['musicbrainz'];
        }
    });
    app()->forgetInstance(EnrichmentExecutor::class);

    $plan = new EnrichmentPlan([
        new EnrichmentNeed('field', 'country_code', 'musicbrainz', 'normal', 'low', 'Missing country.', true),
    ], 50);
    $id = app(PersistEnrichmentSchedule::class)->handle(app(BuildEnrichmentSchedule::class)->handle(EntityType::Artist, (string) $artist->getKey(), $plan))[0];
    $store = app(EnrichmentAttemptStore::class);
    $store->markQueued($id);
    $store->beginGate($id);
    $store->markReady($id);

    (new ExecuteEnrichmentAttempt($id))->handle($store, app(EnrichmentExecutor::class), app(EnrichmentEvidenceAdmissionPolicy::class));

    $attempt = EnrichmentAttempt::query()->findOrFail($id);
    expect($attempt->status)->toBe('succeeded')
        ->and($attempt->result_payload['provider'])->toBe('musicbrainz')
        ->and($attempt->result_payload['candidates'][0]['external_id'])->toBe('mbid-radiohead')
        ->and($attempt->result_payload['candidates'][0]['normalizer_version'])->toBe('1')
        ->and($attempt->result_payload['candidates'][0]['validation']['valid'])->toBeTrue()
        ->and($attempt->result_payload['evidence_admission']['decision'])->toBe('admissible')
        ->and($attempt->result_payload['evidence_admission']['canonical_mutation'])->toBeFalse();
});

it('short-circuits fresh MusicBrainz identity evidence before consuming provider execution budget', function (): void {
    config()->set('songchart.providers.musicbrainz.enabled', true);
    config()->set('songchart.providers.musicbrainz.enrichment.daily_execution_budget', 1);
    config()->set('songchart.providers.musicbrainz.enrichment.fresh_for_days', 30);

    $artist = Artist::factory()->create(['name' => 'Radiohead']);
    ExternalIdentifier::query()->create([
        'entity_type' => EntityType::Artist->value,
        'entity_id' => $artist->getKey(),
        'namespace' => 'musicbrainz_artist',
        'value' => 'mbid-fresh',
        'is_primary' => true,
        'verification_state' => 'verified',
    ]);

    $plan = new EnrichmentPlan([
        new EnrichmentNeed('identity', 'external_identifier', 'musicbrainz', 'high', 'low', 'Refresh identity.', true),
    ], 50);
    $id = app(PersistEnrichmentSchedule::class)->handle(app(BuildEnrichmentSchedule::class)->handle(EntityType::Artist, (string) $artist->getKey(), $plan))[0];
    $store = app(EnrichmentAttemptStore::class);
    $store->markQueued($id);
    $store->beginGate($id);
    $store->markReady($id);

    (new ExecuteEnrichmentAttempt($id))->handle($store, app(EnrichmentExecutor::class), app(EnrichmentEvidenceAdmissionPolicy::class));

    $attempt = EnrichmentAttempt::query()->findOrFail($id);
    expect($attempt->status)->toBe('succeeded')
        ->and($attempt->result_payload['admission'])->toBe('fresh-existing-identity')
        ->and($attempt->result_payload['identifier']['value'])->toBe('mbid-fresh')
        ->and(Cache::get('songchart:enrichment:musicbrainz:budget:'.now('UTC')->format('Y-m-d')))->toBeNull();
});

it('defers MusicBrainz execution when the configured daily enrichment budget is exhausted', function (): void {
    config()->set('songchart.providers.musicbrainz.enabled', true);
    config()->set('songchart.providers.musicbrainz.enrichment.daily_execution_budget', 1);

    $artist = Artist::factory()->create(['name' => 'Radiohead']);
    $budgetKey = 'songchart:enrichment:musicbrainz:budget:'.now('UTC')->format('Y-m-d');
    Cache::put($budgetKey, 1, now('UTC')->addDays(2));

    $result = app(EnrichmentExecutor::class)->execute([
        'id' => 'budget-test',
        'entity_type' => EntityType::Artist->value,
        'entity_id' => (string) $artist->getKey(),
        'provider' => 'musicbrainz',
        'need_kind' => 'field',
        'need_key' => 'country_code',
        'reason' => 'Refresh country evidence.',
    ]);

    expect($result->outcome)->toBe('retryable')
        ->and($result->message)->toContain('daily execution budget is exhausted')
        ->and($result->retryAfterSeconds)->toBeGreaterThanOrEqual(60);
});
