<?php

declare(strict_types=1);

namespace App\Domain\Providers\Normalization\DTO;

use InvalidArgumentException;

final readonly class NormalizedMediaAsset
{
    public function __construct(
        public string $type,
        public string $resourceId,
        public ?string $url = null,
        public ?string $title = null,
        public ?string $officiality = null,
        public ?bool $embeddable = null,
    ) {
        if (trim($type) === '' || trim($resourceId) === '') {
            throw new InvalidArgumentException('Normalized media assets require type and resource ID.');
        }
    }

    /** @return array<string, string|bool|null> */
    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'resource_id' => $this->resourceId,
            'url' => $this->url,
            'title' => $this->title,
            'officiality' => $this->officiality,
            'embeddable' => $this->embeddable,
        ];
    }
}
