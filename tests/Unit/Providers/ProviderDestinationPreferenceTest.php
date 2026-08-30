<?php

declare(strict_types=1);

use App\Domain\Providers\Destinations\DTO\ProviderDestinationSnapshot;
use App\Domain\Providers\Destinations\ProviderDestinationPreference;

function stage186DestinationSnapshot(array $overrides = []): ProviderDestinationSnapshot
{
    $defaults = [
        'id' => '01JDESTINATION00000000000001',
        'providerKey' => 'youtube',
        'providerApproved' => true,
        'providerEnabled' => true,
        'reviewState' => 'approved',
        'privacyStatus' => 'public',
        'embeddable' => true,
        'url' => 'https://www.youtube.com/watch?v=video123',
        'resourceId' => 'video123',
        'matchScore' => 90,
        'verifiedAt' => new DateTimeImmutable('2026-08-29T12:00:00+00:00'),
        'lastCheckedAt' => new DateTimeImmutable('2026-08-29T12:00:00+00:00'),
    ];

    /** @var array{id:string,providerKey:string,providerApproved:bool,providerEnabled:bool,reviewState:string,privacyStatus:?string,embeddable:bool,url:?string,resourceId:string,matchScore:int,verifiedAt:?DateTimeInterface,lastCheckedAt:?DateTimeInterface} $values */
    $values = array_replace($defaults, $overrides);

    return new ProviderDestinationSnapshot(...$values);
}

it('fails closed for stale private disabled and unsafe destinations', function (): void {
    $policy = new ProviderDestinationPreference();
    $now = new DateTimeImmutable('2026-08-30T12:00:00+00:00');

    expect($policy->isPublicEligible(stage186DestinationSnapshot(['lastCheckedAt' => null]), $now))->toBeFalse()
        ->and($policy->isPublicEligible(stage186DestinationSnapshot(['lastCheckedAt' => new DateTimeImmutable('2026-07-01T12:00:00+00:00')]), $now))->toBeFalse()
        ->and($policy->isPublicEligible(stage186DestinationSnapshot(['privacyStatus' => 'private']), $now))->toBeFalse()
        ->and($policy->isPublicEligible(stage186DestinationSnapshot(['providerEnabled' => false]), $now))->toBeFalse()
        ->and($policy->isPublicEligible(stage186DestinationSnapshot(['providerApproved' => false]), $now))->toBeFalse()
        ->and($policy->isPublicEligible(stage186DestinationSnapshot(['reviewState' => 'pending']), $now))->toBeFalse()
        ->and($policy->isPublicEligible(stage186DestinationSnapshot(['url' => 'http://www.youtube.com/watch?v=video123']), $now))->toBeFalse();
});

it('allows a fresh public outbound destination without treating it as embeddable', function (): void {
    $policy = new ProviderDestinationPreference();
    $now = new DateTimeImmutable('2026-08-30T12:00:00+00:00');
    $candidate = stage186DestinationSnapshot(['embeddable' => false]);

    expect($policy->isPublicEligible($candidate, $now))->toBeTrue()
        ->and($policy->canEmbed($candidate, $now))->toBeFalse();
});

it('selects an embeddable eligible destination before a newer outbound-only candidate', function (): void {
    $policy = new ProviderDestinationPreference();
    $now = new DateTimeImmutable('2026-08-30T12:00:00+00:00');

    $selected = $policy->select([
        stage186DestinationSnapshot([
            'id' => '02',
            'embeddable' => false,
            'matchScore' => 99,
            'lastCheckedAt' => new DateTimeImmutable('2026-08-30T11:00:00+00:00'),
        ]),
        stage186DestinationSnapshot([
            'id' => '01',
            'embeddable' => true,
            'matchScore' => 90,
            'lastCheckedAt' => new DateTimeImmutable('2026-08-29T12:00:00+00:00'),
        ]),
    ], $now);

    expect($selected?->id)->toBe('01');
});

it('uses stable provider and destination identifiers as the final tie break', function (): void {
    $policy = new ProviderDestinationPreference();
    $now = new DateTimeImmutable('2026-08-30T12:00:00+00:00');

    $selected = $policy->select([
        stage186DestinationSnapshot(['id' => '02', 'providerKey' => 'youtube']),
        stage186DestinationSnapshot(['id' => '01', 'providerKey' => 'youtube']),
    ], $now);

    expect($selected?->id)->toBe('01');
});
