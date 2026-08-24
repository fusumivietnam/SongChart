<?php

declare(strict_types=1);

use App\Support\Catalog\Enrichment\GovernedEnrichmentEvidenceAdmissionPolicy;

function enrichmentAttempt(array $overrides = []): array
{
    return array_merge([
        'id' => 'attempt-1',
        'entity_type' => 'artist',
        'entity_id' => 'artist-1',
        'provider' => 'musicbrainz',
        'need_kind' => 'field',
        'need_key' => 'country_code',
        'reason' => 'Missing country.',
    ], $overrides);
}

function normalizedCandidate(array $overrides = []): array
{
    return array_merge([
        'provider_slug' => 'musicbrainz',
        'entity_type' => 'artist',
        'external_id' => 'mbid-1',
        'fields' => [
            'countryCode' => ['presence' => 'provided', 'value' => 'GB'],
        ],
        'identifiers' => [],
        'relationships' => [],
        'normalizer_version' => '1',
        'validation' => ['valid' => true, 'issues' => []],
    ], $overrides);
}

it('admits exactly one validated candidate that supplies the requested field', function (): void {
    $decision = (new GovernedEnrichmentEvidenceAdmissionPolicy)->assess(enrichmentAttempt(), [
        'provider' => 'musicbrainz',
        'need' => ['kind' => 'field', 'key' => 'country_code'],
        'candidates' => [normalizedCandidate()],
    ]);

    expect($decision->decision)->toBe('admissible')
        ->and($decision->payload['evidence_admission']['canonical_mutation'])->toBeFalse();
});

it('requires review when more than one validated candidate remains', function (): void {
    $decision = (new GovernedEnrichmentEvidenceAdmissionPolicy)->assess(enrichmentAttempt(), [
        'provider' => 'musicbrainz',
        'need' => ['kind' => 'field', 'key' => 'country_code'],
        'candidates' => [normalizedCandidate(), normalizedCandidate(['external_id' => 'mbid-2'])],
    ]);

    expect($decision->decision)->toBe('review_required')
        ->and($decision->payload['evidence_admission']['decision'])->toBe('review_required');
});

it('requires review when the requested field is not provided', function (): void {
    $decision = (new GovernedEnrichmentEvidenceAdmissionPolicy)->assess(enrichmentAttempt(), [
        'provider' => 'musicbrainz',
        'need' => ['kind' => 'field', 'key' => 'country_code'],
        'candidates' => [normalizedCandidate(['fields' => ['countryCode' => ['presence' => 'missing']]])],
    ]);

    expect($decision->decision)->toBe('review_required');
});

it('rejects normalized evidence that failed validation', function (): void {
    $decision = (new GovernedEnrichmentEvidenceAdmissionPolicy)->assess(enrichmentAttempt(), [
        'provider' => 'musicbrainz',
        'need' => ['kind' => 'field', 'key' => 'country_code'],
        'candidates' => [normalizedCandidate(['validation' => ['valid' => false, 'issues' => [['kind' => 'invalid-value']]]])],
    ]);

    expect($decision->decision)->toBe('rejected')
        ->and($decision->payload['evidence_admission']['decision'])->toBe('rejected');
});

it('rejects provider or entity envelope mismatch', function (): void {
    $decision = (new GovernedEnrichmentEvidenceAdmissionPolicy)->assess(enrichmentAttempt(), [
        'provider' => 'musicbrainz',
        'need' => ['kind' => 'field', 'key' => 'country_code'],
        'candidates' => [normalizedCandidate(['entity_type' => 'release'])],
    ]);

    expect($decision->decision)->toBe('rejected');
});
