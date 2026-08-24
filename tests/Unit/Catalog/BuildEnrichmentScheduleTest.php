<?php

declare(strict_types=1);

use App\Application\Catalog\Enrichment\BuildEnrichmentSchedule;
use App\Domain\Catalog\Enrichment\EnrichmentNeed;
use App\Domain\Catalog\Enrichment\EnrichmentPlan;
use App\Domain\Catalog\Enums\EntityType;

it('builds deterministic deduplicated dispatches and defers disabled providers', function (): void {
    $enabled = new EnrichmentNeed('identity', 'external_identifier', 'musicbrainz', 'high', 'low', 'Missing external identity.', true);
    $duplicate = new EnrichmentNeed('identity', 'external_identifier', 'musicbrainz', 'high', 'low', 'Duplicate planner signal.', true);
    $lowerPriority = new EnrichmentNeed('metadata', 'release_date', 'musicbrainz', 'normal', 'low', 'Missing release date.', true);
    $disabled = new EnrichmentNeed('media', 'youtube_destination', 'youtube', 'critical', 'high', 'Provider disabled.', false);

    $plan = new EnrichmentPlan([$lowerPriority, $disabled, $duplicate, $enabled], 50);
    $builder = new BuildEnrichmentSchedule;

    $first = $builder->handle(EntityType::Recording, 'recording-123', $plan);
    $second = $builder->handle(EntityType::Recording, 'recording-123', $plan);

    expect($first->dispatches)->toHaveCount(2)
        ->and($first->deferred)->toHaveCount(1)
        ->and($first->dispatches[0]->need->key)->toBe('external_identifier')
        ->and($first->dispatches[1]->need->key)->toBe('release_date')
        ->and($first->dispatches[0]->idempotencyKey)->toBe($second->dispatches[0]->idempotencyKey)
        ->and($first->dispatches[0]->idempotencyKey)->toHaveLength(64)
        ->and($first->deferred[0]->provider)->toBe('youtube');
});

it('changes the idempotency key when the target entity changes', function (): void {
    $need = new EnrichmentNeed('identity', 'external_identifier', 'musicbrainz', 'high', 'low', 'Missing identity.', true);
    $builder = new BuildEnrichmentSchedule;

    $left = $builder->handle(EntityType::Artist, 'artist-a', new EnrichmentPlan([$need], 80));
    $right = $builder->handle(EntityType::Artist, 'artist-b', new EnrichmentPlan([$need], 80));

    expect($left->dispatches[0]->idempotencyKey)->not->toBe($right->dispatches[0]->idempotencyKey);
});
