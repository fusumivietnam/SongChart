<?php

declare(strict_types=1);

use App\Application\Catalog\Admission\MaterializeProviderAdmissionEvidence;
use App\Contracts\Catalog\EnrichmentAttemptStore;
use App\Contracts\Catalog\EnrichmentEvidenceAdmissionPolicy;
use App\Contracts\Catalog\EnrichmentExecutor;
use App\Domain\Catalog\Enrichment\EnrichmentExecutionResult;
use App\Domain\Catalog\Enums\CanonicalAdmissionStatus;
use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Catalog\Enums\VerificationState;
use App\Jobs\Catalog\Enrichment\ExecuteEnrichmentAttempt;
use App\Models\Catalog\Artist;
use App\Models\Catalog\CanonicalAdmissionDecision;
use App\Models\Catalog\EnrichmentAttempt;
use App\Models\Catalog\MetadataAssertion;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('materializes admissible provider field evidence into a pending canonical admission without mutation', function (): void {
    $artist = Artist::factory()->create(['country_code' => null]);
    $attempt = [
        'id' => 'attempt-provider-admission-1',
        'entity_type' => EntityType::Artist->value,
        'entity_id' => (string) $artist->getKey(),
        'provider' => 'musicbrainz',
        'need_kind' => 'field',
        'need_key' => 'country_code',
        'reason' => 'Missing country code.',
    ];
    $payload = [
        'provider' => 'musicbrainz',
        'candidates' => [[
            'provider_slug' => 'musicbrainz',
            'entity_type' => EntityType::Artist->value,
            'fields' => ['countryCode' => ['presence' => 'provided', 'value' => 'GB']],
        ]],
    ];

    $first = app(MaterializeProviderAdmissionEvidence::class)->handle($attempt, $payload);
    $second = app(MaterializeProviderAdmissionEvidence::class)->handle($attempt, $payload);

    $artist->refresh();
    $assertion = MetadataAssertion::query()->sole();
    $decision = CanonicalAdmissionDecision::query()->sole();

    expect($artist->country_code)->toBeNull()
        ->and($assertion->verification_state)->toBe(VerificationState::Candidate)
        ->and($assertion->value)->toBe(['value' => 'GB'])
        ->and($decision->status)->toBe(CanonicalAdmissionStatus::Pending)
        ->and($first['canonical_admission']['metadata_assertion_id'])->toBe((string) $assertion->getKey())
        ->and($second['canonical_admission']['decision_id'])->toBe((string) $decision->getKey())
        ->and(MetadataAssertion::query()->count())->toBe(1)
        ->and(CanonicalAdmissionDecision::query()->count())->toBe(1);
});

it('connects an admissible enrichment field result to the governed canonical admission queue', function (): void {
    $artist = Artist::factory()->create(['country_code' => null]);
    $attempt = EnrichmentAttempt::query()->create([
        'entity_type' => EntityType::Artist,
        'entity_id' => (string) $artist->getKey(),
        'provider' => 'musicbrainz',
        'need_kind' => 'field',
        'need_key' => 'country_code',
        'reason' => 'Missing country code.',
        'status' => 'ready',
        'priority' => 50,
        'attempt_count' => 1,
    ]);

    app()->instance(EnrichmentExecutor::class, new class implements EnrichmentExecutor
    {
        public function execute(array $attempt): EnrichmentExecutionResult
        {
            return EnrichmentExecutionResult::succeeded([
                'provider' => 'musicbrainz',
                'need' => ['kind' => 'field', 'key' => 'country_code'],
                'candidates' => [[
                    'provider_slug' => 'musicbrainz',
                    'entity_type' => EntityType::Artist->value,
                    'fields' => ['countryCode' => ['presence' => 'provided', 'value' => 'GB']],
                    'validation' => ['valid' => true, 'issues' => []],
                ]],
            ]);
        }
    });

    (new ExecuteEnrichmentAttempt((string) $attempt->getKey()))->handle(
        app(EnrichmentAttemptStore::class),
        app(EnrichmentExecutor::class),
        app(EnrichmentEvidenceAdmissionPolicy::class),
        app(MaterializeProviderAdmissionEvidence::class),
    );

    $attempt->refresh();
    $artist->refresh();

    expect($attempt->status)->toBe('succeeded')
        ->and($attempt->result_payload['canonical_admission']['status'])->toBe('pending')
        ->and($attempt->result_payload['canonical_admission']['canonical_mutation'])->toBeFalse()
        ->and($artist->country_code)->toBeNull()
        ->and(MetadataAssertion::query()->count())->toBe(1)
        ->and(CanonicalAdmissionDecision::query()->count())->toBe(1);
});
