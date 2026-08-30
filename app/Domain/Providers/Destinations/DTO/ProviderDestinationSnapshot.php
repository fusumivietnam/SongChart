<?php

declare(strict_types=1);

namespace App\Domain\Providers\Destinations\DTO;

use DateTimeInterface;

final readonly class ProviderDestinationSnapshot
{
    public function __construct(
        public string $id,
        public string $providerKey,
        public bool $providerApproved,
        public bool $providerEnabled,
        public string $reviewState,
        public ?string $privacyStatus,
        public bool $embeddable,
        public ?string $url,
        public string $resourceId,
        public int $matchScore,
        public ?DateTimeInterface $verifiedAt,
        public ?DateTimeInterface $lastCheckedAt,
    ) {}
}
