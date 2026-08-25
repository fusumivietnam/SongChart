<?php

declare(strict_types=1);

namespace App\Application\Catalog\Admission;

use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Catalog\Enums\VerificationState;
use App\Models\Catalog\MetadataAssertion;
use App\Models\Catalog\MetadataSource;
use Illuminate\Support\Facades\DB;
use LogicException;

final readonly class MaterializeProviderAdmissionEvidence
{
    public function __construct(private GovernedCanonicalAdmissionService $admissions) {}

    /**
     * @param  array{id:string,entity_type:string,entity_id:string,provider:string,need_kind:string,need_key:string,reason:string}  $attempt
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function handle(array $attempt, array $payload): array
    {
        if ($attempt['need_kind'] !== 'field') {
            return $payload;
        }

        $candidates = $payload['candidates'] ?? null;
        if (! is_array($candidates) || count($candidates) !== 1 || ! is_array($candidates[0])) {
            throw new LogicException('Admissible field evidence requires exactly one normalized candidate.');
        }

        $field = $this->providedField($candidates[0], $attempt['need_key']);
        if ($field === null) {
            throw new LogicException('Admissible field evidence does not provide the requested field.');
        }

        $entityType = EntityType::tryFrom($attempt['entity_type']);
        if ($entityType === null) {
            throw new LogicException('Admissible evidence has an invalid entity type.');
        }

        $provider = $attempt['provider'];
        $value = $field['value'];
        $fingerprint = hash('sha256', json_encode(['value' => $value], JSON_THROW_ON_ERROR));

        return DB::transaction(function () use ($attempt, $payload, $entityType, $provider, $value, $fingerprint): array {
            $source = MetadataSource::query()->firstOrCreate(
                ['key' => 'provider:'.$provider],
                [
                    'name' => ucfirst($provider),
                    'source_type' => 'provider-enrichment',
                ],
            );

            $assertion = MetadataAssertion::query()->firstOrCreate(
                [
                    'entity_type' => $entityType->value,
                    'entity_id' => $attempt['entity_id'],
                    'field_name' => $attempt['need_key'],
                    'value_fingerprint' => $fingerprint,
                    'metadata_source_id' => (string) $source->getKey(),
                ],
                [
                    'value' => ['value' => $value],
                    'verification_state' => VerificationState::Candidate,
                    'confidence' => 0.9000,
                    'observed_at' => now(),
                ],
            );

            $decision = $this->admissions->stage($assertion);
            $payload['canonical_admission'] = [
                'metadata_assertion_id' => (string) $assertion->getKey(),
                'decision_id' => (string) $decision->getKey(),
                'status' => (string) $decision->getRawOriginal('status'),
                'canonical_mutation' => false,
            ];

            return $payload;
        });
    }

    /**
     * @param  array<string, mixed>  $candidate
     * @return array{presence:string,value:mixed}|null
     */
    private function providedField(array $candidate, string $needKey): ?array
    {
        $fields = $candidate['fields'] ?? null;
        if (! is_array($fields)) {
            return null;
        }

        foreach (array_unique([$needKey, $this->camel($needKey)]) as $key) {
            $field = $fields[$key] ?? null;
            if (is_array($field) && ($field['presence'] ?? null) === 'provided' && array_key_exists('value', $field)) {
                return ['presence' => 'provided', 'value' => $field['value']];
            }
        }

        return null;
    }

    private function camel(string $value): string
    {
        $parts = preg_split('/[_-]+/', $value) ?: [$value];
        $first = array_shift($parts);

        return $first.implode('', array_map(static fn (string $part): string => ucfirst($part), $parts));
    }
}
