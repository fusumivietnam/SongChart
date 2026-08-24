<?php

declare(strict_types=1);

namespace App\Support\Providers\Normalization;

use App\Domain\Providers\Catalog\DTO\NormalizedProviderEntity;
use App\Domain\Providers\Normalization\Validation\Contracts\NormalizedProviderEntityValidator;
use App\Domain\Providers\Normalization\Validation\DTO\NormalizationValidationIssue;
use App\Domain\Providers\Normalization\Validation\DTO\NormalizationValidationResult;
use App\Domain\Providers\Normalization\Validation\Enums\NormalizationFailureKind;

final class DefaultNormalizedProviderEntityValidator implements NormalizedProviderEntityValidator
{
    private const MAX_STRING_LENGTH = 4096;

    private const MAX_SERIALIZED_BYTES = 1048576;

    public function validate(NormalizedProviderEntity $entity): NormalizationValidationResult
    {
        $issues = [];
        $fields = $entity->data->toArray();

        if (trim($entity->providerSlug) === '' || trim($entity->externalId) === '') {
            $issues[] = new NormalizationValidationIssue(
                NormalizationFailureKind::RequiredField,
                'envelope',
                'Provider slug and external ID are required.',
            );
        }

        foreach ($fields as $name => $field) {
            if ($field['presence'] !== 'provided') {
                continue;
            }

            $value = $field['value'] ?? null;
            if (is_string($value) && mb_strlen($value) > self::MAX_STRING_LENGTH) {
                $issues[] = new NormalizationValidationIssue(
                    NormalizationFailureKind::InvalidValue,
                    'fields.'.$name,
                    'Normalized string exceeds the maximum length.',
                );
            }

            if (str_ends_with((string) $name, '_url') && is_string($value) && ! $this->isSafeUrl($value)) {
                $issues[] = new NormalizationValidationIssue(
                    NormalizationFailureKind::UnsafeUrl,
                    'fields.'.$name,
                    'Only HTTPS URLs are accepted.',
                );
            }
        }

        foreach ($entity->identifiers as $index => $identifier) {
            if (! preg_match('/^[a-z0-9][a-z0-9_.:-]{1,63}$/', $identifier->namespace)) {
                $issues[] = new NormalizationValidationIssue(
                    NormalizationFailureKind::InvalidIdentifier,
                    'identifiers.'.$index.'.namespace',
                    'Identifier namespace is invalid.',
                );
            }
        }

        foreach ($entity->relationships as $index => $relationship) {
            if (trim($relationship->targetExternalId) === '') {
                $issues[] = new NormalizationValidationIssue(
                    NormalizationFailureKind::InvalidRelationship,
                    'relationships.'.$index.'.target_external_id',
                    'Relationship target external ID is required.',
                );
            }
        }

        $encoded = json_encode($entity->toArray(), JSON_THROW_ON_ERROR);
        if (strlen($encoded) > self::MAX_SERIALIZED_BYTES) {
            $issues[] = new NormalizationValidationIssue(
                NormalizationFailureKind::PayloadTooLarge,
                'entity',
                'Normalized entity exceeds the one MiB validation boundary.',
            );
        }

        return new NormalizationValidationResult($issues);
    }

    private function isSafeUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false
            && strtolower((string) parse_url($url, PHP_URL_SCHEME)) === 'https';
    }
}
