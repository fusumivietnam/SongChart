<?php

declare(strict_types=1);

use App\Domain\Providers\Enums\ProviderStatus;
use App\Domain\Providers\Ingestion\DTO\ProviderImportPlan;
use App\Enums\UserRole;
use App\Jobs\Providers\Ingestion\FetchProviderImportPage;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

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

function stage1814MusicBrainzProvider(): Provider
{
    return Provider::query()->create([
        'slug' => 'musicbrainz',
        'name' => 'MusicBrainz',
        'category' => 'music',
        'status' => ProviderStatus::Approved,
        'is_enabled' => true,
    ]);
}

/** @return array<string, string> */
function stage1814RecordingPreviewInput(): array
{
    return [
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
    ];
}

it('shows the import workbench as a first-class admin navigation destination', function (): void {
    $this->actingAs(stage1813ProviderAdmin())
        ->get(route('admin.imports.preview'))
        ->assertOk()
        ->assertSee('Xem trước dữ liệu nhập')
        ->assertSee('Chỉ xem trước — chưa nhập dữ liệu')
        ->assertSee('Nhập dữ liệu')
        ->assertSee('Lịch sử tác vụ')
        ->assertSee('data-admin-nav="import-workbench"', false)
        ->assertSee('MusicBrainz')
        ->assertSee('Bản ghi âm');
});

it('links the import history workspace back to starting a new import', function (): void {
    $this->actingAs(stage1813ProviderAdmin())
        ->get(route('admin.imports.index'))
        ->assertOk()
        ->assertSee('Nhập dữ liệu mới')
        ->assertSee('Bắt đầu nhập dữ liệu')
        ->assertSee(route('admin.imports.preview'), false);
});

it('turns a valid MusicBrainz preview into a governed import plan without persisting a run', function (): void {
    $this->actingAs(stage1813ProviderAdmin())
        ->post(route('admin.imports.preview.build'), stage1814RecordingPreviewInput())
        ->assertOk()
        ->assertSee('Kế hoạch nhập dữ liệu')
        ->assertSee('Sẵn sàng tạo tác vụ nhập')
        ->assertSee('recording-lookup')
        ->assertSee('Ghi trực tiếp dữ liệu chuẩn')
        ->assertSee('Tạo tác vụ nhập');

    $this->assertDatabaseCount('provider_import_runs', 0);
});

it('creates a governed import run only after plan confirmation', function (): void {
    Queue::fake();
    stage1814MusicBrainzProvider();
    $input = stage1814RecordingPreviewInput();

    $previewResponse = $this->actingAs(stage1813ProviderAdmin())
        ->post(route('admin.imports.preview.build'), $input)
        ->assertOk();

    $plan = $previewResponse->viewData('plan');
    $this->assertInstanceOf(ProviderImportPlan::class, $plan);

    $this->withSession(['auth.password_confirmed_at' => time()])
        ->post(route('admin.imports.preview.execute'), array_merge($input, [
            'plan_fingerprint' => $plan->fingerprint,
        ]))
        ->assertRedirect();

    $this->assertDatabaseHas('provider_import_runs', [
        'operation' => 'recording-lookup',
        'status' => 'queued',
    ]);
    Queue::assertPushed(FetchProviderImportPage::class);
});

it('rejects a stale or tampered plan fingerprint before creating an import run', function (): void {
    Queue::fake();
    stage1814MusicBrainzProvider();
    $input = stage1814RecordingPreviewInput();

    $this->actingAs(stage1813ProviderAdmin())
        ->withSession(['auth.password_confirmed_at' => time()])
        ->from(route('admin.imports.preview'))
        ->post(route('admin.imports.preview.execute'), array_merge($input, [
            'plan_fingerprint' => str_repeat('0', 64),
        ]))
        ->assertRedirect(route('admin.imports.preview'))
        ->assertSessionHasErrors('plan_fingerprint');

    $this->assertDatabaseCount('provider_import_runs', 0);
    Queue::assertNothingPushed();
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
