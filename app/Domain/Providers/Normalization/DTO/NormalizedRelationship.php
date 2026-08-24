<?php

declare(strict_types=1);

namespace App\Domain\Providers\Normalization\DTO;

use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Providers\Normalization\ValueObjects\ProviderField;
use InvalidArgumentException;

final readonly class NormalizedRelationship
{
    /** @param array<string, ProviderField> $attributes */
    public function __construct(
        public string $type,
        public EntityType $targetEntityType,
        public string $targetExternalId,
        public array $attributes = [],
    ) {
        if (trim($type) === '' || trim($targetExternalId) === '') {
            throw new InvalidArgumentException('Normalized relationships require type and target external ID.');
        }
    }

    /** @return array{type: string, target_entity_type: string, target_external_id: string, attributes: array<string, array{presence: string, value?: mixed}>} */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'target_entity_type' => $this->targetEntityType->value,
            'target_external_id' => $this->targetExternalId,
            'attributes' => array_map(static fn (ProviderField $field): array => $field->toArray(), $this->attributes),
        ];
    }
}
