<?php

declare(strict_types=1);

use App\Application\Catalog\Admission\GovernedCanonicalAdmissionService;
use App\Domain\Catalog\Enums\CanonicalAdmissionStatus;
use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Catalog\Enums\VerificationState;
use App\Domain\Catalog\Events\CanonicalEntityChanged;
use App\Models\Catalog\Artist;
use App\Models\Catalog\MetadataAssertion;
use App\Models\Catalog\MetadataSource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

function canonicalAdmissionAssertion(Artist $artist, string $field, mixed $value): MetadataAssertion
{
    $source = MetadataSource::query()->firstOrCreate(
        ['key' => 'provider:musicbrainz'],
        ['name' => 'MusicBrainz', 'source_type' => 'provider-import'],
    );

    return MetadataAssertion::query()->create([
        'entity_type' => EntityType::Artist,
        'entity_id' => (string) $artist->getKey(),
        'field_name' => $field,
        'value' => ['value' => $value],
        'value_fingerprint' => hash('sha256', json_encode($value, JSON_THROW_ON_ERROR)),
        'metadata_source_id' => $source->getKey(),
        'verification_state' => VerificationState::Candidate,
        'confidence' => 0.95,
        'observed_at' => now(),
    ]);
}

it('stages evidence without mutating canonical data', function (): void {
    $artist = Artist::factory()->create(['name' => 'Before']);
    $assertion = canonicalAdmissionAssertion($artist, 'name', 'After');

    $decision = app(GovernedCanonicalAdmissionService::class)->stage($assertion);

    expect($decision->status)->toBe(CanonicalAdmissionStatus::Pending)
        ->and($artist->refresh()->name)->toBe('Before')
        ->and($assertion->refresh()->verification_state)->toBe(VerificationState::Candidate);
});

it('applies an approved scalar assertion atomically and emits one semantic canonical change', function (): void {
    Event::fake([CanonicalEntityChanged::class]);

    $artist = Artist::factory()->create(['name' => 'Before']);
    $reviewer = User::factory()->create();
    $assertion = canonicalAdmissionAssertion($artist, 'name', 'After');
    $service = app(GovernedCanonicalAdmissionService::class);
    $decision = $service->stage($assertion);

    $applied = $service->apply($decision, $reviewer, 'MusicBrainz is authoritative for the canonical artist name.');

    expect($applied->status)->toBe(CanonicalAdmissionStatus::Applied)
        ->and($applied->canonical_value)->toBe(['value' => 'After'])
        ->and($applied->reviewer_id)->toBe((string) $reviewer->getKey())
        ->and($artist->refresh()->name)->toBe('After')
        ->and($assertion->refresh()->verification_state)->toBe(VerificationState::Verified);

    Event::assertDispatched(CanonicalEntityChanged::class, fn (CanonicalEntityChanged $event): bool => $event->entityType === EntityType::Artist
        && $event->entityId === (string) $artist->getKey()
        && $event->fieldName === 'name'
    );
});

it('rejects evidence without changing canonical data', function (): void {
    $artist = Artist::factory()->create(['name' => 'Before']);
    $reviewer = User::factory()->create();
    $assertion = canonicalAdmissionAssertion($artist, 'name', 'After');
    $service = app(GovernedCanonicalAdmissionService::class);
    $decision = $service->stage($assertion);

    $rejected = $service->reject($decision, $reviewer, 'The proposed value conflicts with reviewed canonical evidence.');

    expect($rejected->status)->toBe(CanonicalAdmissionStatus::Rejected)
        ->and($artist->refresh()->name)->toBe('Before')
        ->and($assertion->refresh()->verification_state)->toBe(VerificationState::Rejected);
});

it('fails closed when evidence targets a non fillable canonical field', function (): void {
    $artist = Artist::factory()->create(['name' => 'Before']);
    $reviewer = User::factory()->create();
    $assertion = canonicalAdmissionAssertion($artist, 'created_at', '2026-08-26T00:00:00+07:00');
    $service = app(GovernedCanonicalAdmissionService::class);
    $decision = $service->stage($assertion);

    expect(fn () => $service->apply($decision, $reviewer, 'Attempting an unsupported canonical field mutation.'))
        ->toThrow(LogicException::class);

    expect($decision->refresh()->status)->toBe(CanonicalAdmissionStatus::Pending)
        ->and($assertion->refresh()->verification_state)->toBe(VerificationState::Candidate);
});
