<?php

declare(strict_types=1);

namespace App\Domain\Providers\Normalization\DTO;

use InvalidArgumentException;

final readonly class NormalizedMetricObservation
{
    public function __construct(
        public string $metric,
        public int|float $value,
        public string $observedAt,
    ) {
        if (trim($metric) === '' || trim($observedAt) === '') {
            throw new InvalidArgumentException('Normalized metric observations require metric and observed timestamp.');
        }
    }

    /** @return array{metric: string, value: int|float, observed_at: string} */
    public function toArray(): array
    {
        return [
            'metric' => $this->metric,
            'value' => $this->value,
            'observed_at' => $this->observedAt,
        ];
    }
}
