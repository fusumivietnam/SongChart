<?php

declare(strict_types=1);

use App\Application\Catalog\Enrichment\BuildEnrichmentSchedule;
use App\Application\Catalog\Enrichment\PersistEnrichmentSchedule;
use App\Domain\Catalog\Enrichment\EnrichmentNeed;
use App\Domain\Catalog\Enrichment\EnrichmentPlan;
use App\Domain\Catalog\Enums\EntityType;
use App\Models\Catalog\EnrichmentAttempt;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('persists enrichment attempts idempotently across repeated schedule reservations', function (): void {
    $plan = new EnrichmentPlan([
        new EnrichmentNeed('identity', 'external_identifier', 'musicbrainz', 'high', 'low', 'Missing external identity.', true),
        new EnrichmentNeed('metadata', 'release_date', 'musicbrainz', 'normal', 'low', 'Missing release date.', true),
    ], 50);

    $schedule = app(BuildEnrichmentSchedule::class)->handle(EntityType::Recording, 'recording-123', $plan);
    $persist = app(PersistEnrichmentSchedule::class);

    $first = $persist->handle($schedule);
    $second = $persist->handle($schedule);

    expect($first)->toHaveCount(2)
        ->and($second)->toBe($first)
        ->and(EnrichmentAttempt::query()->count())->toBe(2)
        ->and(EnrichmentAttempt::query()->pluck('idempotency_key')->all())->toEqualCanonicalizing([
            $schedule->dispatches[0]->idempotencyKey,
            $schedule->dispatches[1]->idempotencyKey,
        ]);
});

it('stores execution state needed by later queue slices without performing provider work', function (): void {
    $plan = new EnrichmentPlan([
        new EnrichmentNeed('identity', 'external_identifier', 'musicbrainz', 'critical', 'medium', 'Missing external identity.', true),
    ], 75);

    $schedule = app(BuildEnrichmentSchedule::class)->handle(EntityType::Artist, 'artist-123', $plan);
    app(PersistEnrichmentSchedule::class)->handle($schedule);

    $attempt = EnrichmentAttempt::query()->sole();

    expect($attempt->entity_type)->toBe('artist')
        ->and($attempt->entity_id)->toBe('artist-123')
        ->and($attempt->provider)->toBe('musicbrainz')
        ->and($attempt->status)->toBe('planned')
        ->and($attempt->attempt_count)->toBe(0)
        ->and($attempt->last_error)->toBeNull();
});
