<?php

declare(strict_types=1);

use App\Support\Providers\ProviderDestinationPolicy;

it('renders governed provider destination states', function (): void {
    $this->get('/releases/ok-computer')->assertOk()
        ->assertSee('data-provider-chooser', false)
        ->assertSee('data-provider-key="youtube"', false)
        ->assertSee('data-provider-status="available"', false)
        ->assertSee('data-provider-status="unknown"', false)
        ->assertSee('data-provider-status="stale"', false)
        ->assertSee('Không phát tại SongChart')
        ->assertSee('rel="noopener noreferrer external"', false);
});

it('only makes approved fresh https destinations actionable', function (): void {
    $response = $this->get('/releases/ok-computer')->assertOk();

    $response
        ->assertSee('https://www.youtube.com/results?search_query=OK%20Computer', false)
        ->assertDontSee('href="https://soundcloud.com/example/stale-link"', false)
        ->assertSee('Spotify hiện không khả dụng')
        ->assertSee('SoundCloud hiện không khả dụng');
});

it('rejects unsafe or unapproved destinations at the policy boundary', function (): void {
    $policy = app(ProviderDestinationPolicy::class);

    expect($policy->canOpen([
        'key' => 'youtube', 'status' => 'available', 'compliance_state' => 'approved', 'url' => 'http://www.youtube.com/watch?v=unsafe',
    ]))->toBeFalse()
        ->and($policy->canOpen([
            'key' => 'youtube', 'status' => 'available', 'compliance_state' => 'pending', 'url' => 'https://www.youtube.com/watch?v=pending',
        ]))->toBeFalse()
        ->and($policy->canOpen([
            'key' => 'youtube', 'status' => 'available', 'compliance_state' => 'approved', 'url' => 'https://example.com/not-youtube',
        ]))->toBeFalse();
});
