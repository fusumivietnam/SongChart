<?php

declare(strict_types=1);

namespace App\Support\Catalog\Enrichment;

use App\Contracts\Catalog\EnrichmentEvidenceAdmissionPolicy;
use App\Domain\Catalog\Enrichment\EnrichmentEvidenceAdmission;

final class GovernedEnrichmentEvidenceAdmissionPolicy implements EnrichmentEvidenceAdmissionPolicy
{
    /**
     * @param  array{id:string,entity_type:string,entity_id:string,provider:string,need_kind:string,need_key:string,reason:string}  $attempt
     * @param  array<string, mixed>  $payload
     */
    public function assess(array $attempt, array $payload): EnrichmentEvidenceAdmission
    {
        $payload = $this->withAdmission($payload, 'pending', 'Evidence admission has not completed.');

        if (($payload['admission'] ?? null) === 'fresh-existing-identity') {
            return EnrichmentEvidenceAdmission::admissible(
                'Fresh existing canonical identity satisfies the enrichment need without provider mutation.',
                $this->withAdmission($payload, 'admissible', 'Fresh existing identity is already governed evidence.'),
            );
        }

        if (($payload['provider'] ?? null) !== $attempt['provider']) {
            return EnrichmentEvidenceAdmission::rejected(
                'Evidence provider does not match the enrichment attempt provider.',
                $this->withAdmission($payload, 'rejected', 'Provider mismatch.'),
            );
        }

        if (isset($payload['need']) && is_array($payload['need'])) {
            if (($payload['need']['kind'] ?? null) !== $attempt['need_kind'] || ($payload['need']['key'] ?? null) !== $attempt['need_key']) {
                return EnrichmentEvidenceAdmission::rejected(
                    'Evidence need metadata does not match the enrichment attempt.',
                    $this->withAdmission($payload, 'rejected', 'Need metadata mismatch.'),
                );
            }
        }

        $candidates = $payload['candidates'] ?? null;
        if (! is_array($candidates) || $candidates === []) {
            return EnrichmentEvidenceAdmission::reviewRequired(
                'No normalized candidate is available for governed admission.',
                $this->withAdmission($payload, 'review_required', 'No normalized candidate.'),
            );
        }

        foreach ($candidates as $candidate) {
            if (! is_array($candidate)) {
                return EnrichmentEvidenceAdmission::rejected(
                    'Normalized candidate payload is malformed.',
                    $this->withAdmission($payload, 'rejected', 'Malformed candidate payload.'),
                );
            }

            if (($candidate['provider_slug'] ?? null) !== $attempt['provider'] || ($candidate['entity_type'] ?? null) !== $attempt['entity_type']) {
                return EnrichmentEvidenceAdmission::rejected(
                    'Normalized candidate provider or entity type does not match the attempt.',
                    $this->withAdmission($payload, 'rejected', 'Candidate envelope mismatch.'),
                );
            }

            $validation = $candidate['validation'] ?? null;
            if (! is_array($validation) || ($validation['valid'] ?? false) !== true) {
                return EnrichmentEvidenceAdmission::rejected(
                    'Normalized candidate failed provider normalization validation.',
                    $this->withAdmission($payload, 'rejected', 'Normalization validation failed.'),
                );
            }
        }

        if (count($candidates) !== 1) {
            return EnrichmentEvidenceAdmission::reviewRequired(
                'Multiple valid candidates remain ambiguous and require governed review.',
                $this->withAdmission($payload, 'review_required', 'Multiple valid candidates remain.'),
            );
        }

        if ($attempt['need_kind'] === 'field' && ! $this->candidateProvidesField($candidates[0], $attempt['need_key'])) {
            return EnrichmentEvidenceAdmission::reviewRequired(
                'The normalized candidate does not provide the requested field.',
                $this->withAdmission($payload, 'review_required', 'Requested field is not provided.'),
            );
        }

        return EnrichmentEvidenceAdmission::admissible(
            'Exactly one validated candidate satisfies the governed admission boundary.',
            $this->withAdmission($payload, 'admissible', 'Single validated candidate.'),
        );
    }

    /** @param array<string, mixed> $candidate */
    private function candidateProvidesField(array $candidate, string $needKey): bool
    {
        $fields = $candidate['fields'] ?? null;
        if (! is_array($fields)) {
            return false;
        }

        $candidateKeys = array_unique([$needKey, $this->camel($needKey)]);
        foreach ($candidateKeys as $key) {
            $field = $fields[$key] ?? null;
            if (is_array($field) && ($field['presence'] ?? null) === 'provided') {
                return true;
            }
        }

        return false;
    }

    private function camel(string $value): string
    {
        $parts = preg_split('/[_-]+/', $value) ?: [$value];
        $first = array_shift($parts);

        return $first.implode('', array_map(static fn (string $part): string => ucfirst($part), $parts));
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function withAdmission(array $payload, string $decision, string $reason): array
    {
        $payload['evidence_admission'] = [
            'decision' => $decision,
            'reason' => $reason,
            'canonical_mutation' => false,
        ];

        return $payload;
    }
}
