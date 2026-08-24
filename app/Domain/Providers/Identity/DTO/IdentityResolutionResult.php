<?php

declare(strict_types=1);

namespace App\Domain\Providers\Identity\DTO;

use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Providers\Identity\Enums\IdentityMatchMethod;
use App\Domain\Providers\Identity\Enums\IdentityResolutionOutcome;

final readonly class IdentityResolutionResult
{
    /** @param list<string> $candidateEntityIds */
    public function __construct(
        public IdentityResolutionOutcome $outcome,
        public EntityType $entityType,
        public ?string $entityId,
        public IdentityMatchMethod $method,
        public array $candidateEntityIds = [],
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'outcome' => $this->outcome->value,
            'entity_type' => $this->entityType->value,
            'entity_id' => $this->entityId,
            'method' => $this->method->value,
            'candidate_entity_ids' => $this->candidateEntityIds,
        ];
    }
}
