<?php

declare(strict_types=1);

namespace App\Domain\Providers\Normalization\DTO;

use InvalidArgumentException;

final readonly class NormalizedClassification
{
    public function __construct(
        public string $taxonomy,
        public string $term,
        public ?float $confidence = null,
    ) {
        if (trim($taxonomy) === '' || trim($term) === '') {
            throw new InvalidArgumentException('Normalized classifications require taxonomy and term.');
        }

        if ($confidence !== null && ($confidence < 0.0 || $confidence > 1.0)) {
            throw new InvalidArgumentException('Normalized classification confidence must be between 0 and 1.');
        }
    }

    /** @return array{taxonomy: string, term: string, confidence: float|null} */
    public function toArray(): array
    {
        return [
            'taxonomy' => $this->taxonomy,
            'term' => $this->term,
            'confidence' => $this->confidence,
        ];
    }
}
