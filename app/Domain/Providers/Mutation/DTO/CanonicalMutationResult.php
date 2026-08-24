<?php

declare(strict_types=1);

namespace App\Domain\Providers\Mutation\DTO;

use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Providers\Mutation\Enums\CanonicalMutationOutcome;

final readonly class CanonicalMutationResult
{
    /** @param list<string> $changedFields */
    public function __construct(
        public CanonicalMutationOutcome $outcome,
        public EntityType $entityType,
        public ?string $entityId,
        public array $changedFields = [],
        public int $assertionsRecorded = 0,
        public int $identifiersAttached = 0,
        public int $relationshipsAttached = 0,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'outcome' => $this->outcome->value,
            'entity_type' => $this->entityType->value,
            'entity_id' => $this->entityId,
            'changed_fields' => $this->changedFields,
            'assertions_recorded' => $this->assertionsRecorded,
            'identifiers_attached' => $this->identifiersAttached,
            'relationships_attached' => $this->relationshipsAttached,
        ];
    }
}
