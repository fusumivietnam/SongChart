<?php

declare(strict_types=1);

use App\Domain\Providers\Enums\ProviderStatus;
use App\Enums\UserRole;
use App\Models\Provider;
use App\Models\Providers\ProviderCredential;
use App\Models\User;
use App\Support\Providers\Configuration\ProviderRuntimeConfiguration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;

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

it('shows provider setup controls in system settings without exposing stored secrets', function (): void {
    stage181Provider('musicbrainz', 'MusicBrainz');
    stage181Provider('youtube', 'YouTube');

    $this->actingAs(stage181ProviderConfigurationAdmin())
        ->get(route('admin.system.index'))
        ->assertOk()
        ->assertSeeText('API & tích hợp')
        ->assertSee('MusicBrainz User-Agent')
        ->assertSee('YouTube Data API credential pool')
        ->assertSee('Secret được mã hóa');
});

it('stores a YouTube API key encrypted in the provider credential pool', function (): void {
    $provider = stage181Provider('youtube', 'YouTube');
    $secret = 'AIzaSyStage181ExampleSecretKey000000';

    $this->actingAs(stage181ProviderConfigurationAdmin())
        ->withSession(['auth.password_confirmed_at' => time()])
        ->post(route('admin.providers.configuration.update', $provider), [
            'youtube_api_keys' => $secret,
            'provider_operational_state' => 'enabled',
            'rationale' => 'Cấu hình YouTube cho môi trường vận hành.',
            'idempotency_key' => (string) str()->uuid(),
        ])
        ->assertRedirect();

    $provider->refresh();
    $rawConfiguration = json_encode($provider->configuration, JSON_THROW_ON_ERROR);
    expect($rawConfiguration)->not->toContain($secret)
        ->and(app(ProviderRuntimeConfiguration::class)->hasSecret('youtube', 'api_key'))->toBeFalse();

    $credential = ProviderCredential::query()
        ->where('provider_id', $provider->getKey())
        ->where('kind', 'api_key')
        ->sole();

    expect((string) $credential->encrypted_secret)->not->toBe($secret)
        ->and(Crypt::decryptString((string) $credential->encrypted_secret))->toBe($secret)
        ->and($credential->is_enabled)->toBeTrue();

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
            'provider_operational_state' => 'enabled',
            'rationale' => 'Cấu hình nhận diện MusicBrainz cho vận hành.',
            'idempotency_key' => (string) str()->uuid(),
        ])
        ->assertRedirect();

    expect(app(ProviderRuntimeConfiguration::class)->setting('musicbrainz', 'user_agent'))->toBe($userAgent);
});
