<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function stage1813ProviderAdmin(): User
{
    return User::factory()->create([
        'role' => UserRole::SuperAdmin,
        'is_active' => true,
        'email_verified_at' => now(),
        'two_factor_secret' => encrypt('stage-18-1-3'),
        'two_factor_confirmed_at' => now(),
    ]);
}

it('shows the read-only all-in-one import preview workspace to admins', function (): void {
    $this->actingAs(stage1813ProviderAdmin())
        ->get(route('admin.imports.preview'))
        ->assertOk()
        ->assertSee('Xem trước dữ liệu nhập')
        ->assertSee('Chỉ xem trước — chưa nhập dữ liệu')
        ->assertSee('MusicBrainz')
        ->assertSee('Bản ghi âm');
});

it('previews a MusicBrainz recording without persisting an import run', function (): void {
    $this->actingAs(stage1813ProviderAdmin())
        ->post(route('admin.imports.preview.build'), [
            'provider_slug' => 'musicbrainz',
            'entity_type' => 'recording',
            'external_id' => 'recording-mbid',
            'payload_json' => json_encode([
                'title' => 'Example Song',
                'length' => 181000,
                'isrcs' => ['USAAA2600001'],
                'artist-credit' => [
                    ['artist' => ['id' => 'artist-mbid', 'name' => 'Example Artist']],
                ],
            ], JSON_THROW_ON_ERROR),
        ])
        ->assertOk()
        ->assertSee('Có thể tiếp tục xử lý')
        ->assertSee('Example Song')
        ->assertSee('USAAA2600001')
        ->assertSee('performed-by')
        ->assertSee('artist-mbid');

    $this->assertDatabaseCount('provider_import_runs', 0);
});

it('rejects non-object JSON instead of failing the preview page', function (): void {
    $this->actingAs(stage1813ProviderAdmin())
        ->from(route('admin.imports.preview'))
        ->post(route('admin.imports.preview.build'), [
            'provider_slug' => 'musicbrainz',
            'entity_type' => 'recording',
            'external_id' => 'recording-mbid',
            'payload_json' => '[{"title":"Example Song"}]',
        ])
        ->assertRedirect(route('admin.imports.preview'))
        ->assertSessionHasErrors('payload_json');
});
