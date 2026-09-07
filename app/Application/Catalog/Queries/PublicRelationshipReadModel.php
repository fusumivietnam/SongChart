<?php

declare(strict_types=1);

namespace App\Application\Catalog\Queries;

use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Catalog\Enums\RelationshipType;
use App\Domain\Catalog\Enums\VerificationState;
use App\Models\Catalog\EntityRelationship;
use App\Support\Catalog\PublicEntityUrl;
use App\Support\DomainContracts\DomainContractRegistry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final readonly class PublicRelationshipReadModel
{
    public function __construct(private DomainContractRegistry $contracts) {}

    /** @return list<array<string, mixed>> */
    public function for(EntityType $type, Model $model): array
    {
        $rows = EntityRelationship::query()
            ->where('verification_state', VerificationState::Verified->value)
            ->where(function (Builder $query) use ($type, $model): void {
                $query->where([
                    'subject_type' => $type->value,
                    'subject_id' => $model->getKey(),
                ])->orWhere(function (Builder $inverse) use ($type, $model): void {
                    $inverse->where([
                        'object_type' => $type->value,
                        'object_id' => $model->getKey(),
                    ]);
                });
            })
            ->orderBy('relationship_type')
            ->latest('updated_at')
            ->limit(20)
            ->get();

        $items = [];
        foreach ($rows as $row) {
            $subjectType = $row->subject_type;
            $objectType = $row->object_type;
            if (! $subjectType instanceof EntityType || ! $objectType instanceof EntityType) {
                continue;
            }

            $isSubject = $subjectType === $type && (string) $row->subject_id === (string) $model->getKey();
            $targetType = $isSubject ? $objectType : $subjectType;
            $targetId = $isSubject ? (string) $row->object_id : (string) $row->subject_id;
            $target = $targetType->modelClass()::query()->find($targetId);
            if (! $target instanceof Model) {
                continue;
            }

            $relationshipType = $row->relationship_type;
            $metadata = is_array($row->metadata) ? $row->metadata : [];
            $artistType = $targetType === EntityType::Artist ? (string) ($target->getAttribute('artist_type') ?? '') : null;
            $display = (string) $target->getAttribute($this->contracts->displayField($targetType));
            $creditedAs = trim((string) ($metadata['credited_as'] ?? ''));
            $joinPhrase = trim((string) ($metadata['join_phrase'] ?? ''));
            $position = $metadata['position'] ?? null;

            $items[] = [
                'type' => $targetType->value,
                'label' => $targetType->label(),
                'canonical_id' => (string) $target->getKey(),
                'slug' => (string) $target->getAttribute($this->contracts->slugField($targetType)),
                'title' => $creditedAs !== '' ? $creditedAs : $display,
                'canonical_title' => $display,
                'relationship_type' => $relationshipType instanceof RelationshipType ? $relationshipType->value : (string) $relationshipType,
                'direction' => $isSubject ? 'outbound' : 'inbound',
                'join_phrase' => $joinPhrase !== '' ? $joinPhrase : null,
                'position' => is_numeric($position) ? (int) $position : null,
                'verified' => true,
                'url' => PublicEntityUrl::to(
                    $targetType,
                    (string) $target->getAttribute($this->contracts->slugField($targetType)),
                    $artistType,
                ),
            ];
        }

        usort($items, static function (array $left, array $right): int {
            $position = ($left['position'] ?? PHP_INT_MAX) <=> ($right['position'] ?? PHP_INT_MAX);

            return $position !== 0 ? $position : strnatcasecmp((string) $left['title'], (string) $right['title']);
        });

        return $items;
    }
}
