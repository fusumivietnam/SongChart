<?php

declare(strict_types=1);

namespace App\Support\DomainContracts\DTO;

final readonly class SupportDataSurface
{
    /**
     * @param  list<string>  $readFields
     * @param  list<string>  $writeFields
     */
    public function __construct(
        public string $key,
        public array $readFields,
        public array $writeFields,
    ) {}
}
