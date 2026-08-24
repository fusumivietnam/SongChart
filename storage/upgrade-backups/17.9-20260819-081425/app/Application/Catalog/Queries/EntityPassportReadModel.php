<?php

declare(strict_types=1);

namespace App\Application\Catalog\Queries;

use App\Domain\Catalog\Enums\EntityType;
use App\Models\Catalog\ExternalIdentifier;
use App\Models\Catalog\MetadataAssertion;
use App\Models\Catalog\MetadataConflict;
use App\Models\ProviderDestination;
use App\Support\Catalog\Fusion\CanonicalFieldResolver;
use App\Support\DomainContracts\DomainContractRegistry;
use Illuminate\Database\Eloquent\Model;

final readonly class EntityPassportReadModel
{
    public function __construct(
        private CanonicalFieldResolver $resolver,
        private DomainContractRegistry $contracts,
    ) {}

    /** @return array<string, mixed> */
    public function for(EntityType $type, Model $entity): array
    {
        $entityId = (string) $entity->getKey();
        $fields = array_values(array_filter([
            $this->contracts->displayField($type),
            $this->contracts->dateField($type),
            $this->contracts->descriptionField($type),
        ]));

        $assertedFields = MetadataAssertion::query()
            ->where('entity_type', $type->value)
            ->where('entity_id', $entityId)
            ->distinct()
            ->orderBy('field_name')
            ->pluck('field_name');
        foreach ($assertedFields as $assertedField) {
            if (is_string($assertedField) && $assertedField !== '') {
                $fields[] = $assertedField;
            }
        }
        $fields = array_values(array_unique($fields));

        $resolutions = [];
        $covered = 0;
        $scoreTotal = 0.0;
        foreach ($fields as $fieldName) {
            $resolution = $this->resolver->resolve($type, $entityId, $fieldName);
            if ($resolution->selected !== null) {
                $covered++;
                $scoreTotal += $resolution->selected->score();
            }
            $resolutions[] = $resolution->toArray();
        }

        $fieldCount = count($fields);
        $coverage = $fieldCount === 0 ? 100 : (int) round(($covered / $fieldCount) * 100);
        $confidence = $covered === 0 ? 0 : (int) round(($scoreTotal / $covered) * 100);

        return [
            'coverage' => $coverage,
            'confidence' => $confidence,
            'assertion_count' => MetadataAssertion::query()->where('entity_type', $type->value)->where('entity_id', $entityId)->count(),
            'identifier_count' => ExternalIdentifier::query()->where('entity_type', $type->value)->where('entity_id', $entityId)->count(),
            'open_conflict_count' => MetadataConflict::query()->where('entity_type', $type->value)->where('entity_id', $entityId)->where('status', 'open')->count(),
            'approved_destination_count' => ProviderDestination::query()->where('entity_type', $type->value)->where('entity_id', $entityId)->where('review_state', 'approved')->count(),
            'fields' => $resolutions,
        ];
    }
}
