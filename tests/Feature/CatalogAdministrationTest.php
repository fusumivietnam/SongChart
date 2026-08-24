<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\Catalog\Artist;
use App\Models\Catalog\ExternalIdentifier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function stage163Admin(): User
{
    return User::factory()->create(['role' => UserRole::SuperAdmin, 'is_active' => true, 'email_verified_at' => now(), 'two_factor_secret' => encrypt('stage-16-3'), 'two_factor_confirmed_at' => now()]);
}

it('protects catalog administration routes', function (): void {
    $artist = Artist::factory()->create();
    $this->get(route('admin.catalog.entities.index', 'artist'))->assertRedirect(route('login'));
    $this->get(route('admin.catalog.entities.show', ['artist', $artist->id]))->assertRedirect(route('login'));
});

it('lists filters and opens canonical entity details', function (): void {
    $artist = Artist::factory()->create(['name' => 'Radiohead Stage 163', 'slug' => 'radiohead-stage-163']);
    ExternalIdentifier::query()->create(['entity_type' => 'artist', 'entity_id' => $artist->id, 'namespace' => 'musicbrainz_artist', 'value' => 'stage-163-mbid', 'is_primary' => true, 'verification_state' => 'verified']);

    $this->actingAs(stage163Admin())->get(route('admin.catalog.entities.index', ['artist', 'q' => 'Radiohead']))
        ->assertOk()->assertSee('data-admin-section="catalog-administration"', false)->assertSee('Radiohead Stage 163');

    $this->actingAs(stage163Admin())->get(route('admin.catalog.entities.show', ['artist', $artist->id]))
        ->assertOk()->assertSee('data-admin-section="catalog-entity-detail"', false)->assertSee('stage-163-mbid');
});

it('rejects unsupported entity types', function (): void {
    $this->actingAs(stage163Admin())->get('/admin/catalog/unknown')->assertNotFound();
});

it('allows a privileged catalog editor to update canonical artist metadata with audit rationale', function (): void {
    $artist = Artist::factory()->create(['name' => 'Old Name', 'slug' => 'old-name', 'country_code' => 'US']);
    $admin = stage163Admin();

    $this->actingAs($admin)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->patch(route('admin.catalog.artists.update', $artist->id), [
            'name' => 'New Name',
            'sort_name' => 'Name, New',
            'slug' => 'new-name',
            'artist_type' => 'group',
            'country_code' => 'fr',
            'verification_state' => 'verified',
            'rationale' => 'Correct canonical metadata after provider import review.',
        ])
        ->assertRedirect(route('admin.catalog.entities.show', ['artist', $artist->id]));

    $this->assertDatabaseHas('artists', [
        'id' => $artist->id,
        'name' => 'New Name',
        'slug' => 'new-name',
        'country_code' => 'FR',
        'verification_state' => 'verified',
    ]);

    $this->assertDatabaseHas('activity_log', [
        'event' => 'catalog.artist.updated',
        'subject_type' => Artist::class,
        'subject_id' => $artist->id,
    ]);
});

it('rejects canonical artist updates for a reviewer without catalog mutation capability', function (): void {
    $artist = Artist::factory()->create();
    $reviewer = User::factory()->withConfirmedTwoFactorAuthentication()->create(['role' => UserRole::Reviewer]);

    $this->actingAs($reviewer)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->patch(route('admin.catalog.artists.update', $artist->id), [
            'name' => $artist->name,
            'sort_name' => $artist->sort_name,
            'slug' => $artist->slug,
            'artist_type' => $artist->artist_type,
            'country_code' => $artist->country_code,
            'verification_state' => 'unverified',
            'rationale' => 'Attempted canonical metadata mutation by reviewer.',
        ])
        ->assertForbidden();
});
