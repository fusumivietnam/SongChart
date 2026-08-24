<?php

declare(strict_types=1);

use App\Application\Discovery\Rules\DefaultDiscoveryRuleEngine;
use App\Domain\Discovery\DTO\DiscoveryEntitySnapshot;
use App\Domain\Discovery\DTO\DiscoveryRuleCondition;
use App\Domain\Discovery\DTO\DiscoveryRuleSet;
use App\Domain\Discovery\DTO\DiscoverySort;
use App\Domain\Discovery\Enums\DiscoverableEntityType;
use App\Domain\Discovery\Enums\DiscoveryRuleOperator;
use App\Domain\Discovery\Enums\DiscoverySortDirection;
use App\Support\Discovery\CanonicalDiscoveryFieldRegistry;

function discoveryRuleEngine(): DefaultDiscoveryRuleEngine
{
    return new DefaultDiscoveryRuleEngine(new CanonicalDiscoveryFieldRegistry);
}

it('compiles typed and rules without SQL or arbitrary fields', function (): void {
    $engine = discoveryRuleEngine();
    $rules = new DiscoveryRuleSet('and', [
        new DiscoveryRuleCondition('country_code', DiscoveryRuleOperator::Equal, 'VN'),
        new DiscoveryRuleCondition('verification_state', DiscoveryRuleOperator::In, ['verified', 'trusted']),
    ]);

    $compiled = $engine->compile(DiscoverableEntityType::Artist, $rules);
    $matching = new DiscoveryEntitySnapshot(DiscoverableEntityType::Artist, '01a', [
        'country_code' => 'VN',
        'verification_state' => 'verified',
    ]);
    $nonMatching = new DiscoveryEntitySnapshot(DiscoverableEntityType::Artist, '01b', [
        'country_code' => 'US',
        'verification_state' => 'verified',
    ]);

    expect($compiled->matches($matching))->toBeTrue()
        ->and($compiled->matches($nonMatching))->toBeFalse();
});

it('rejects unknown fields unsupported operators and invalid typed values', function (): void {
    $engine = discoveryRuleEngine();

    expect(fn () => $engine->compile(DiscoverableEntityType::Recording, new DiscoveryRuleSet('and', [
        new DiscoveryRuleCondition('provider_payload', DiscoveryRuleOperator::Equal, 'x'),
    ])))->toThrow(InvalidArgumentException::class)
        ->and(fn () => $engine->compile(DiscoverableEntityType::Recording, new DiscoveryRuleSet('and', [
            new DiscoveryRuleCondition('is_explicit', DiscoveryRuleOperator::GreaterThan, true),
        ])))->toThrow(InvalidArgumentException::class)
        ->and(fn () => $engine->compile(DiscoverableEntityType::Recording, new DiscoveryRuleSet('and', [
            new DiscoveryRuleCondition('duration_ms', DiscoveryRuleOperator::GreaterThan, '180000'),
        ])))->toThrow(InvalidArgumentException::class);
});

it('supports exists and ordered canonical comparisons', function (): void {
    $engine = discoveryRuleEngine();
    $entity = new DiscoveryEntitySnapshot(DiscoverableEntityType::Release, '01r', [
        'released_on' => '2026-08-10',
        'country_code' => null,
    ]);

    expect($engine->matches($entity, new DiscoveryRuleSet('and', [
        new DiscoveryRuleCondition('released_on', DiscoveryRuleOperator::GreaterThanOrEqual, '2026-08-01'),
        new DiscoveryRuleCondition('country_code', DiscoveryRuleOperator::NotExists),
    ])))->toBeTrue();
});

it('sorts deterministically with canonical id as implicit tie breaker', function (): void {
    $engine = discoveryRuleEngine();
    $entities = [
        new DiscoveryEntitySnapshot(DiscoverableEntityType::Recording, '02', ['duration_ms' => 180000]),
        new DiscoveryEntitySnapshot(DiscoverableEntityType::Recording, '01', ['duration_ms' => 180000]),
        new DiscoveryEntitySnapshot(DiscoverableEntityType::Recording, '03', ['duration_ms' => 240000]),
    ];

    $sorted = $engine->sort($entities, [new DiscoverySort('duration_ms', DiscoverySortDirection::Descending)]);

    expect(array_map(static fn (DiscoveryEntitySnapshot $entity): string => $entity->id, $sorted))
        ->toBe(['03', '01', '02']);
});

it('keeps rule evaluation entity-type safe', function (): void {
    $engine = discoveryRuleEngine();
    $compiled = $engine->compile(DiscoverableEntityType::Artist, new DiscoveryRuleSet('and', [
        new DiscoveryRuleCondition('country_code', DiscoveryRuleOperator::Equal, 'VN'),
    ]));

    expect(fn () => $compiled->matches(new DiscoveryEntitySnapshot(
        DiscoverableEntityType::Release,
        '01r',
        ['country_code' => 'VN'],
    )))->toThrow(InvalidArgumentException::class);
});
