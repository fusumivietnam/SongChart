<?php

declare(strict_types=1);

use App\Application\Catalog\Admission\MaterializeProviderAdmissionEvidence;
use App\Domain\Catalog\Enums\CanonicalAdmissionStatus;
use App\Domain\Catalog\Enums\EntityType;
use App\Models\Catalog\Artist;
use App\Models\Catalog\CanonicalAdmissionDecision;
use App\Models\Catalog\MetadataAssertion;
use App\Models\Catalog\Recording;
use App\Models\Catalog\RecordingVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;

uses(RefreshDatabase::class);

function goldenProviderAttempt(Recording $recording, string $provider, string $id): array
{
    return [
        'id' => $id,
        'entity_type' => EntityType::Recording->value,
        'entity_id' => (string) $recording->getKey(),
        'provider' => $provider,
        'need_kind' => 'field',
        'need_key' => 'duration_ms',
        'reason' => 'Golden provider evidence for recording duration.',
    ];
}

function goldenProviderPayload(string $provider, int $durationMs): array
{
    return [
        'provider' => $provider,
        'candidates' => [[
            'provider_slug' => $provider,
            'entity_type' => EntityType::Recording->value,
            'fields' => [
                'durationMs' => [
                    'presence' => 'provided',
                    'value' => $durationMs,
                ],
            ],
        ]],
    ];
}

it('replays identical single-provider evidence idempotently', function (): void {
    $recording = Recording::factory()->create(['duration_ms' => null]);
    $materialize = app(MaterializeProviderAdmissionEvidence::class);
    $attempt = goldenProviderAttempt($recording, 'musicbrainz', 'golden-single-provider');
    $payload = goldenProviderPayload('musicbrainz', 240000);

    $first = $materialize->handle($attempt, $payload);
    $second = $materialize->handle($attempt, $payload);

    expect(Recording::query()->count())->toBe(1)
        ->and(MetadataAssertion::query()->count())->toBe(1)
        ->and(CanonicalAdmissionDecision::query()->count())->toBe(1)
        ->and($first['canonical_admission']['metadata_assertion_id'])->toBe($second['canonical_admission']['metadata_assertion_id'])
        ->and($first['canonical_admission']['decision_id'])->toBe($second['canonical_admission']['decision_id']);
});

it('keeps agreeing multi-provider evidence separate while targeting one canonical recording', function (): void {
    $recording = Recording::factory()->create(['duration_ms' => null]);
    $materialize = app(MaterializeProviderAdmissionEvidence::class);

    $materialize->handle(
        goldenProviderAttempt($recording, 'musicbrainz', 'golden-multi-provider-mb'),
        goldenProviderPayload('musicbrainz', 240000),
    );
    $materialize->handle(
        goldenProviderAttempt($recording, 'youtube', 'golden-multi-provider-yt'),
        goldenProviderPayload('youtube', 240000),
    );

    $recording->refresh();

    expect(Recording::query()->count())->toBe(1)
        ->and($recording->duration_ms)->toBeNull()
        ->and(MetadataAssertion::query()->count())->toBe(2)
        ->and(MetadataAssertion::query()->distinct('metadata_source_id')->count('metadata_source_id'))->toBe(2)
        ->and(CanonicalAdmissionDecision::query()->count())->toBe(2)
        ->and(CanonicalAdmissionDecision::query()->where('status', CanonicalAdmissionStatus::Pending->value)->count())->toBe(2);
});

it('preserves conflicting provider evidence for governed review without canonical mutation', function (): void {
    $recording = Recording::factory()->create(['duration_ms' => null]);
    $materialize = app(MaterializeProviderAdmissionEvidence::class);

    $materialize->handle(
        goldenProviderAttempt($recording, 'musicbrainz', 'golden-conflict-mb'),
        goldenProviderPayload('musicbrainz', 240000),
    );
    $materialize->handle(
        goldenProviderAttempt($recording, 'youtube', 'golden-conflict-yt'),
        goldenProviderPayload('youtube', 241500),
    );

    $recording->refresh();
    $values = MetadataAssertion::query()
        ->get()
        ->map(fn (MetadataAssertion $assertion): mixed => $assertion->value['value'] ?? null)
        ->sort()
        ->values()
        ->all();

    expect($recording->duration_ms)->toBeNull()
        ->and($values)->toBe([240000, 241500])
        ->and(CanonicalAdmissionDecision::query()->where('status', CanonicalAdmissionStatus::Pending->value)->count())->toBe(2);
});

it('rejects ambiguous normalized provider candidates before admission evidence is persisted', function (): void {
    $recording = Recording::factory()->create(['duration_ms' => null]);
    $attempt = goldenProviderAttempt($recording, 'musicbrainz', 'golden-ambiguous-mb');
    $candidate = goldenProviderPayload('musicbrainz', 240000)['candidates'][0];

    expect(fn () => app(MaterializeProviderAdmissionEvidence::class)->handle($attempt, [
        'provider' => 'musicbrainz',
        'candidates' => [$candidate, $candidate],
    ]))->toThrow(LogicException::class, 'Admissible field evidence requires exactly one normalized candidate.');

    expect(MetadataAssertion::query()->count())->toBe(0)
        ->and(CanonicalAdmissionDecision::query()->count())->toBe(0);
});

it('keeps same-name artists as separate canonical identities until governed identity evidence resolves them', function (): void {
    Artist::factory()->create(['name' => 'Phoenix', 'slug' => 'phoenix-fr']);
    Artist::factory()->create(['name' => 'Phoenix', 'slug' => 'phoenix-us']);

    expect(Artist::query()->where('name', 'Phoenix')->count())->toBe(2)
        ->and(Artist::query()->where('slug', 'phoenix-fr')->exists())->toBeTrue()
        ->and(Artist::query()->where('slug', 'phoenix-us')->exists())->toBeTrue();
});

it('models a remaster as a version of one canonical recording instead of a duplicate recording', function (): void {
    $recording = Recording::factory()->create(['title' => 'Golden Song', 'slug' => 'golden-song']);
    $version = RecordingVersion::factory()->create([
        'recording_id' => (string) $recording->getKey(),
        'name' => '2026 Remaster',
        'slug' => 'golden-song-2026-remaster',
        'version_type' => 'remaster',
    ]);

    expect(Recording::query()->count())->toBe(1)
        ->and(RecordingVersion::query()->count())->toBe(1)
        ->and((string) $version->recording_id)->toBe((string) $recording->getKey());
});
