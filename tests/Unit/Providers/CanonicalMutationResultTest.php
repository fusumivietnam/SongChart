<?php

declare(strict_types=1);

use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Providers\Mutation\DTO\CanonicalMutationResult;
use App\Domain\Providers\Mutation\Enums\CanonicalMutationOutcome;

it('serializes canonical mutation outcomes deterministically', function (): void {
    $result = new CanonicalMutationResult(
        CanonicalMutationOutcome::Created,
        EntityType::Artist,
        '01TEST',
        ['name'],
        2,
        1,
        0,
    );

    expect($result->toArray())->toBe([
        'outcome' => 'created',
        'entity_type' => 'artist',
        'entity_id' => '01TEST',
        'changed_fields' => ['name'],
        'assertions_recorded' => 2,
        'identifiers_attached' => 1,
        'relationships_attached' => 0,
    ]);
});
