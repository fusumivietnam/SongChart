<?php

declare(strict_types=1);

namespace App\Domain\Providers\Normalization\DTO;

use InvalidArgumentException;

final readonly class NormalizedDestination
{
    public function __construct(
        public string $kind,
        public string $url,
        public ?string $resourceId = null,
        public ?string $market = null,
    ) {
        if (trim($kind) === '' || trim($url) === '') {
            throw new InvalidArgumentException('Normalized destinations require kind and URL.');
        }
    }

    /** @return array{kind: string, url: string, resource_id: string|null, market: string|null} */
    public function toArray(): array
    {
        return [
            'kind' => $this->kind,
            'url' => $this->url,
            'resource_id' => $this->resourceId,
            'market' => $this->market,
        ];
    }
}
