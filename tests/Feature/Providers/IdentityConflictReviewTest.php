<?php

declare(strict_types=1);

use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Catalog\Enums\MatchStatus;
use App\Domain\Providers\Enums\ProviderStatus;
use App\Domain\Providers\Identity\Review\Contracts\IdentityConflictReviewService;
use App\Domain\Providers\Identity\Review\Enums\IdentityConflictReviewStatus;
use App\Models\Catalog\EntityMatch;
use App\Models\Provider;
use App\Models\ProviderEntity;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('approves one exact candidate and preserves an append-only decision audit', function (): void {
    $provider = Provider::query()->create([
        'slug' => 'review-test', 'name' => 'Review Test', 'category' => 'metadata',
        'status' => ProviderStatus::Approved, 'is_enabled' => true,
    ]);
    $providerEntity = ProviderEntity::query()->create([
        'provider_id' => $provider->getKey(), 'entity_type' => EntityType::Artist->value,
        'external_id' => 'provider-artist-1', 'status' => 'active',
    ]);

    foreach (['01KZ0000000000000000000001', '01KZ0000000000000000000002'] as $candidate) {
        EntityMatch::query()->create([
            'provider_entity_id' => $providerEntity->getKey(), 'entity_type' => EntityType::Artist,
            'entity_id' => $candidate, 'status' => MatchStatus::NeedsReview,
            'match_method' => 'identifier-conflict',
        ]);
    }

    $service = app(IdentityConflictReviewService::class);
    $review = $service->open($providerEntity, EntityType::Artist, ['01KZ0000000000000000000001', '01KZ0000000000000000000002']);
    $result = $service->approveMatch($review, '01KZ0000000000000000000001', rationale: 'Exact identifier verified.');

    expect($result->status)->toBe(IdentityConflictReviewStatus::Resolved)
        ->and($review->refresh()->decisions()->count())->toBe(1)
        ->and(EntityMatch::query()->where('entity_id', '01KZ0000000000000000000001')->firstOrFail()->status)->toBe(MatchStatus::Matched)
        ->and(EntityMatch::query()->where('entity_id', '01KZ0000000000000000000002')->firstOrFail()->status)->toBe(MatchStatus::Superseded);
});
