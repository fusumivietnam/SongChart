<?php

declare(strict_types=1);

namespace App\Support\Catalog\Fusion;

use App\Contracts\Catalog\FieldAuthorityPolicy;
use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Catalog\Enums\VerificationState;
use App\Domain\Catalog\Fusion\FieldEvidence;
use App\Domain\Catalog\Fusion\FieldResolution;
use App\Models\Catalog\MetadataAssertion;
use App\Models\Catalog\MetadataSource;
use DateTimeInterface;
use Illuminate\Support\Collection;

final readonly class CanonicalFieldResolver
{
    public function __construct(private FieldAuthorityPolicy $authority) {}

    public function resolve(EntityType $entityType, string $entityId, string $fieldName): FieldResolution
    {
        $assertions = MetadataAssertion::query()
            ->where('entity_type', $entityType->value)
            ->where('entity_id', $entityId)
            ->where('field_name', $fieldName)
            ->with('source')
            ->latest('observed_at')
            ->get();

        return $this->resolveAssertions($entityType, $fieldName, $assertions);
    }

    /** @param Collection<int, MetadataAssertion> $assertions */
    public function resolveAssertions(EntityType $entityType, string $fieldName, Collection $assertions): FieldResolution
    {
        $evidence = [];
        foreach ($assertions as $assertion) {
            $source = $assertion->getRelation('source');
            $sourceKey = $source instanceof MetadataSource ? (string) $source->key : 'songchart:unknown';
            $confidence = $assertion->getAttribute('confidence');
            $verification = $assertion->getAttribute('verification_state');
            $observedAt = $assertion->getAttribute('observed_at');
            $expiresAt = $assertion->getAttribute('expires_at');
            $value = $assertion->getAttribute('value');

            $assertionConfidence = is_numeric($confidence) ? (float) $confidence : 0.75;
            $freshness = $expiresAt instanceof DateTimeInterface && $expiresAt < now() ? 0.35 : 1.0;
            $verificationState = $verification instanceof VerificationState ? $verification->value : (string) $verification;

            $evidence[] = new FieldEvidence(
                assertionId: (string) $assertion->getKey(),
                sourceKey: $sourceKey,
                fieldName: $fieldName,
                value: is_array($value) ? $value : ['value' => $value],
                sourceAuthority: $this->authority->authority($sourceKey, $entityType, $fieldName),
                assertionConfidence: max(0.0, min(1.0, $assertionConfidence)),
                freshness: $freshness,
                verificationState: $verificationState,
                observedAt: $observedAt instanceof DateTimeInterface ? $observedAt->format(DATE_ATOM) : null,
            );
        }

        usort($evidence, static function (FieldEvidence $left, FieldEvidence $right): int {
            $score = $right->score() <=> $left->score();
            if ($score !== 0) {
                return $score;
            }

            return strcmp($left->assertionId, $right->assertionId);
        });

        $fingerprints = [];
        foreach ($evidence as $item) {
            $fingerprints[hash('sha256', json_encode($item->value, JSON_THROW_ON_ERROR))] = true;
        }

        return new FieldResolution(
            fieldName: $fieldName,
            selected: $evidence[0] ?? null,
            evidence: $evidence,
            hasConflict: count($fingerprints) > 1,
        );
    }
}
