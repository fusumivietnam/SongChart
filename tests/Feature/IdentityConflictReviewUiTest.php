<?php

declare(strict_types=1);

use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Catalog\Enums\MatchStatus;
use App\Domain\Providers\Identity\Review\Contracts\IdentityConflictReviewService;
use App\Enums\UserRole;
use App\Models\Catalog\Artist;
use App\Models\Catalog\EntityMatch;
use App\Models\Provider;
use App\Models\ProviderEntity;
use App\Models\Providers\Identity\IdentityConflictReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(RefreshDatabase::class);

function stage167Admin(UserRole $role = UserRole::SuperAdmin): User
{
    return User::factory()->create([
        'role' => $role,
        'is_active' => true,
        'email_verified_at' => now(),
        'two_factor_secret' => encrypt('stage-16-7-secret'),
        'two_factor_confirmed_at' => now(),
    ]);
}

/** @return array{0:IdentityConflictReview,1:Artist,2:Artist} */
function stage167Conflict(): array
{
    $provider = Provider::query()->create(['slug' => 'stage167', 'name' => 'Stage 167', 'category' => 'metadata', 'status' => 'sandbox', 'is_enabled' => true]);
    $providerEntity = ProviderEntity::query()->create(['provider_id' => $provider->id, 'entity_type' => 'artist', 'external_id' => 'external-167', 'status' => 'active']);
    $first = Artist::factory()->create(['name' => 'Candidate Alpha']);
    $second = Artist::factory()->create(['name' => 'Candidate Beta']);

    foreach ([$first, $second] as $artist) {
        EntityMatch::query()->create([
            'provider_entity_id' => $providerEntity->id,
            'entity_type' => 'artist',
            'entity_id' => $artist->id,
            'status' => MatchStatus::NeedsReview,
            'match_method' => 'exact-identifier-conflict',
            'confidence' => 1,
        ]);
    }

    $review = app(IdentityConflictReviewService::class)->open($providerEntity, EntityType::Artist, [$first->id, $second->id], ['reason' => 'test conflict']);

    return [$review, $first, $second];
}

it('protects identity conflict detail and decision routes', function (): void {
    /** @var TestCase $this */
    [$review] = stage167Conflict();

    $this->get(route('admin.identity-conflicts.show', $review))->assertRedirect(route('login'));
    $this->post(route('admin.identity-conflicts.decide', $review), [])->assertRedirect(route('login'));
});

it('renders candidate evidence and decision history for an administrator', function (): void {
    /** @var TestCase $this */
    [$review] = stage167Conflict();

    $this->actingAs(stage167Admin())
        ->get(route('admin.identity-conflicts.show', $review))
        ->assertOk()
        ->assertSee('data-admin-section="identity-conflict-detail"', false)
        ->assertSee('Candidate Alpha')
        ->assertSee('Candidate Beta')
        ->assertSee('test conflict');
});

it('records an audited approve-match decision through the existing review service', function (): void {
    /** @var TestCase $this */
    [$review, $first, $second] = stage167Conflict();
    $admin = stage167Admin();

    $this->actingAs($admin)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->post(route('admin.identity-conflicts.decide', $review), [
            'action' => 'approve_match',
            'selected_entity_id' => $first->id,
            'rationale' => 'The provider evidence resolves deterministically to Candidate Alpha.',
        ])
        ->assertRedirect(route('admin.identity-conflicts.show', $review));

    expect($review->refresh()->status->value)->toBe('resolved')
        ->and($review->decisions()->count())->toBe(1)
        ->and($review->decisions()->firstOrFail()->actor_id)->toBe($admin->id)
        ->and(EntityMatch::query()->where('entity_id', $first->id)->firstOrFail()->status)->toBe(MatchStatus::Matched)
        ->and(EntityMatch::query()->where('entity_id', $second->id)->firstOrFail()->status)->toBe(MatchStatus::Superseded);
});

it('requires the identity-review capability for mutations', function (): void {
    /** @var TestCase $this */
    [$review, $first] = stage167Conflict();
    $user = stage167Admin(UserRole::SystemOperator);

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->post(route('admin.identity-conflicts.decide', $review), [
            'action' => 'approve_match',
            'selected_entity_id' => $first->id,
            'rationale' => 'This user must not be allowed to record a review decision.',
        ])
        ->assertForbidden();
});
