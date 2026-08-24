<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Fusion;

final readonly class FieldResolution
{
    /** @param list<FieldEvidence> $evidence */
    public function __construct(
        public string $fieldName,
        public ?FieldEvidence $selected,
        public array $evidence,
        public bool $hasConflict,
    ) {}

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'field' => $this->fieldName,
            'selected' => $this->selected === null ? null : [
                'value' => $this->selected->value,
                'source' => $this->selected->sourceKey,
                'score' => $this->selected->score(),
                'verification_state' => $this->selected->verificationState,
                'observed_at' => $this->selected->observedAt,
            ],
            'evidence_count' => count($this->evidence),
            'has_conflict' => $this->hasConflict,
        ];
    }
}
