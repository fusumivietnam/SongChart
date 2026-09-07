<?php

declare(strict_types=1);

use App\Application\Catalog\Admission\GovernedCanonicalAdmissionService;
use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Catalog\Enums\VerificationState;
use App\Enums\UserRole;
use App\Models\Catalog\Artist;
use App\Models\Catalog\MetadataAssertion;
use App\Models\Catalog\MetadataSource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function stage21BrowserReviewFixture(): array
{
    $admin = User::factory()->create([
        'name' => 'Stage 21 Review Admin',
        'role' => UserRole::SuperAdmin,
        'is_active' => true,
        'email_verified_at' => now(),
        'two_factor_secret' => encrypt('stage-21-browser-review-secret'),
        'two_factor_confirmed_at' => now(),
    ]);

    $artist = Artist::factory()->create(['name' => 'Tên hiện tại']);
    $source = MetadataSource::query()->create([
        'key' => 'provider:musicbrainz-stage21-review',
        'name' => 'MusicBrainz',
        'source_type' => 'provider-import',
    ]);
    $proposedValue = 'Tên đề xuất từ MusicBrainz';

    $assertion = MetadataAssertion::query()->create([
        'entity_type' => EntityType::Artist,
        'entity_id' => (string) $artist->getKey(),
        'field_name' => 'name',
        'value' => ['value' => $proposedValue],
        'value_fingerprint' => hash('sha256', json_encode($proposedValue, JSON_THROW_ON_ERROR)),
        'metadata_source_id' => $source->getKey(),
        'verification_state' => VerificationState::Candidate,
        'confidence' => 0.95,
        'observed_at' => now(),
    ]);

    $decision = app(GovernedCanonicalAdmissionService::class)->stage($assertion);

    return [$admin, $decision];
}

it('captures owner review evidence for the Stage 21 editorial admin flow', function (): void {
    [$admin, $decision] = stage21BrowserReviewFixture();
    $this->actingAs($admin);

    $index = visit('/admin/canonical-admissions')
        ->assertSee('Duyệt thay đổi dữ liệu')
        ->assertSee('Đề xuất cần bạn xem xét')
        ->assertNoSmoke();
    $index->screenshot(filename: 'stage21-admission-index-desktop', fullPage: true);

    $detail = visit('/admin/canonical-admissions/'.(string) $decision->getKey())
        ->assertSee('Xem xét thay đổi dữ liệu')
        ->assertSee('Nếu chấp nhận')
        ->assertSee('Nếu từ chối')
        ->assertSee('Chấp nhận và cập nhật dữ liệu')
        ->assertSee('Từ chối đề xuất')
        ->assertNoSmoke();
    $detail->screenshot(filename: 'stage21-admission-detail-desktop', fullPage: true);

    $mobileIndex = visit('/admin/canonical-admissions')
        ->on()
        ->iPhone14Pro()
        ->assertSee('Duyệt thay đổi dữ liệu')
        ->assertSee('Đề xuất cần bạn xem xét')
        ->assertNoSmoke();
    $mobileIndex->screenshot(filename: 'stage21-admission-index-mobile', fullPage: true);

    $mobileDetail = visit('/admin/canonical-admissions/'.(string) $decision->getKey())
        ->on()
        ->iPhone14Pro()
        ->assertSee('Xem xét thay đổi dữ liệu')
        ->assertSee('Chấp nhận và cập nhật dữ liệu')
        ->assertSee('Từ chối đề xuất')
        ->assertNoSmoke();
    $mobileDetail->screenshot(filename: 'stage21-admission-detail-mobile', fullPage: true);
});
