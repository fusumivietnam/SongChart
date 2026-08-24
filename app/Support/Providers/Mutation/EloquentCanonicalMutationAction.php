<?php

declare(strict_types=1);

namespace App\Support\Providers\Mutation;

use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Catalog\Enums\RelationshipType;
use App\Domain\Catalog\Enums\VerificationState;
use App\Domain\Providers\Catalog\DTO\NormalizedProviderEntity;
use App\Domain\Providers\Identity\Contracts\ExactIdentityResolver;
use App\Domain\Providers\Identity\Enums\IdentityResolutionOutcome;
use App\Domain\Providers\Mutation\Contracts\CanonicalMutationAction;
use App\Domain\Providers\Mutation\DTO\CanonicalMutationResult;
use App\Domain\Providers\Mutation\Enums\CanonicalMutationOutcome;
use App\Domain\Providers\Normalization\DTO\NormalizedRelationship;
use App\Domain\Providers\Normalization\Enums\FieldPresence;
use App\Models\Catalog\Artist;
use App\Models\Catalog\EntityRelationship;
use App\Models\Catalog\ExternalIdentifier;
use App\Models\Catalog\MetadataAssertion;
use App\Models\Catalog\MetadataSource;
use App\Models\Catalog\Recording;
use App\Models\Catalog\Release;
use App\Models\Catalog\ReleaseGroup;
use App\Models\Catalog\Work;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

final class EloquentCanonicalMutationAction implements CanonicalMutationAction
{
    public function __construct(private readonly ExactIdentityResolver $identityResolver) {}

    public function supports(EntityType $entityType): bool
    {
        return in_array($entityType, [EntityType::Artist, EntityType::Work, EntityType::Recording, EntityType::ReleaseGroup, EntityType::Release], true);
    }

    public function mutate(NormalizedProviderEntity $entity, MetadataSource $source): CanonicalMutationResult
    {
        if (! $this->supports($entity->entityType)) {
            return new CanonicalMutationResult(CanonicalMutationOutcome::Skipped, $entity->entityType, null);
        }

        return DB::transaction(function () use ($entity, $source): CanonicalMutationResult {
            $resolution = $this->identityResolver->resolve($entity);
            if ($resolution->outcome === IdentityResolutionOutcome::Conflict) {
                return new CanonicalMutationResult(CanonicalMutationOutcome::Conflict, $entity->entityType, null);
            }

            $model = $resolution->entityId === null
                ? $this->newModel($entity->entityType)
                : $this->findModel($entity->entityType, $resolution->entityId);

            $wasNew = ! $model->exists;
            $attributes = $this->canonicalAttributes($entity);
            $changed = [];

            foreach ($attributes as $field => $value) {
                if ($value === null || (! $wasNew && $model->getAttribute($field) !== null)) {
                    continue;
                }
                $model->setAttribute($field, $value);
                $changed[] = $field;
            }

            if ($wasNew || $model->isDirty()) {
                $model->save();
            }

            if ($wasNew) {
                $this->identityResolver->recordCreatedMatch($entity, (string) $model->getKey());
            }

            $identifierCount = $this->attachIdentifiers($entity, $source, (string) $model->getKey());
            $assertionCount = $this->recordAssertions($entity, $source, (string) $model->getKey());
            $relationshipCount = $this->attachRelationships($entity, $source, (string) $model->getKey());
            if ($entity->entityType === EntityType::Recording) {
                $this->syncRecordingArtistCredits($entity, (string) $model->getKey());
                $this->syncRecordingWork($entity, $model);
            }

            $outcome = $wasNew
                ? CanonicalMutationOutcome::Created
                : ($changed === [] ? CanonicalMutationOutcome::Unchanged : CanonicalMutationOutcome::Updated);

            return new CanonicalMutationResult(
                $outcome,
                $entity->entityType,
                (string) $model->getKey(),
                $changed,
                $assertionCount,
                $identifierCount,
                $relationshipCount,
            );
        });
    }

    private function newModel(EntityType $type): Model
    {
        return match ($type) {
            EntityType::Artist => new Artist,
            EntityType::Work => new Work,
            EntityType::Recording => new Recording,
            EntityType::ReleaseGroup => new ReleaseGroup,
            EntityType::Release => new Release,
            default => throw new RuntimeException('Unsupported canonical entity type.'),
        };
    }

    private function findModel(EntityType $type, string $id): Model
    {
        return match ($type) {
            EntityType::Artist => Artist::query()->findOrFail($id),
            EntityType::Work => Work::query()->findOrFail($id),
            EntityType::Recording => Recording::query()->findOrFail($id),
            EntityType::ReleaseGroup => ReleaseGroup::query()->findOrFail($id),
            EntityType::Release => Release::query()->findOrFail($id),
            default => throw new RuntimeException('Unsupported canonical entity type.'),
        };
    }

    /** @return array<string, mixed> */
    private function canonicalAttributes(NormalizedProviderEntity $entity): array
    {
        $fields = $entity->data->fields();
        $provided = static fn (string $key): mixed => isset($fields[$key]) && $fields[$key]->presence === FieldPresence::Provided
            ? $fields[$key]->value
            : null;

        return match ($entity->entityType) {
            EntityType::Artist => [
                'name' => $provided('name'),
                'sort_name' => $provided('sortName'),
                'slug' => $this->uniqueSlug(EntityType::Artist, (string) ($provided('name') ?? $entity->externalId), $entity->externalId),
                'artist_type' => $provided('type') === null ? null : Str::snake(strtolower((string) $provided('type'))),
                'country_code' => $provided('countryCode'),
                'verification_state' => VerificationState::Candidate,
            ],
            EntityType::Work => [
                'title' => $provided('title'),
                'slug' => $this->uniqueSlug(EntityType::Work, (string) ($provided('title') ?? $entity->externalId), $entity->externalId),
                'work_type' => $provided('type'),
                'language_code' => $provided('languageCode'),
                'verification_state' => VerificationState::Candidate,
            ],
            EntityType::Recording => [
                'title' => $provided('title'),
                'slug' => $this->uniqueSlug(EntityType::Recording, (string) ($provided('title') ?? $entity->externalId), $entity->externalId),
                'duration_ms' => $provided('durationMs'),
                'verification_state' => VerificationState::Candidate,
            ],
            EntityType::ReleaseGroup => [
                'title' => $provided('title'),
                'slug' => $this->uniqueSlug(EntityType::ReleaseGroup, (string) ($provided('title') ?? $entity->externalId), $entity->externalId),
                'primary_type' => $provided('primaryType') === null ? null : Str::snake(strtolower((string) $provided('primaryType'))),
                'secondary_types' => is_array($provided('secondaryTypes')) ? $provided('secondaryTypes') : null,
                'first_release_date' => $this->fullDateOrNull($provided('firstReleaseDate')),
                'disambiguation' => $provided('disambiguation'),
                'verification_state' => VerificationState::Candidate,
            ],
            EntityType::Release => [
                'release_group_id' => $this->releaseGroupId($entity, $provided('releaseGroupMbid')),
                'title' => $provided('title'),
                'slug' => $this->uniqueSlug(EntityType::Release, (string) ($provided('title') ?? $entity->externalId), $entity->externalId),
                'release_type' => $provided('primaryType') === null ? null : Str::snake(strtolower((string) $provided('primaryType'))),
                'released_on' => $this->fullDateOrNull($provided('releaseDate')),
                'country_code' => $provided('countryCode'),
                'barcode' => $provided('barcode'),
                'verification_state' => VerificationState::Candidate,
            ],
            default => [],
        };
    }

    private function fullDateOrNull(mixed $value): ?string
    {
        if (! is_string($value) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        return $value;
    }

    private function releaseGroupId(NormalizedProviderEntity $entity, mixed $releaseGroupMbid): ?string
    {
        if ($entity->entityType !== EntityType::Release || ! is_string($releaseGroupMbid) || $releaseGroupMbid === '') {
            return null;
        }

        $identifier = ExternalIdentifier::query()
            ->where('entity_type', EntityType::ReleaseGroup)
            ->where('namespace', 'provider:'.$entity->providerSlug)
            ->where('value', $releaseGroupMbid)
            ->first();

        return $identifier === null ? null : (string) $identifier->entity_id;
    }

    private function uniqueSlug(EntityType $type, string $label, string $externalId): string
    {
        $base = Str::slug($label);
        if ($base === '') {
            $base = $type->value;
        }

        $modelClass = $type->modelClass();
        if (! $modelClass::query()->where('slug', $base)->exists()) {
            return $base;
        }

        return $base.'-'.substr(hash('sha256', $externalId), 0, 8);
    }

    private function attachIdentifiers(NormalizedProviderEntity $entity, MetadataSource $source, string $entityId): int
    {
        $identifiers = [['namespace' => 'provider:'.$entity->providerSlug, 'value' => $entity->externalId]];
        foreach ($entity->identifiers as $identifier) {
            $identifiers[] = $identifier->toArray();
        }

        $count = 0;
        foreach ($identifiers as $identifier) {
            ExternalIdentifier::query()->firstOrCreate([
                'namespace' => $identifier['namespace'],
                'value' => $identifier['value'],
            ], [
                'entity_type' => $entity->entityType,
                'entity_id' => $entityId,
                'metadata_source_id' => $source->getKey(),
                'is_primary' => $identifier['namespace'] === 'provider:'.$entity->providerSlug,
                'verification_state' => VerificationState::Candidate,
            ]);
            $count++;
        }

        return $count;
    }

    private function recordAssertions(NormalizedProviderEntity $entity, MetadataSource $source, string $entityId): int
    {
        $count = 0;
        foreach ($entity->data->fields() as $fieldName => $field) {
            if ($field->presence !== FieldPresence::Provided) {
                continue;
            }
            $value = ['value' => $field->value];
            MetadataAssertion::query()->firstOrCreate([
                'entity_type' => $entity->entityType,
                'entity_id' => $entityId,
                'field_name' => $fieldName,
                'metadata_source_id' => $source->getKey(),
                'value_fingerprint' => hash('sha256', json_encode($value, JSON_THROW_ON_ERROR)),
            ], [
                'value' => $value,
                'verification_state' => VerificationState::Candidate,
                'observed_at' => now(),
            ]);
            $count++;
        }

        return $count;
    }

    private function syncRecordingArtistCredits(NormalizedProviderEntity $entity, string $recordingId): void
    {
        foreach ($entity->relationships as $relationship) {
            if ($relationship->type !== RelationshipType::PerformedBy->value || $relationship->targetEntityType !== EntityType::Artist) {
                continue;
            }

            $target = ExternalIdentifier::query()
                ->where('namespace', 'provider:'.$entity->providerSlug)
                ->where('value', $relationship->targetExternalId)
                ->first();
            if ($target === null || (string) $target->getRawOriginal('entity_type') !== EntityType::Artist->value) {
                continue;
            }

            $positionField = $relationship->attributes['position'] ?? null;
            $position = $positionField !== null && $positionField->presence === FieldPresence::Provided
                ? max(1, (int) $positionField->value)
                : 1;

            $creditedNameField = $relationship->attributes['credited_name'] ?? null;
            $joinPhraseField = $relationship->attributes['join_phrase'] ?? null;

            DB::table('artist_recording')->updateOrInsert([
                'artist_id' => $target->entity_id,
                'recording_id' => $recordingId,
                'credit_role' => 'primary',
            ], [
                'position' => $position,
                'credited_name' => $creditedNameField !== null && $creditedNameField->presence === FieldPresence::Provided ? (string) $creditedNameField->value : null,
                'join_phrase' => $joinPhraseField !== null && $joinPhraseField->presence === FieldPresence::Provided ? (string) $joinPhraseField->value : null,
                'updated_at' => now(),
                'created_at' => now(),
            ]);
        }
    }

    private function syncRecordingWork(NormalizedProviderEntity $entity, Model $recording): void
    {
        $workRelationships = array_values(array_filter(
            $entity->relationships,
            static fn ($relationship): bool => $relationship->type === RelationshipType::RecordingOf->value
                && $relationship->targetEntityType === EntityType::Work,
        ));

        // The legacy recordings.work_id shortcut can represent only an unambiguous single Work.
        // All Work links, including medleys/mashups, remain losslessly represented in entity_relationships.
        if (count($workRelationships) !== 1 || $recording->getAttribute('work_id') !== null) {
            return;
        }

        $relationship = $workRelationships[0];
        $target = ExternalIdentifier::query()
            ->where('namespace', 'provider:'.$entity->providerSlug)
            ->where('value', $relationship->targetExternalId)
            ->first();
        if ($target === null || (string) $target->getRawOriginal('entity_type') !== EntityType::Work->value) {
            return;
        }

        $recording->setAttribute('work_id', $target->entity_id);
        $recording->save();
    }

    private function attachRelationships(NormalizedProviderEntity $entity, MetadataSource $source, string $entityId): int
    {
        $count = 0;
        foreach ($entity->relationships as $relationship) {
            $target = ExternalIdentifier::query()
                ->where('namespace', 'provider:'.$entity->providerSlug)
                ->where('value', $relationship->targetExternalId)
                ->first();

            if ($target === null) {
                $target = $this->materializeRelationshipTarget($entity, $relationship, $source);
            }
            if ($target === null) {
                continue;
            }

            $metadata = [];
            foreach ($relationship->attributes as $name => $field) {
                if ($field->presence === FieldPresence::Provided) {
                    $metadata[$name] = $field->value;
                }
            }

            EntityRelationship::query()->updateOrCreate([
                'subject_type' => $entity->entityType,
                'subject_id' => $entityId,
                'relationship_type' => RelationshipType::tryFrom($relationship->type) ?? RelationshipType::RelatedTo,
                'object_type' => $relationship->targetEntityType,
                'object_id' => $target->entity_id,
            ], [
                'metadata_source_id' => $source->getKey(),
                'verification_state' => VerificationState::Candidate,
                'metadata' => $metadata === [] ? null : $metadata,
            ]);
            $count++;
        }

        return $count;
    }

    private function materializeRelationshipTarget(
        NormalizedProviderEntity $entity,
        NormalizedRelationship $relationship,
        MetadataSource $source,
    ): ?ExternalIdentifier {
        if ($relationship->targetEntityType === EntityType::Artist) {
            $name = $this->relationshipAttribute($relationship, 'target_name');
            if (! is_string($name) || trim($name) === '') {
                return null;
            }

            $artist = Artist::query()->create([
                'name' => $name,
                'sort_name' => $this->nullableRelationshipString($relationship, 'target_sort_name'),
                'slug' => $this->uniqueSlug(EntityType::Artist, $name, $relationship->targetExternalId),
                'artist_type' => (($type = $this->nullableRelationshipString($relationship, 'target_type')) !== null && $type !== '') ? Str::snake(strtolower($type)) : 'person',
                'country_code' => (($country = $this->nullableRelationshipString($relationship, 'target_country_code')) !== null && $country !== '') ? strtoupper($country) : null,
                'verification_state' => VerificationState::Candidate,
            ]);

            return ExternalIdentifier::query()->create([
                'entity_type' => EntityType::Artist,
                'entity_id' => $artist->getKey(),
                'namespace' => 'provider:'.$entity->providerSlug,
                'value' => $relationship->targetExternalId,
                'metadata_source_id' => $source->getKey(),
                'is_primary' => true,
                'verification_state' => VerificationState::Candidate,
            ]);
        }

        if ($relationship->targetEntityType === EntityType::Work) {
            $title = $this->relationshipAttribute($relationship, 'target_title');
            if (! is_string($title) || trim($title) === '') {
                return null;
            }

            $work = Work::query()->create([
                'title' => $title,
                'slug' => $this->uniqueSlug(EntityType::Work, $title, $relationship->targetExternalId),
                'work_type' => (($type = $this->nullableRelationshipString($relationship, 'target_type')) !== null && $type !== '') ? Str::snake(strtolower($type)) : 'song',
                'language_code' => $this->nullableRelationshipString($relationship, 'target_language_code'),
                'verification_state' => VerificationState::Candidate,
            ]);

            $identifier = ExternalIdentifier::query()->create([
                'entity_type' => EntityType::Work,
                'entity_id' => $work->getKey(),
                'namespace' => 'provider:'.$entity->providerSlug,
                'value' => $relationship->targetExternalId,
                'metadata_source_id' => $source->getKey(),
                'is_primary' => true,
                'verification_state' => VerificationState::Candidate,
            ]);

            $iswcs = $this->relationshipAttribute($relationship, 'target_iswcs');
            if (is_array($iswcs)) {
                foreach ($iswcs as $iswc) {
                    if (! is_string($iswc) || trim($iswc) === '') {
                        continue;
                    }
                    ExternalIdentifier::query()->firstOrCreate([
                        'namespace' => 'iswc',
                        'value' => strtoupper(trim($iswc)),
                    ], [
                        'entity_type' => EntityType::Work,
                        'entity_id' => $work->getKey(),
                        'metadata_source_id' => $source->getKey(),
                        'is_primary' => false,
                        'verification_state' => VerificationState::Candidate,
                    ]);
                }
            }

            return $identifier;
        }

        return null;
    }

    private function relationshipAttribute(
        NormalizedRelationship $relationship,
        string $key,
    ): mixed {
        $field = $relationship->attributes[$key] ?? null;

        return $field !== null && $field->presence === FieldPresence::Provided ? $field->value : null;
    }

    private function nullableRelationshipString(
        NormalizedRelationship $relationship,
        string $key,
    ): ?string {
        $value = $this->relationshipAttribute($relationship, $key);
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
