<?php

declare(strict_types=1);

namespace App\Domain\Providers\Catalog\DTO;

use App\Domain\Catalog\Enums\EntityType;
use DateTimeImmutable;

final readonly class ProviderPayload
{
    /** @param array<string, mixed> $data */
    public function __construct(
        public string $providerSlug,
        public EntityType $entityType,
        public string $externalId,
        public array $data,
        public DateTimeImmutable $receivedAt,
        public string $schemaVersion = '1',
    ) {}

    public function hash(): string
    {
        return hash('sha256', json_encode($this->data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));
    }
}
