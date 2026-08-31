<?php

declare(strict_types=1);

use App\Domain\Audit\Contracts\PrivilegedAuditLogger;
use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Providers\Enums\ProviderStatus;
use App\Models\Catalog\Recording;
use App\Models\Provider;
use App\Models\ProviderDestination;
use App\Models\Providers\ProviderOperationAudit;
use App\Models\User;
use App\Support\Providers\Operations\ProviderMutationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use LogicException;

uses(RefreshDatabase::class);

it('reverifies one YouTube destination through the audited provider mutation boundary', function (): void {
    config()->set('songchart.providers.youtube.enabled', true);
    config()->set('songchart.providers.youtube.api_key', 'test-key');

    $this->mock(PrivilegedAuditLogger::class)
        ->shouldReceive('record')
        ->once();

    $provider = Provider::query()->create([
        'slug' => 'youtube',
        'name' => 'YouTube',
        'category' => 'music',
        'status' => ProviderStatus::Approved,
        'is_enabled' => true,
    ]);
    $recording = Recording::factory()->create(['title' => 'Reverify Me', 'duration_ms' => 180000]);
    $destination = ProviderDestination::query()->create([
        'provider_id' => $provider->getKey(),
        'entity_type' => EntityType::Recording,
        'entity_id' => $recording->getKey(),
        'provider_resource_id' => 'reverify123',
        'url' => 'https://www.youtube.com/watch?v=reverify123',
        'title' => 'Reverify Me',
        'is_embeddable' => true,
        'privacy_status' => 'public',
        'match_score' => 95,
        'review_state' => 'approved',
        'evidence' => ['availability' => 'observed'],
        'verified_at' => now()->subDays(10),
        'last_checked_at' => now()->subDays(45),
    ]);
    $destination->refresh();
    $verifiedAt = $destination->verified_at;
    $actor = User::factory()->create();

    Http::fake([
        'www.googleapis.com/youtube/v3/videos*' => Http::response([
            'items' => [[
                'id' => 'reverify123',
                'snippet' => [
                    'title' => 'Reverify Me',
                    'channelId' => 'channel-1',
                    'channelTitle' => 'Official',
                ],
                'contentDetails' => ['duration' => 'PT3M'],
                'status' => ['privacyStatus' => 'private', 'embeddable' => false],
            ]],
        ]),
    ]);

    $service = app(ProviderMutationService::class);
    $service->reverifyDestination(
        $provider,
        (string) $destination->getKey(),
        $actor,
        'Destination is stale and needs provider evidence refreshed.',
        'destination-reverify-1',
    );

    $destination->refresh();
    expect($destination->entity_id)->toBe($recording->id)
        ->and($destination->entity_type)->toBe(EntityType::Recording)
        ->and($destination->review_state)->toBe('approved')
        ->and($destination->privacy_status)->toBe('private')
        ->and($destination->is_embeddable)->toBeFalse()
        ->and($destination->verified_at?->equalTo($verifiedAt))->toBeTrue()
        ->and($destination->last_checked_at)->not->toBeNull()
        ->and($destination->evidence['availability'])->toBe('observed');

    $audit = ProviderOperationAudit::query()->where('idempotency_key', 'destination-reverify-1')->firstOrFail();
    expect($audit->provider_id)->toBe($provider->id)
        ->and($audit->actor_user_id)->toBe($actor->id)
        ->and($audit->action)->toBe('destination_reverify')
        ->and($audit->before_state['destination_id'])->toBe($destination->id)
        ->and($audit->before_state['privacy_status'])->toBe('public')
        ->and($audit->after_state['privacy_status'])->toBe('private')
        ->and($audit->after_state['destination_id'])->toBe($destination->id);

    $service->reverifyDestination(
        $provider,
        (string) $destination->getKey(),
        $actor,
        'Destination is stale and needs provider evidence refreshed.',
        'destination-reverify-1',
    );

    expect(ProviderOperationAudit::query()->where('idempotency_key', 'destination-reverify-1')->count())->toBe(1);
    Http::assertSentCount(1);
});

it('rejects destination reverification through the wrong provider boundary', function (): void {
    config()->set('songchart.providers.youtube.enabled', true);
    config()->set('songchart.providers.youtube.api_key', 'test-key');

    $this->mock(PrivilegedAuditLogger::class)
        ->shouldNotReceive('record');

    $youtube = Provider::query()->create([
        'slug' => 'youtube',
        'name' => 'YouTube',
        'category' => 'music',
        'status' => ProviderStatus::Approved,
        'is_enabled' => true,
    ]);
    $other = Provider::query()->create([
        'slug' => 'other-provider',
        'name' => 'Other Provider',
        'category' => 'music',
        'status' => ProviderStatus::Approved,
        'is_enabled' => true,
    ]);
    $recording = Recording::factory()->create();
    $destination = ProviderDestination::query()->create([
        'provider_id' => $youtube->getKey(),
        'entity_type' => EntityType::Recording,
        'entity_id' => $recording->getKey(),
        'provider_resource_id' => 'ownership123',
        'url' => 'https://www.youtube.com/watch?v=ownership123',
        'title' => 'Ownership check',
        'is_embeddable' => true,
        'privacy_status' => 'public',
        'match_score' => 90,
        'review_state' => 'approved',
        'verified_at' => now()->subDay(),
        'last_checked_at' => now()->subDay(),
    ]);

    expect(fn () => app(ProviderMutationService::class)->reverifyDestination(
        $other,
        (string) $destination->getKey(),
        User::factory()->create(),
        'Trying the wrong provider boundary must be rejected.',
        'destination-reverify-wrong-provider',
    ))->toThrow(LogicException::class, 'Destination does not belong to the requested provider.');

    Http::assertNothingSent();
    expect(ProviderOperationAudit::query()->count())->toBe(0);
});
