<?php

declare(strict_types=1);

namespace App\Domain\Providers\Normalization\Contracts;

use App\Domain\Catalog\Enums\EntityType;
use App\Domain\Providers\Normalization\ValueObjects\ProviderField;

interface NormalizedEntityData
{
    public function entityType(): EntityType;

    /** @return array<string, ProviderField> */
    public function fields(): array;

    /** @return array<string, array{presence: string, value?: mixed}> */
    public function toArray(): array;
}
