<?php

declare(strict_types=1);

use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Providers\Identity\DTO\IdentityResolutionResult;
use App\Domain\Providers\Identity\Enums\IdentityMatchMethod;
use App\Domain\Providers\Identity\Enums\IdentityResolutionOutcome;

it('serializes deterministic exact identity decisions', function (): void {
    $result = new IdentityResolutionResult(
        IdentityResolutionOutcome::Matched,
        EntityType::Artist,
        '01TESTENTITY',
        IdentityMatchMethod::ExternalIdentifier,
        ['01TESTENTITY'],
    );

    expect($result->toArray())->toBe([
        'outcome' => 'matched',
        'entity_type' => 'artist',
        'entity_id' => '01TESTENTITY',
        'method' => 'external-identifier',
        'candidate_entity_ids' => ['01TESTENTITY'],
    ]);
});
