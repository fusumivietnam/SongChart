<?php

declare(strict_types=1);

namespace App\Application\Catalog\Admission;

use App\Domain\Catalog\Enums\CanonicalAdmissionStatus;
use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Catalog\Enums\VerificationState;
use App\Models\Catalog\CanonicalAdmissionDecision;
use App\Models\Catalog\MetadataAssertion;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use LogicException;

final class GovernedCanonicalAdmissionService
{
    public function stage(MetadataAssertion $assertion): CanonicalAdmissionDecision
    {
        $entityType = $assertion->getAttribute('entity_type');
        if (! $entityType instanceof EntityType) {
            throw new InvalidArgumentException('Metadata assertion entity type is invalid.');
        }

        return CanonicalAdmissionDecision::query()->firstOrCreate(
            ['metadata_assertion_id' => (string) $assertion->getKey()],
            [
                'entity_type' => $entityType,
                'entity_id' => (string) $assertion->getAttribute('entity_id'),
                'field_name' => (string) $assertion->getAttribute('field_name'),
                'status' => CanonicalAdmissionStatus::Pending,
            ],
        );
    }

    public function apply(CanonicalAdmissionDecision $decision, User $reviewer, string $reason): CanonicalAdmissionDecision
    {
        return DB::transaction(function () use ($decision, $reviewer, $reason): CanonicalAdmissionDecision {
            /** @var CanonicalAdmissionDecision $locked */
            $locked = CanonicalAdmissionDecision::query()->lockForUpdate()->findOrFail($decision->getKey());
            $this->assertPending($locked);

            $assertion = MetadataAssertion::query()->findOrFail((string) $locked->getAttribute('metadata_assertion_id'));
            $entityType = $assertion->getAttribute('entity_type');
            if (! $entityType instanceof EntityType) {
                throw new LogicException('Canonical admission assertion has an invalid entity type.');
            }

            $modelClass = $entityType->modelClass();
            /** @var Model $entity */
            $entity = $modelClass::query()->lockForUpdate()->findOrFail((string) $assertion->getAttribute('entity_id'));
            $field = (string) $assertion->getAttribute('field_name');

            if (! $entity->isFillable($field)) {
                throw new LogicException("Canonical field [{$field}] is not writable through governed admission.");
            }

            $payload = $assertion->getAttribute('value');
            if (! is_array($payload) || ! array_key_exists('value', $payload) || is_array($payload['value']) || is_object($payload['value'])) {
                throw new LogicException('Canonical admission requires a scalar or null assertion value under the [value] key.');
            }

            $entity->setAttribute($field, $payload['value']);
            $entity->save();

            $assertion->forceFill(['verification_state' => VerificationState::Verified])->save();

            $locked->forceFill([
                'status' => CanonicalAdmissionStatus::Applied,
                'canonical_value' => ['value' => $payload['value']],
                'decision_reason' => $reason,
                'reviewer_id' => (string) $reviewer->getAuthIdentifier(),
                'reviewed_at' => now(),
                'applied_at' => now(),
            ])->save();

            return $locked->refresh();
        });
    }

    public function reject(CanonicalAdmissionDecision $decision, User $reviewer, string $reason): CanonicalAdmissionDecision
    {
        return DB::transaction(function () use ($decision, $reviewer, $reason): CanonicalAdmissionDecision {
            /** @var CanonicalAdmissionDecision $locked */
            $locked = CanonicalAdmissionDecision::query()->lockForUpdate()->findOrFail($decision->getKey());
            $this->assertPending($locked);

            MetadataAssertion::query()
                ->whereKey((string) $locked->getAttribute('metadata_assertion_id'))
                ->update(['verification_state' => VerificationState::Rejected->value]);

            $locked->forceFill([
                'status' => CanonicalAdmissionStatus::Rejected,
                'decision_reason' => $reason,
                'reviewer_id' => (string) $reviewer->getAuthIdentifier(),
                'reviewed_at' => now(),
                'applied_at' => null,
            ])->save();

            return $locked->refresh();
        });
    }

    private function assertPending(CanonicalAdmissionDecision $decision): void
    {
        if ($decision->getAttribute('status') !== CanonicalAdmissionStatus::Pending) {
            throw new LogicException('Only pending canonical admissions may be decided.');
        }
    }
}
