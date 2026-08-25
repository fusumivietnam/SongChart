<?php

declare(strict_types=1);

namespace App\Support\Providers\Ingestion;

use App\Domain\Providers\Catalog\DTO\ProviderPayload;
use App\Domain\Providers\Ingestion\DTO\ProviderImportPreview;
use App\Domain\Providers\Normalization\Validation\Contracts\NormalizedProviderEntityValidator;
use App\Support\Providers\Normalization\ProviderSpecificMapperRegistry;

final class ProviderImportPreviewBuilder
{
    public function __construct(
        private readonly ProviderSpecificMapperRegistry $mappers,
        private readonly NormalizedProviderEntityValidator $validator,
    ) {}

    public function build(ProviderPayload $payload): ProviderImportPreview
    {
        $entity = $this->mappers->mapperFor($payload)->map($payload);
        $validation = $this->validator->validate($entity);
        $normalized = $entity->toArray();

        $issues = array_map(
            static fn ($issue): array => [
                'kind' => $issue->kind->value,
                'path' => $issue->path,
                'message' => $issue->message,
            ],
            $validation->issues,
        );

        return new ProviderImportPreview(
            valid: $validation->isValid(),
            counts: [
                'fields' => count($normalized['fields']),
                'identifiers' => count($normalized['identifiers']),
                'relationships' => count($normalized['relationships']),
                'media_assets' => count($normalized['media_assets']),
                'destinations' => count($normalized['destinations']),
                'availability' => count($normalized['availability']),
                'classifications' => count($normalized['classifications']),
                'metrics' => count($normalized['metrics']),
            ],
            issues: $issues,
            normalized: $normalized,
        );
    }
}
