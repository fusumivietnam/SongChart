<?php

declare(strict_types=1);

namespace App\Domain\Providers\Normalization\DTO;

use InvalidArgumentException;

final readonly class NormalizedAvailability
{
    public function __construct(
        public string $market,
        public bool $available,
        public ?string $restrictionReason = null,
    ) {
        if (trim($market) === '') {
            throw new InvalidArgumentException('Normalized availability requires a market.');
        }
    }

    /** @return array{market: string, available: bool, restriction_reason: string|null} */
    public function toArray(): array
    {
        return [
            'market' => $this->market,
            'available' => $this->available,
            'restriction_reason' => $this->restrictionReason,
        ];
    }
}
