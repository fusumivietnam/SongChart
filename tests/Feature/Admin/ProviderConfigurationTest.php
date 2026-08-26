<?php

declare(strict_types=1);

use App\Domain\Providers\Enums\ProviderStatus;
use App\Enums\UserRole;
use App\Models\Provider;
use App\Models\User;
use App\Support\Providers\Configuration\ProviderRuntimeConfiguration;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function stage181ProviderConfigurationAdmin(): User
{
    return User::factory()->create([
        'role' => UserRole::SuperAdmin,
        'is_active' => true,
        'email_verified_at' => now(),
        'two_factor_secret' => encrypt('stage-18-1-provider-config'),
        'two_factor_confirmed_at' => now(),
    ]);
}

function stage181Provider(string $slug, string $name): Provider
{
    return Provider::query()->create([
        'slug' => $slug,
        'name' => $name,
        'category' => 'music',
        'status' => ProviderStatus::Approved,
        'is_enabled' => true,
        'configuration' => [],
    ]);
}

it('shows provider setup controls without exposing stored secrets', function (): void {
    stage181Provider('musicbrainz', 'MusicBrainz');
    stage181Provider('youtube', 'YouTube');

    $this->actingAs(stage181ProviderConfigurationAdmin())
        ->get(route('admin.providers.index'))
        ->assertOk()
        ->assertSee('Thiết lập nguồn dữ liệu')
        ->assertSee('Thông tin nhận diện ứng dụng')
        ->assertSee('YouTube Data API key')
        ->assertSee('Secret được mã hóa');
});

it('stores a YouTube API key encrypted and resolves it only through runtime configuration', function (): void {
    $provider = stage181Provider('youtube', 'YouTube');
    $secret = 'AIzaSyStage181ExampleSecretKey000000';

    $this->actingAs(stage181ProviderConfigurationAdmin())
        ->withSession(['auth.password_confirmed_at' => time()])
        ->post(route('admin.providers.configuration.update', $provider), [
            'youtube_api_key' => $secret,
            'rationale' => 'Cấu hình YouTube cho môi trường vận hành.',
            'idempotency_key' => (string) str()->uuid(),
        ])
        ->assertRedirect();

    $provider->refresh();
    $rawConfiguration = json_encode($provider->configuration, JSON_THROW_ON_ERROR);
    expect($rawConfiguration)->not->toContain($secret);
    expect(app(ProviderRuntimeConfiguration::class)->secret('youtube', 'api_key'))->toBe($secret);

    $this->assertDatabaseHas('provider_operation_audits', [
        'provider_id' => $provider->getKey(),
        'action' => 'configure',
    ]);
});

it('stores MusicBrainz operator identity as a non-secret runtime setting', function (): void {
    $provider = stage181Provider('musicbrainz', 'MusicBrainz');
    $userAgent = 'SongChartWeb/1.0 (ops@example.com)';

    $this->actingAs(stage181ProviderConfigurationAdmin())
        ->withSession(['auth.password_confirmed_at' => time()])
        ->post(route('admin.providers.configuration.update', $provider), [
            'musicbrainz_user_agent' => $userAgent,
            'rationale' => 'Cấu hình nhận diện MusicBrainz cho vận hành.',
            'idempotency_key' => (string) str()->uuid(),
        ])
        ->assertRedirect();

    expect(app(ProviderRuntimeConfiguration::class)->setting('musicbrainz', 'user_agent'))->toBe($userAgent);
});
